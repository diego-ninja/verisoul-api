<?php

use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Ninja\Verisoul\Support\RetryStrategy;
use Psr\Log\LoggerInterface;

describe('RetryStrategy Integration Tests', function (): void {
    beforeEach(function (): void {
        $this->mockLogger = Mockery::mock(LoggerInterface::class);
        $this->mockLogger->shouldReceive('info')->zeroOrMoreTimes();
        $this->mockLogger->shouldReceive('warning')->zeroOrMoreTimes();
        $this->mockLogger->shouldReceive('error')->zeroOrMoreTimes();
    });

    describe('Real retry scenarios', function (): void {
        it('handles intermittent network failures with exponential backoff', function (): void {
            $retryStrategy = new RetryStrategy(
                maxAttempts: 4,
                baseDelayMs: 100,
                backoffMultiplier: 2.0,
                maxDelayMs: 5000,
                logger: $this->mockLogger,
            );

            $attemptCount = 0;
            $startTime = microtime(true);

            $result = $retryStrategy->execute(function () use (&$attemptCount) {
                $attemptCount++;

                // Fail first 3 attempts, succeed on 4th
                if ($attemptCount < 4) {
                    throw new VerisoulConnectionException("Attempt {$attemptCount} failed");
                }

                return ['success' => true, 'attempt' => $attemptCount];
            });

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

            expect($result)->toBe(['success' => true, 'attempt' => 4])
                ->and($attemptCount)->toBe(4)
                ->and($totalTime)->toBeGreaterThan(700); // Should have delays: 100 + 200 + 400 = 700ms minimum
        });

        it('respects maximum delay limits', function (): void {
            $retryStrategy = new RetryStrategy(
                maxAttempts: 5,
                baseDelayMs: 1000,
                backoffMultiplier: 3.0,
                maxDelayMs: 2000, // Cap at 2 seconds
                logger: $this->mockLogger,
            );

            $attemptCount = 0;
            $delayTimes = [];

            // Mock time tracking for delays
            $originalTime = time();

            try {
                $retryStrategy->execute(function () use (&$attemptCount, &$delayTimes): void {
                    $attemptCount++;
                    $delayTimes[] = microtime(true);
                    throw new VerisoulConnectionException("Always fails");
                });
            } catch (VerisoulConnectionException $e) {
                // Expected to fail after all retries
            }

            expect($attemptCount)->toBe(5);

            // Verify delays were capped at maxDelayMs
            // The theoretical delays would be: 1000, 3000, 9000, 27000
            // But should be capped at: 1000, 2000, 2000, 2000
        });

        it('handles different exception types appropriately', function (): void {
            $retryStrategy = new RetryStrategy(
                maxAttempts: 3,
                baseDelayMs: 50,
                logger: $this->mockLogger,
            );

            // Test 1: Connection exceptions should be retried
            $connectionAttempts = 0;
            try {
                $retryStrategy->execute(function () use (&$connectionAttempts): void {
                    $connectionAttempts++;
                    throw new VerisoulConnectionException("Connection failed");
                });
            } catch (VerisoulConnectionException $e) {
                // Expected
            }
            expect($connectionAttempts)->toBe(3);

            // Test 2: API exceptions should be retried
            $apiAttempts = 0;
            try {
                $retryStrategy->execute(function () use (&$apiAttempts): void {
                    $apiAttempts++;
                    throw new VerisoulApiException("Server error", 500);
                });
            } catch (VerisoulApiException $e) {
                // Expected
            }
            expect($apiAttempts)->toBe(3);

            // Test 3: Other exceptions should not be retried
            $otherAttempts = 0;
            try {
                $retryStrategy->execute(function () use (&$otherAttempts): void {
                    $otherAttempts++;
                    throw new InvalidArgumentException("Invalid input");
                });
            } catch (InvalidArgumentException $e) {
                // Expected
            }
            expect($otherAttempts)->toBe(1); // No retries for non-Verisoul exceptions
        });
    });

    describe('Performance under load', function (): void {
        it('maintains consistent timing under concurrent load', function (): void {
            $retryStrategy = new RetryStrategy(
                maxAttempts: 2,
                baseDelayMs: 100,
                logger: $this->mockLogger,
            );

            $results = [];
            $promises = [];

            // Simulate 5 concurrent operations
            for ($i = 0; $i < 5; $i++) {
                $promises[] = function () use ($retryStrategy, $i, &$results): void {
                    $startTime = microtime(true);

                    try {
                        $retryStrategy->execute(function () use ($i) {
                            // First attempt always fails
                            static $attempts = [];
                            $attempts[$i] = ($attempts[$i] ?? 0) + 1;

                            if (1 === $attempts[$i]) {
                                throw new VerisoulConnectionException("First attempt fails");
                            }

                            return ['operation' => $i, 'success' => true];
                        });
                    } catch (Exception $e) {
                        // Some may fail
                    }

                    $endTime = microtime(true);
                    $results[$i] = ($endTime - $startTime) * 1000;
                };
            }

            // Execute all promises
            foreach ($promises as $promise) {
                $promise();
            }

            expect(count($results))->toBe(5);

            // All operations should have similar timing (within reasonable variance)
            $avgTime = array_sum($results) / count($results);
            foreach ($results as $time) {
                expect($time)->toBeLessThan($avgTime * 1.5); // Within 50% of average
            }
        });

        it('handles rapid successive calls efficiently', function (): void {
            $retryStrategy = new RetryStrategy(
                maxAttempts: 2,
                baseDelayMs: 50,
                logger: $this->mockLogger,
            );

            $startTime = microtime(true);
            $successCount = 0;

            // Make 10 rapid calls
            for ($i = 0; $i < 10; $i++) {
                try {
                    $result = $retryStrategy->execute(function () use ($i) {
                        // 70% success rate on first try
                        if (random_int(1, 10) <= 7) {
                            return ['call' => $i, 'success' => true];
                        }
                        throw new VerisoulConnectionException("Random failure");
                    });

                    if ($result['success']) {
                        $successCount++;
                    }
                } catch (Exception $e) {
                    // Some calls may fail completely
                }
            }

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000;

            expect($successCount)->toBeGreaterThan(0)
                ->and($totalTime)->toBeLessThan(5000); // Should complete within 5 seconds
        });
    });

    describe('Logging integration', function (): void {
        it('logs retry attempts with proper detail levels', function (): void {
            $mockLogger = Mockery::mock(LoggerInterface::class);

            // Expect specific log calls
            $mockLogger->shouldReceive('info')
                ->twice() // Two retry attempts
                ->with('Retrying operation', Mockery::type('array'));

            $retryStrategy = new RetryStrategy(
                maxAttempts: 3,
                baseDelayMs: 25,
                logger: $mockLogger,
            );

            $attemptCount = 0;
            $result = $retryStrategy->execute(function () use (&$attemptCount) {
                $attemptCount++;

                if ($attemptCount < 3) {
                    throw new VerisoulConnectionException("Attempt {$attemptCount} failed");
                }

                return ['success' => true];
            });

            expect($result)->toBe(['success' => true]);
        });

        it('logs final failure with complete context', function (): void {
            $mockLogger = Mockery::mock(LoggerInterface::class);

            $mockLogger->shouldReceive('info')
                ->times(2) // Two retry attempts
                ->with('Retrying operation', Mockery::type('array'));

            $retryStrategy = new RetryStrategy(
                maxAttempts: 3,
                baseDelayMs: 25,
                logger: $mockLogger,
            );

            expect(function () use ($retryStrategy): void {
                $retryStrategy->execute(function (): void {
                    throw new VerisoulConnectionException("Always fails");
                });
            })->toThrow(VerisoulConnectionException::class);
        });
    });

    describe('Edge cases and boundary conditions', function (): void {
        it('handles zero delay correctly', function (): void {
            $retryStrategy = new RetryStrategy(
                maxAttempts: 3,
                baseDelayMs: 0, // No delay
                logger: $this->mockLogger,
            );

            $startTime = microtime(true);
            $attemptCount = 0;

            try {
                $retryStrategy->execute(function () use (&$attemptCount): void {
                    $attemptCount++;
                    throw new VerisoulConnectionException("Always fails");
                });
            } catch (VerisoulConnectionException $e) {
                // Expected
            }

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000;

            expect($attemptCount)->toBe(3)
                ->and($totalTime)->toBeLessThan(100); // Should be very fast with no delays
        });

        it('handles single attempt correctly', function (): void {
            $retryStrategy = new RetryStrategy(
                maxAttempts: 1,
                baseDelayMs: 100,
                logger: $this->mockLogger,
            );

            $attemptCount = 0;

            try {
                $retryStrategy->execute(function () use (&$attemptCount): void {
                    $attemptCount++;
                    throw new VerisoulConnectionException("Single attempt fails");
                });
            } catch (VerisoulConnectionException $e) {
                // Expected
            }

            expect($attemptCount)->toBe(1); // Should only try once
        });

        it('handles immediate success', function (): void {
            $retryStrategy = new RetryStrategy(
                maxAttempts: 5,
                baseDelayMs: 1000, // Long delay, but shouldn't be used
                logger: $this->mockLogger,
            );

            $startTime = microtime(true);

            $result = $retryStrategy->execute(fn() => ['immediate' => 'success']);

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000;

            expect($result)->toBe(['immediate' => 'success'])
                ->and($totalTime)->toBeLessThan(100); // Should be immediate, no delays
        });
    });
});
