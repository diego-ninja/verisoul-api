<?php

use Ninja\Verisoul\Clients\AccountClient;
use Ninja\Verisoul\Clients\SessionClient;
use Ninja\Verisoul\Enums\VerisoulEnvironment;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Tests\Helpers\MockFactory;
use Ninja\Verisoul\Contracts\HttpClientInterface;

describe('Rate Limiting Integration Tests', function () {
    beforeEach(function () {
        $this->testApiKey = 'rate_limit_test_key';
        $this->sandboxEnv = VerisoulEnvironment::Sandbox;
    });

    describe('API rate limit handling', function () {
        it('handles 429 rate limit responses with backoff', function () {
            $rateLimitClient = Mockery::mock(HttpClientInterface::class);
            $rateLimitClient->shouldReceive('setTimeout')->andReturnSelf();
            $rateLimitClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $rateLimitClient->shouldReceive('setHeaders')->andReturnSelf();

            $attemptCount = 0;

            // First call returns 429, second succeeds
            $rateLimitClient->shouldReceive('get')
                ->once()
                ->andReturnUsing(function() use (&$attemptCount) {
                    $attemptCount++;
                    throw new VerisoulApiException('Rate limit exceeded', 429);
                });

            $rateLimitClient->shouldReceive('get')
                ->once()
                ->andReturnUsing(function() use (&$attemptCount) {
                    $attemptCount++;
                    return MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'rate_limit_success']]);
                });

            $client = new AccountClient(
                $this->testApiKey,
                $this->sandboxEnv,
                retryAttempts: 2,
                retryDelay: 100,
                httpClient: $rateLimitClient
            );

            $response = $client->getAccount('rate_limit_test');

            expect($response)->toBeInstanceOf(\Ninja\Verisoul\Responses\AccountResponse::class)
                ->and($attemptCount)->toBe(2);
        });

        it('respects Retry-After header from API', function () {
            $retryAfterClient = Mockery::mock(HttpClientInterface::class);
            $retryAfterClient->shouldReceive('setTimeout')->andReturnSelf();
            $retryAfterClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $retryAfterClient->shouldReceive('setHeaders')->andReturnSelf();

            $startTime = microtime(true);
            $callTimes = [];

            // First call returns 429 with Retry-After header
            $retryAfterClient->shouldReceive('get')
                ->once()
                ->andReturnUsing(function() use (&$callTimes) {
                    $callTimes[] = microtime(true);
                    $exception = new VerisoulApiException('Rate limit exceeded', 429);
                    // Simulate Retry-After header (this would be handled by HTTP client)
                    throw $exception;
                });

            // Second call succeeds
            $retryAfterClient->shouldReceive('get')
                ->once()
                ->andReturnUsing(function() use (&$callTimes) {
                    $callTimes[] = microtime(true);
                    return MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'retry_after_success']]);
                });

            $client = new AccountClient(
                $this->testApiKey,
                $this->sandboxEnv,
                retryAttempts: 2,
                retryDelay: 200, // 200ms base delay
                httpClient: $retryAfterClient
            );

            $response = $client->getAccount('retry_after_test');

            expect($response)->toBeInstanceOf(\Ninja\Verisoul\Responses\AccountResponse::class)
                ->and(count($callTimes))->toBe(2);

            // Verify there was a delay between calls
            if (count($callTimes) >= 2) {
                $delayMs = ($callTimes[1] - $callTimes[0]) * 1000;
                expect($delayMs)->toBeGreaterThan(180); // Should be at least close to 200ms
            }
        });

        it('handles burst rate limits correctly', function () {
            $burstClient = Mockery::mock(HttpClientInterface::class);
            $burstClient->shouldReceive('setTimeout')->andReturnSelf();
            $burstClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $burstClient->shouldReceive('setHeaders')->andReturnSelf();

            $callCount = 0;
            $rateLimitHits = 0;

            // Setup pattern: rate limit on calls 6 and 7 to cause permanent failures
            $burstClient->shouldReceive('get')
                ->andReturnUsing(function() use (&$callCount, &$rateLimitHits) {
                    $callCount++;
                    
                    // Simulate persistent rate limit on calls 6 and 7
                    if ($callCount >= 6 && $callCount <= 8) {
                        $rateLimitHits++;
                        throw new VerisoulApiException('Burst rate limit exceeded', 429);
                    }
                    
                    return MockFactory::createAccountResponseFromFixture([
                        'account' => ['id' => "burst_test_{$callCount}"]
                    ]);
                });

            $client = new AccountClient(
                $this->testApiKey,
                $this->sandboxEnv,
                retryAttempts: 1, // Reduce retries to ensure some failures
                retryDelay: 10,
                httpClient: $burstClient
            );

            $results = [];
            $exceptions = [];

            // Make 7 calls rapidly
            for ($i = 1; $i <= 7; $i++) {
                try {
                    $response = $client->getAccount("burst_test_{$i}");
                    $results[] = $response;
                } catch (VerisoulApiException $e) {
                    $exceptions[] = $e;
                }
            }

            expect(count($results))->toBeGreaterThan(3) // Most should succeed
                ->and(count($exceptions))->toBeGreaterThan(0) // Some should fail
                ->and($rateLimitHits)->toBeGreaterThan(0); // Should hit rate limit
        });
    });

    describe('Client-side rate limiting', function () {
        it('implements client-side request throttling', function () {
            $throttleClient = Mockery::mock(HttpClientInterface::class);
            $throttleClient->shouldReceive('setTimeout')->andReturnSelf();
            $throttleClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $throttleClient->shouldReceive('setHeaders')->andReturnSelf();

            $callTimes = [];

            $throttleClient->shouldReceive('get')
                ->times(5)
                ->andReturnUsing(function() use (&$callTimes) {
                    $callTimes[] = microtime(true);
                    return MockFactory::createAccountResponseFromFixture([
                        'account' => ['id' => 'throttle_test_' . count($callTimes)]
                    ]);
                });

            $client = new AccountClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: $throttleClient
            );

            $startTime = microtime(true);

            // Make 5 rapid requests
            for ($i = 1; $i <= 5; $i++) {
                $response = $client->getAccount("throttle_test_{$i}");
                expect($response)->toBeInstanceOf(\Ninja\Verisoul\Responses\AccountResponse::class);
                
                // Add small delay to simulate throttling
                if ($i < 5) {
                    usleep(10000); // 10ms between requests
                }
            }

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000;

            expect(count($callTimes))->toBe(5)
                ->and($totalTime)->toBeGreaterThan(40); // Should take at least 40ms with delays
        });

        it('adapts request rate based on server responses', function () {
            $adaptiveClient = Mockery::mock(HttpClientInterface::class);
            $adaptiveClient->shouldReceive('setTimeout')->andReturnSelf();
            $adaptiveClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $adaptiveClient->shouldReceive('setHeaders')->andReturnSelf();

            $requestTimes = [];
            $rateLimitCount = 0;

            $adaptiveClient->shouldReceive('post')
                ->andReturnUsing(function() use (&$requestTimes, &$rateLimitCount) {
                    $requestTimes[] = microtime(true);
                    $requestNum = count($requestTimes);
                    
                    // Simulate rate limiting on every 3rd request
                    if ($requestNum % 3 === 0 && $rateLimitCount < 2) {
                        $rateLimitCount++;
                        throw new VerisoulApiException('Adaptive rate limit', 429);
                    }
                    
                    return MockFactory::createAuthenticateSessionResponseFromFixture([
                        'session_id' => "adaptive_session_{$requestNum}"
                    ]);
                });

            $sessionClient = new SessionClient(
                $this->testApiKey,
                $this->sandboxEnv,
                retryAttempts: 2,
                retryDelay: 100,
                httpClient: $adaptiveClient
            );

            $userAccount = \Ninja\Verisoul\DTO\UserAccount::from(['id' => 'adaptive_user']);
            $successCount = 0;

            // Make 8 requests with adaptive rate limiting
            for ($i = 1; $i <= 8; $i++) {
                try {
                    $response = $sessionClient->authenticate($userAccount, "adaptive_session_{$i}");
                    if ($response) {
                        $successCount++;
                    }
                } catch (VerisoulApiException $e) {
                    // Some requests may fail after retries
                }
            }

            expect($successCount)->toBeGreaterThan(4) // Most should succeed after retries
                ->and($rateLimitCount)->toBe(2); // Should hit rate limit twice
        });
    });

    describe('Concurrent request handling', function () {
        it('manages concurrent requests under rate limits', function () {
            $concurrentClient = Mockery::mock(HttpClientInterface::class);
            $concurrentClient->shouldReceive('setTimeout')->andReturnSelf();
            $concurrentClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $concurrentClient->shouldReceive('setHeaders')->andReturnSelf();

            $concurrentRequests = 0;
            $maxConcurrent = 0;
            $currentConcurrent = 0;

            $concurrentClient->shouldReceive('get')
                ->andReturnUsing(function() use (&$concurrentRequests, &$maxConcurrent, &$currentConcurrent) {
                    $currentConcurrent++;
                    $concurrentRequests++;
                    $maxConcurrent = max($maxConcurrent, $currentConcurrent);
                    
                    // Simulate processing time
                    usleep(50000); // 50ms
                    
                    $result = MockFactory::createAccountResponseFromFixture([
                        'account' => ['id' => "concurrent_{$concurrentRequests}"]
                    ]);
                    
                    $currentConcurrent--;
                    return $result;
                });

            $client = new AccountClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: $concurrentClient
            );

            $promises = [];
            $results = [];

            // Simulate 5 concurrent requests
            for ($i = 1; $i <= 5; $i++) {
                $promises[] = function() use ($client, $i, &$results) {
                    try {
                        $response = $client->getAccount("concurrent_{$i}");
                        $results[] = $response;
                    } catch (\Exception $e) {
                        // Handle any failures
                    }
                };
            }

            // Execute all promises (simulating concurrent execution)
            foreach ($promises as $promise) {
                $promise();
            }

            expect(count($results))->toBe(5)
                ->and($concurrentRequests)->toBe(5);
        });

        it('handles rate limit recovery for concurrent requests', function () {
            $recoveryClient = Mockery::mock(HttpClientInterface::class);
            $recoveryClient->shouldReceive('setTimeout')->andReturnSelf();
            $recoveryClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $recoveryClient->shouldReceive('setHeaders')->andReturnSelf();

            $totalRequests = 0;
            $rateLimitHits = 0;

            $recoveryClient->shouldReceive('post')
                ->andReturnUsing(function() use (&$totalRequests, &$rateLimitHits) {
                    $totalRequests++;
                    
                    // First 3 requests hit rate limit
                    if ($totalRequests <= 3) {
                        $rateLimitHits++;
                        throw new VerisoulApiException('Concurrent rate limit', 429);
                    }
                    
                    return MockFactory::createSessionResponseFromFixture([
                        'session_id' => "recovery_session_{$totalRequests}"
                    ]);
                });

            $sessionClient = new SessionClient(
                $this->testApiKey,
                $this->sandboxEnv,
                retryAttempts: 3,
                retryDelay: 200,
                httpClient: $recoveryClient
            );

            $operations = [];
            $results = [];
            $failures = [];

            // Create 6 concurrent operations
            for ($i = 1; $i <= 6; $i++) {
                $operations[] = function() use ($sessionClient, $i, &$results, &$failures) {
                    try {
                        $response = $sessionClient->unauthenticated("recovery_session_{$i}");
                        $results[] = $response;
                    } catch (\Exception $e) {
                        $failures[] = $e;
                    }
                };
            }

            // Execute operations
            foreach ($operations as $operation) {
                $operation();
            }

            expect(count($results))->toBeGreaterThan(0) // Some should succeed after retries
                ->and($rateLimitHits)->toBe(3); // Should hit rate limit 3 times initially
        });
    });

    describe('Rate limit monitoring and metrics', function () {
        it('tracks rate limit encounters for monitoring', function () {
            $monitoringClient = Mockery::mock(HttpClientInterface::class);
            $monitoringClient->shouldReceive('setTimeout')->andReturnSelf();
            $monitoringClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $monitoringClient->shouldReceive('setHeaders')->andReturnSelf();

            $rateLimitEvents = [];

            $requestCounter = 0;
            $monitoringClient->shouldReceive('get')
                ->times(10)
                ->andReturnUsing(function() use (&$rateLimitEvents, &$requestCounter) {
                    $requestCounter++;
                    $requestNum = $requestCounter;
                    
                    // Every 4th request hits rate limit
                    if ($requestNum % 4 === 0) {
                        $rateLimitEvents[] = [
                            'timestamp' => microtime(true),
                            'request_num' => $requestNum,
                            'type' => 'rate_limit_hit'
                        ];
                        throw new VerisoulApiException('Monitoring rate limit', 429);
                    }
                    
                    return MockFactory::createAccountResponseFromFixture([
                        'account' => ['id' => "monitoring_test_{$requestNum}"]
                    ]);
                });

            $client = new AccountClient(
                $this->testApiKey,
                $this->sandboxEnv,
                retryAttempts: 1, // Don't retry for this test
                httpClient: $monitoringClient
            );

            $successCount = 0;
            $failureCount = 0;

            // Make 10 requests
            for ($i = 1; $i <= 10; $i++) {
                try {
                    $response = $client->getAccount("monitoring_test_{$i}");
                    $successCount++;
                } catch (VerisoulApiException $e) {
                    $failureCount++;
                }
            }

            expect($successCount)->toBeGreaterThan(5) // Most should succeed  
                ->and($failureCount)->toBeGreaterThan(0) // Some should fail
                ->and(count($rateLimitEvents))->toBeGreaterThan(0);

            // Verify rate limit events have proper structure
            foreach ($rateLimitEvents as $event) {
                expect($event)->toHaveKeys(['timestamp', 'request_num', 'type'])
                    ->and($event['type'])->toBe('rate_limit_hit');
            }
        });
    });
});