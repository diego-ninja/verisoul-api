<?php

namespace Tests\Unit\Support;

use Exception;
use Ninja\Verisoul\Exceptions\CircuitBreakerOpenException;
use Ninja\Verisoul\Support\CircuitBreaker;
use Ninja\Verisoul\Support\InMemoryCache;
use Tests\TestCase;

describe('CircuitBreaker Simple Tests', function () {
    beforeEach(function () {
        $this->cache = new InMemoryCache();
        $this->service = 'test-service';
        $this->circuitBreaker = new CircuitBreaker(
            service: $this->service,
            cache: $this->cache,
            failureThreshold: 2,
            timeoutSeconds: 1,
            recoveryTime: 1
        );
    });

    describe('basic functionality', function () {
        it('executes successful callbacks', function () {
            $result = $this->circuitBreaker->call(fn() => 'success');
            expect($result)->toBe('success');
        });

        it('opens circuit after threshold failures', function () {
            // First failure
            try {
                $this->circuitBreaker->call(function () {
                    throw new Exception('fail 1');
                });
            } catch (Exception $e) {}

            // Second failure - should open circuit
            try {
                $this->circuitBreaker->call(function () {
                    throw new Exception('fail 2');
                });
            } catch (Exception $e) {}

            // Third call should be blocked by open circuit
            expect(fn() => $this->circuitBreaker->call(fn() => 'should be blocked'))
                ->toThrow(CircuitBreakerOpenException::class);
        });

        it('allows recovery after timeout', function () {
            // Open the circuit
            try {
                $this->circuitBreaker->call(function () {
                    throw new Exception('fail 1');
                });
            } catch (Exception $e) {}

            try {
                $this->circuitBreaker->call(function () {
                    throw new Exception('fail 2');
                });
            } catch (Exception $e) {}

            // Should be blocked initially
            expect(fn() => $this->circuitBreaker->call(fn() => 'blocked'))
                ->toThrow(CircuitBreakerOpenException::class);

            // Wait for recovery time
            sleep(2);

            // Should allow recovery attempt
            $result = $this->circuitBreaker->call(fn() => 'recovered');
            expect($result)->toBe('recovered');
        });

        it('handles different service names', function () {
            $circuit1 = new CircuitBreaker('service1', $this->cache, failureThreshold: 2);
            $circuit2 = new CircuitBreaker('service2', $this->cache, failureThreshold: 2);

            // Open circuit1
            try {
                $circuit1->call(function () { throw new Exception('fail'); });
            } catch (Exception $e) {}
            try {
                $circuit1->call(function () { throw new Exception('fail'); });
            } catch (Exception $e) {}

            // circuit1 should be blocked
            expect(fn() => $circuit1->call(fn() => 'blocked'))
                ->toThrow(CircuitBreakerOpenException::class);

            // circuit2 should still work
            $result = $circuit2->call(fn() => 'working');
            expect($result)->toBe('working');
        });

        it('reduces failure count on success', function () {
            // First failure
            try {
                $this->circuitBreaker->call(function () {
                    throw new Exception('fail 1');
                });
            } catch (Exception $e) {}

            // Success should reduce count
            $this->circuitBreaker->call(fn() => 'success');

            // Should need 2 more failures to open circuit
            try {
                $this->circuitBreaker->call(function () {
                    throw new Exception('fail 2');
                });
            } catch (Exception $e) {}

            // Should still be closed (only 1 effective failure)
            $result = $this->circuitBreaker->call(fn() => 'still open');
            expect($result)->toBe('still open');
        });
    });

    describe('constructor parameters', function () {
        it('respects custom failure threshold', function () {
            $circuit = new CircuitBreaker(
                service: 'threshold-test',
                cache: $this->cache,
                failureThreshold: 1 // Very low threshold
            );

            // First failure should open circuit
            try {
                $circuit->call(function () {
                    throw new Exception('single failure');
                });
            } catch (Exception $e) {}

            // Should be blocked immediately
            expect(fn() => $circuit->call(fn() => 'blocked'))
                ->toThrow(CircuitBreakerOpenException::class);
        });

        it('respects custom recovery time', function () {
            $circuit = new CircuitBreaker(
                service: 'recovery-test',
                cache: $this->cache,
                failureThreshold: 1,
                recoveryTime: 3 // 3 seconds
            );

            // Open circuit
            try {
                $circuit->call(function () {
                    throw new Exception('failure');
                });
            } catch (Exception $e) {}

            // Should be blocked initially
            expect(fn() => $circuit->call(fn() => 'blocked'))
                ->toThrow(CircuitBreakerOpenException::class);

            // Should still be blocked after 1 second
            sleep(1);
            expect(fn() => $circuit->call(fn() => 'still blocked'))
                ->toThrow(CircuitBreakerOpenException::class);
        });
    });

    describe('data types and edge cases', function () {
        it('handles null return values', function () {
            $result = $this->circuitBreaker->call(fn() => null);
            expect($result)->toBeNull();
        });

        it('handles complex return values', function () {
            $complexData = [
                'array' => [1, 2, 3],
                'object' => (object) ['prop' => 'value'],
                'nested' => ['deep' => ['data' => true]]
            ];

            $result = $this->circuitBreaker->call(fn() => $complexData);
            expect($result)->toBe($complexData);
        });

        it('propagates exception types correctly', function () {
            $customException = new \InvalidArgumentException('Custom error');

            expect(fn() => $this->circuitBreaker->call(function () use ($customException) {
                throw $customException;
            }))->toThrow(\InvalidArgumentException::class, 'Custom error');
        });
    });

    describe('circuit states', function () {
        it('handles half-open state correctly', function () {
            // Open the circuit
            try {
                $this->circuitBreaker->call(function () {
                    throw new Exception('fail 1');
                });
            } catch (Exception $e) {}

            try {
                $this->circuitBreaker->call(function () {
                    throw new Exception('fail 2');
                });
            } catch (Exception $e) {}

            // Wait for recovery
            sleep(2);

            // First call after recovery should work (half-open -> closed)
            $result1 = $this->circuitBreaker->call(fn() => 'first recovery');
            expect($result1)->toBe('first recovery');

            // Subsequent calls should work normally (now closed)
            $result2 = $this->circuitBreaker->call(fn() => 'second call');
            expect($result2)->toBe('second call');
        });

        it('returns to open state on half-open failure', function () {
            // Open the circuit
            try {
                $this->circuitBreaker->call(function () {
                    throw new Exception('fail 1');
                });
            } catch (Exception $e) {}

            try {
                $this->circuitBreaker->call(function () {
                    throw new Exception('fail 2');
                });
            } catch (Exception $e) {}

            // Wait for recovery
            sleep(2);

            // Fail during half-open state
            try {
                $this->circuitBreaker->call(function () {
                    throw new Exception('half-open failure');
                });
            } catch (Exception $e) {}

            // Should be open again
            expect(fn() => $this->circuitBreaker->call(fn() => 'should be blocked'))
                ->toThrow(CircuitBreakerOpenException::class);
        });
    });
});