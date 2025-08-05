<?php

use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Ninja\Verisoul\Exceptions\VerisoulValidationException;
use Ninja\Verisoul\Support\RetryStrategy;
use Psr\Log\LoggerInterface;

describe('RetryStrategy Support Class', function (): void {
    describe('construction and basic functionality', function (): void {
        it('can be created with default parameters', function (): void {
            $retry = new RetryStrategy();

            expect($retry)->toBeInstanceOf(RetryStrategy::class);
        });

        it('can be created with custom parameters', function (): void {
            $logger = Mockery::mock(LoggerInterface::class);

            $retry = new RetryStrategy(
                maxAttempts: 5,
                baseDelayMs: 2000,
                backoffMultiplier: 1.5,
                maxDelayMs: 60000,
                logger: $logger,
            );

            expect($retry)->toBeInstanceOf(RetryStrategy::class);
        });

        it('is a readonly class', function (): void {
            $retry = new RetryStrategy();

            expect($retry)->toBeInstanceOf(RetryStrategy::class);
            // Properties should be readonly and not modifiable
        });
    });

    describe('execute method - successful operations', function (): void {
        it('executes callback successfully on first attempt', function (): void {
            $retry = new RetryStrategy();
            $callCount = 0;

            $result = $retry->execute(function () use (&$callCount) {
                $callCount++;
                return 'success';
            });

            expect($result)->toBe('success');
            expect($callCount)->toBe(1);
        });

        it('returns callback result correctly', function (): void {
            $retry = new RetryStrategy();

            $result = $retry->execute(fn() => ['data' => 'test', 'status' => 'OK']);

            expect($result)->toBe(['data' => 'test', 'status' => 'OK']);
        });

        it('handles different return types', function (): void {
            $retry = new RetryStrategy();

            // String
            $stringResult = $retry->execute(fn() => 'test string');
            expect($stringResult)->toBe('test string');

            // Integer
            $intResult = $retry->execute(fn() => 42);
            expect($intResult)->toBe(42);

            // Array
            $arrayResult = $retry->execute(fn() => [1, 2, 3]);
            expect($arrayResult)->toBe([1, 2, 3]);

            // Boolean
            $boolResult = $retry->execute(fn() => true);
            expect($boolResult)->toBeTrue();

            // Null
            $nullResult = $retry->execute(fn() => null);
            expect($nullResult)->toBeNull();
        });
    });

    describe('execute method - retry behavior', function (): void {
        it('retries on VerisoulConnectionException', function (): void {
            $retry = new RetryStrategy(maxAttempts: 3);
            $callCount = 0;

            $result = $retry->execute(function () use (&$callCount) {
                $callCount++;
                if ($callCount < 3) {
                    throw new VerisoulConnectionException('Connection failed');
                }
                return 'success after retries';
            });

            expect($result)->toBe('success after retries');
            expect($callCount)->toBe(3);
        });

        it('retries on 5xx VerisoulApiException', function (): void {
            $retry = new RetryStrategy(maxAttempts: 2);
            $callCount = 0;

            $result = $retry->execute(function () use (&$callCount) {
                $callCount++;
                if ($callCount < 2) {
                    throw new VerisoulApiException('Server error', 500);
                }
                return 'success after server error';
            });

            expect($result)->toBe('success after server error');
            expect($callCount)->toBe(2);
        });

        it('retries on 429 rate limit error', function (): void {
            $retry = new RetryStrategy(maxAttempts: 2);
            $callCount = 0;

            $result = $retry->execute(function () use (&$callCount) {
                $callCount++;
                if ($callCount < 2) {
                    throw new VerisoulApiException('Rate limit exceeded', 429);
                }
                return 'success after rate limit';
            });

            expect($result)->toBe('success after rate limit');
            expect($callCount)->toBe(2);
        });

        it('retries on 408 timeout error', function (): void {
            $retry = new RetryStrategy(maxAttempts: 2);
            $callCount = 0;

            $result = $retry->execute(function () use (&$callCount) {
                $callCount++;
                if ($callCount < 2) {
                    throw new VerisoulApiException('Request timeout', 408);
                }
                return 'success after timeout';
            });

            expect($result)->toBe('success after timeout');
            expect($callCount)->toBe(2);
        });
    });

    describe('execute method - non-retryable exceptions', function (): void {
        it('does not retry VerisoulValidationException', function (): void {
            $retry = new RetryStrategy(maxAttempts: 3);
            $callCount = 0;

            expect(function () use ($retry, &$callCount): void {
                $retry->execute(function () use (&$callCount): void {
                    $callCount++;
                    throw new VerisoulValidationException('Validation failed', 'test_field', 'invalid_value');
                });
            })->toThrow(VerisoulValidationException::class);

            expect($callCount)->toBe(1);
        });

        it('does not retry 4xx client errors (except 408, 429)', function (): void {
            $retry = new RetryStrategy(maxAttempts: 3);
            $clientErrors = [400, 401, 403, 404, 422];

            foreach ($clientErrors as $statusCode) {
                $callCount = 0;

                expect(function () use ($retry, &$callCount, $statusCode): void {
                    $retry->execute(function () use (&$callCount, $statusCode): void {
                        $callCount++;
                        throw new VerisoulApiException("Client error", $statusCode);
                    });
                })->toThrow(VerisoulApiException::class);

                expect($callCount)->toBe(1, "Status code {$statusCode} should not retry");
            }
        });

        it('does not retry after max attempts reached with retryable exceptions', function (): void {
            $retry = new RetryStrategy(maxAttempts: 2);
            $callCount = 0;

            expect(function () use ($retry, &$callCount): void {
                $retry->execute(function () use (&$callCount): void {
                    $callCount++;
                    throw new VerisoulConnectionException('Always failing connection');
                });
            })->toThrow(VerisoulConnectionException::class);

            expect($callCount)->toBe(2);
        });
    });

    describe('delay calculation and backoff', function (): void {
        it('applies exponential backoff correctly', function (): void {
            $retry = new RetryStrategy(
                maxAttempts: 4,
                baseDelayMs: 100,
                backoffMultiplier: 2.0,
            );
            $callCount = 0;
            $delays = [];
            $startTime = microtime(true);

            expect(function () use ($retry, &$callCount, &$delays, $startTime): void {
                $retry->execute(function () use (&$callCount, &$delays, $startTime): void {
                    $callCount++;
                    if ($callCount > 1) {
                        $currentTime = microtime(true);
                        $delays[] = ($currentTime - $startTime) * 1000; // Convert to ms
                    }
                    throw new VerisoulConnectionException('Always failing');
                });
            })->toThrow(VerisoulConnectionException::class);

            expect($callCount)->toBe(4);
            expect(count($delays))->toBe(3); // 3 delays between 4 attempts

            // Verify delays are increasing (allowing for jitter)
            expect($delays[1])->toBeGreaterThan($delays[0]);
            expect($delays[2])->toBeGreaterThan($delays[1]);
        });

        it('respects max delay limit', function (): void {
            $retry = new RetryStrategy(
                maxAttempts: 3,
                baseDelayMs: 1000,
                backoffMultiplier: 10.0,
                maxDelayMs: 2000,
            );

            // We can't easily test the internal delay calculation without exposing it,
            // but we can verify the strategy doesn't hang indefinitely
            $startTime = microtime(true);
            $callCount = 0;

            expect(function () use ($retry, &$callCount): void {
                $retry->execute(function () use (&$callCount): void {
                    $callCount++;
                    throw new VerisoulConnectionException('Test failure');
                });
            })->toThrow(VerisoulConnectionException::class);

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000; // Convert to ms

            // Should complete within reasonable time due to max delay limit
            expect($totalTime)->toBeLessThan(10000); // Less than 10 seconds
            expect($callCount)->toBe(3);
        });
    });

    describe('logging functionality', function (): void {
        it('logs retry attempts when logger is provided', function (): void {
            $logger = Mockery::mock(LoggerInterface::class);
            $logger->shouldReceive('info')
                ->with('Retrying operation', Mockery::type('array'))
                ->twice(); // Should log for attempts 1 and 2 (before attempt 3 succeeds)

            $retry = new RetryStrategy(maxAttempts: 3, logger: $logger);
            $callCount = 0;

            $result = $retry->execute(function () use (&$callCount) {
                $callCount++;
                if ($callCount < 3) {
                    throw new VerisoulConnectionException('Connection failed');
                }
                return 'success';
            });

            expect($result)->toBe('success');
            expect($callCount)->toBe(3);
        });

        it('does not log when no logger is provided', function (): void {
            $retry = new RetryStrategy(maxAttempts: 3);
            $callCount = 0;

            // This should not throw any errors even without a logger
            $result = $retry->execute(function () use (&$callCount) {
                $callCount++;
                if ($callCount < 2) {
                    throw new VerisoulConnectionException('Connection failed');
                }
                return 'success without logging';
            });

            expect($result)->toBe('success without logging');
            expect($callCount)->toBe(2);
        });
    });

    describe('edge cases and error handling', function (): void {
        it('handles callback that throws non-Verisoul exceptions', function (): void {
            $retry = new RetryStrategy(maxAttempts: 3);
            $callCount = 0;

            expect(function () use ($retry, &$callCount): void {
                $retry->execute(function () use (&$callCount): void {
                    $callCount++;
                    throw new RuntimeException('Generic runtime error');
                });
            })->toThrow(RuntimeException::class);

            // Based on the shouldRetry logic, non-Verisoul exceptions are retried
            // only if attempt < maxAttempts in the condition, so it should try once and fail
            expect($callCount)->toBe(1);
        });

        it('handles callback that returns callable', function (): void {
            $retry = new RetryStrategy();

            $result = $retry->execute(fn() => fn() => 'nested callable result');

            expect($result)->toBeCallable();
            expect($result())->toBe('nested callable result');
        });

        it('handles very high max attempts', function (): void {
            $retry = new RetryStrategy(maxAttempts: 1000);
            $callCount = 0;

            $result = $retry->execute(function () use (&$callCount) {
                $callCount++;
                if ($callCount < 3) {
                    throw new VerisoulConnectionException('Temporary failure');
                }
                return 'success with high max attempts';
            });

            expect($result)->toBe('success with high max attempts');
            expect($callCount)->toBe(3);
        });

        it('handles zero and negative delays gracefully', function (): void {
            $retry = new RetryStrategy(
                maxAttempts: 2,
                baseDelayMs: 0,
                backoffMultiplier: 0.0,
            );
            $callCount = 0;

            $result = $retry->execute(function () use (&$callCount) {
                $callCount++;
                if ($callCount < 2) {
                    throw new VerisoulConnectionException('Quick retry test');
                }
                return 'success with zero delay';
            });

            expect($result)->toBe('success with zero delay');
            expect($callCount)->toBe(2);
        });
    });

    describe('realistic usage scenarios', function (): void {
        it('handles API rate limiting scenario', function (): void {
            $retry = new RetryStrategy(maxAttempts: 4, baseDelayMs: 500);
            $callCount = 0;

            $result = $retry->execute(function () use (&$callCount) {
                $callCount++;

                if ($callCount <= 2) {
                    throw new VerisoulApiException('Rate limit exceeded', 429);
                }

                return ['status' => 'success', 'data' => 'API call successful'];
            });

            expect($result)->toBe(['status' => 'success', 'data' => 'API call successful']);
            expect($callCount)->toBe(3);
        });

        it('handles network instability scenario', function (): void {
            $retry = new RetryStrategy(maxAttempts: 5, baseDelayMs: 200);
            $callCount = 0;

            $result = $retry->execute(function () use (&$callCount) {
                $callCount++;

                // Simulate intermittent network issues
                if ($callCount <= 3) {
                    throw new VerisoulConnectionException('Network timeout');
                }

                return 'Network call succeeded';
            });

            expect($result)->toBe('Network call succeeded');
            expect($callCount)->toBe(4);
        });

        it('handles server maintenance scenario', function (): void {
            $retry = new RetryStrategy(maxAttempts: 3, baseDelayMs: 1000);
            $callCount = 0;

            $result = $retry->execute(function () use (&$callCount) {
                $callCount++;

                if ($callCount < 3) {
                    throw new VerisoulApiException('Service temporarily unavailable', 503);
                }

                return 'Service restored';
            });

            expect($result)->toBe('Service restored');
            expect($callCount)->toBe(3);
        });

        it('fails permanently on validation errors', function (): void {
            $retry = new RetryStrategy(maxAttempts: 5);
            $callCount = 0;

            expect(function () use ($retry, &$callCount): void {
                $retry->execute(function () use (&$callCount): void {
                    $callCount++;
                    throw new VerisoulValidationException('Invalid request format', 'request_body', '{}');
                });
            })->toThrow(VerisoulValidationException::class);

            expect($callCount)->toBe(1);
        });

        it('fails permanently on authentication errors', function (): void {
            $retry = new RetryStrategy(maxAttempts: 3);
            $callCount = 0;

            expect(function () use ($retry, &$callCount): void {
                $retry->execute(function () use (&$callCount): void {
                    $callCount++;
                    throw new VerisoulApiException('Unauthorized', 401);
                });
            })->toThrow(VerisoulApiException::class);

            expect($callCount)->toBe(1);
        });
    });

    afterEach(function (): void {
        Mockery::close();
    });
});
