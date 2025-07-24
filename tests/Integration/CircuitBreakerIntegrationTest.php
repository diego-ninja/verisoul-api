<?php

use Ninja\Verisoul\Support\CircuitBreaker;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Ninja\Verisoul\Exceptions\CircuitBreakerOpenException;
use Psr\SimpleCache\CacheInterface;

describe('CircuitBreaker Integration Tests', function () {
    beforeEach(function () {
        $this->mockCache = Mockery::mock(CacheInterface::class);
        $this->mockCache->shouldReceive('get')->andReturnUsing(function($key, $default = null) {
            return $default ?? 'closed';
        })->byDefault();
        $this->mockCache->shouldReceive('set')->andReturn(true)->byDefault();
        $this->mockCache->shouldReceive('delete')->andReturn(true)->byDefault();
    });

    describe('Circuit state transitions under load', function () {
        it('transitions from closed to open under failure load', function () {
            $circuitBreaker = new CircuitBreaker(
                service: 'test-service',
                cache: $this->mockCache,
                failureThreshold: 3,
                recoveryTime: 1000
            );

            $failureCount = 0;

            // Simulate 5 consecutive failures
            for ($i = 0; $i < 5; $i++) {
                try {
                    $circuitBreaker->call(function() use (&$failureCount) {
                        $failureCount++;
                        throw new VerisoulConnectionException("Service failure #{$failureCount}");
                    });
                } catch (VerisoulConnectionException $e) {
                    // Expected for first 3 failures
                    if ($i < 3) {
                        expect($e->getMessage())->toContain("Service failure");
                    }
                } catch (CircuitBreakerOpenException $e) {
                    // Expected after failure threshold is reached
                    expect($i)->toBeGreaterThanOrEqualTo(3);
                }
            }

            expect($failureCount)->toBe(3); // Should stop calling after threshold
        });

        it('transitions from open to half-open after timeout', function () {
            $circuitBreaker = new CircuitBreaker(
                service: 'timeout-test',
                cache: $this->mockCache,
                failureThreshold: 2,
                recoveryTime: 100 // Short timeout for testing
            );

            // Trigger circuit to open
            for ($i = 0; $i < 3; $i++) {
                try {
                    $circuitBreaker->call(function() {
                        throw new VerisoulConnectionException("Initial failure");
                    });
                } catch (\Exception $e) {
                    // Expected
                }
            }

            // Wait for recovery timeout
            usleep(150000); // 150ms

            $halfOpenTested = false;

            // Next call should be in half-open state
            try {
                $result = $circuitBreaker->call(function() use (&$halfOpenTested) {
                    $halfOpenTested = true;
                    return ['status' => 'recovered'];
                });

                expect($result)->toBe(['status' => 'recovered'])
                    ->and($halfOpenTested)->toBeTrue();

            } catch (\Exception $e) {
                // If this fails, circuit should go back to open
            }
        });

        it('resets to closed state after successful half-open operation', function () {
            $circuitBreaker = new CircuitBreaker(
                service: 'reset-test',
                cache: $this->mockCache,
                failureThreshold: 2,
                recoveryTime: 50
            );

            // Open the circuit
            for ($i = 0; $i < 3; $i++) {
                try {
                    $circuitBreaker->call(function() {
                        throw new VerisoulConnectionException("Opening circuit");
                    });
                } catch (\Exception $e) {
                    // Expected
                }
            }

            // Wait for recovery timeout
            usleep(100000); // 100ms

            // Successful call in half-open state should reset to closed
            $result = $circuitBreaker->call(function() {
                return ['circuit' => 'reset'];
            });

            expect($result)->toBe(['circuit' => 'reset']);

            // Subsequent calls should work normally (circuit is closed)
            $result2 = $circuitBreaker->call(function() {
                return ['circuit' => 'working'];
            });

            expect($result2)->toBe(['circuit' => 'working']);
        });
    });

    describe('High load scenarios', function () {
        it('handles concurrent requests appropriately', function () {
            $circuitBreaker = new CircuitBreaker(
                service: 'concurrent-test',
                cache: $this->mockCache,
                failureThreshold: 5,
                recoveryTime: 200
            );

            $results = [];
            $exceptions = [];

            // Simulate 10 concurrent operations
            $operations = [];
            for ($i = 0; $i < 10; $i++) {
                $operations[] = function() use ($circuitBreaker, $i, &$results, &$exceptions) {
                    try {
                        $result = $circuitBreaker->call(function() use ($i) {
                            // 50% failure rate
                            if ($i % 2 === 0) {
                                throw new VerisoulConnectionException("Failure #{$i}");
                            }
                            return ['operation' => $i, 'success' => true];
                        });
                        $results[$i] = $result;
                    } catch (\Exception $e) {
                        $exceptions[$i] = $e;
                    }
                };
            }

            // Execute all operations
            foreach ($operations as $operation) {
                $operation();
            }

            // Should have some successes and some failures
            expect(count($results))->toBeGreaterThan(0);
            expect(count($exceptions))->toBeGreaterThan(0);
            expect(count($results) + count($exceptions))->toBe(10);
        });

        it('maintains performance under burst load', function () {
            $circuitBreaker = new CircuitBreaker(
                service: 'burst-test',
                cache: $this->mockCache,
                failureThreshold: 10,
                recoveryTime: 100
            );

            $startTime = microtime(true);
            $successCount = 0;
            $totalOperations = 100;

            for ($i = 0; $i < $totalOperations; $i++) {
                try {
                    $result = $circuitBreaker->call(function() use ($i) {
                        // Simulate varying response times
                        if ($i % 10 === 0) {
                            usleep(1000); // 1ms delay for some operations
                        }
                        
                        // 90% success rate
                        if ($i % 10 !== 9) {
                            return ['burst_op' => $i];
                        }
                        
                        throw new VerisoulConnectionException("Burst failure #{$i}");
                    });
                    
                    if ($result) {
                        $successCount++;
                    }
                } catch (\Exception $e) {
                    // Some operations will fail
                }
            }

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

            expect($successCount)->toBeGreaterThan(80) // At least 80% success
                ->and($totalTime)->toBeLessThan(5000); // Should complete within 5 seconds
        });
    });

    describe('Cache integration and persistence', function () {
        it('persists circuit state across instances', function () {
            $persistentCache = Mockery::mock(CacheInterface::class);
            
            // First instance opens the circuit
            $circuit1 = new CircuitBreaker(
                service: 'persistent-test',
                cache: $persistentCache,
                failureThreshold: 2,
                recoveryTime: 1000
            );

            // Mock cache to store and retrieve state
            $circuitState = 'closed';
            $persistentCache->shouldReceive('get')
                ->andReturnUsing(function($key, $default = null) use (&$circuitState) {
                    return $circuitState ?? $default;
                });
                
            $persistentCache->shouldReceive('set')
                ->andReturnUsing(function($key, $value) use (&$circuitState) {
                    $circuitState = $value;
                    return true;
                });

            // Open the circuit with first instance
            for ($i = 0; $i < 3; $i++) {
                try {
                    $circuit1->call(function() {
                        throw new VerisoulConnectionException("Persistent failure");
                    });
                } catch (\Exception $e) {
                    // Expected
                }
            }

            // Second instance should recognize the open state
            $circuit2 = new CircuitBreaker(
                service: 'persistent-test',
                cache: $persistentCache,
                failureThreshold: 2,
                recoveryTime: 1000
            );

            expect(function() use ($circuit2) {
                $circuit2->call(function() {
                    return ['should' => 'fail'];
                });
            })->toThrow(CircuitBreakerOpenException::class);
        });

        it('handles cache failures gracefully', function () {
            $failingCache = Mockery::mock(CacheInterface::class);
            $failingCache->shouldReceive('get')->andThrow(new \Exception("Cache failure"));
            $failingCache->shouldReceive('set')->andThrow(new \Exception("Cache failure"));

            $circuitBreaker = new CircuitBreaker(
                service: 'cache-fail-test',
                cache: $failingCache,
                failureThreshold: 3,
                recoveryTime: 100
            );

            // Circuit breaker should still work even with cache failures
            $result = $circuitBreaker->call(function() {
                return ['cache_failed' => 'but_circuit_works'];
            });

            expect($result)->toBe(['cache_failed' => 'but_circuit_works']);

            // Should handle failures too
            $failureCount = 0;
            for ($i = 0; $i < 5; $i++) {
                try {
                    $circuitBreaker->call(function() use (&$failureCount) {
                        $failureCount++;
                        throw new VerisoulConnectionException("Service down");
                    });
                } catch (\Exception $e) {
                    // Expected - should handle failures even without cache
                }
            }

            expect($failureCount)->toBeGreaterThan(0);
        });
    });

    describe('Resource cleanup and memory management', function () {
        it('properly cleans up resources after many operations', function () {
            $circuitBreaker = new CircuitBreaker(
                service: 'cleanup-test',
                cache: $this->mockCache,
                failureThreshold: 50,
                recoveryTime: 100
            );

            $initialMemory = memory_get_usage();

            // Perform many operations
            for ($i = 0; $i < 1000; $i++) {
                try {
                    $circuitBreaker->call(function() use ($i) {
                        // Mix of successes and failures
                        if ($i % 10 === 0) {
                            throw new VerisoulConnectionException("Occasional failure");
                        }
                        return ['iteration' => $i];
                    });
                } catch (\Exception $e) {
                    // Some will fail
                }
            }

            $finalMemory = memory_get_usage();
            $memoryIncrease = $finalMemory - $initialMemory;

            // Memory increase should be reasonable (less than 1MB)
            expect($memoryIncrease)->toBeLessThan(1024 * 1024);
        });

        it('handles rapid state changes efficiently', function () {
            $circuitBreaker = new CircuitBreaker(
                service: 'rapid-change-test',
                cache: $this->mockCache,
                failureThreshold: 2,
                recoveryTime: 10 // Very short timeout
            );

            $stateChanges = 0;
            $startTime = microtime(true);

            // Rapidly cycle between success and failure
            for ($i = 0; $i < 50; $i++) {
                try {
                    $circuitBreaker->call(function() use ($i) {
                        // Alternate between success and failure patterns
                        if ($i % 4 < 2) {
                            throw new VerisoulConnectionException("Rapid failure");
                        }
                        return ['rapid' => $i];
                    });
                } catch (\Exception $e) {
                    $stateChanges++;
                }

                // Small delay to allow timeout recovery
                if ($i % 10 === 0) {
                    usleep(15000); // 15ms
                }
            }

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000;

            expect($stateChanges)->toBeGreaterThan(0)
                ->and($totalTime)->toBeLessThan(3000); // Should handle rapid changes efficiently
        });
    });
});