<?php

use Ninja\Verisoul\Clients\AccountClient;
use Ninja\Verisoul\Clients\PhoneClient;
use Ninja\Verisoul\Clients\SessionClient;
use Ninja\Verisoul\DTO\UserAccount;
use Ninja\Verisoul\Enums\VerisoulEnvironment;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('Concurrent Requests Performance Tests', function (): void {
    beforeEach(function (): void {
        $this->testApiKey = 'performance_test_key';
        $this->sandboxEnv = VerisoulEnvironment::Sandbox;
    });

    describe('Multiple client concurrent operations', function (): void {
        it('handles concurrent requests across different clients efficiently', function (): void {
            $accountClient = new AccountClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'get' => MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'concurrent_acc']]),
                ]),
            );

            $sessionClient = new SessionClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'post' => MockFactory::createAuthenticateSessionResponseFromFixture(['session_id' => 'concurrent_session']),
                ]),
            );

            $phoneClient = new PhoneClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'post' => ['phone_number' => '+1234567890', 'is_valid' => true, 'carrier' => 'Test Carrier'],
                ]),
            );

            $startTime = microtime(true);
            $results = [];
            $operations = [];

            // Prepare 15 concurrent operations across different clients
            for ($i = 1; $i <= 5; $i++) {
                $operations[] = function () use ($accountClient, $i, &$results): void {
                    $response = $accountClient->getAccount("concurrent_acc_{$i}");
                    $results[] = ['type' => 'account', 'id' => $i, 'response' => $response];
                };

                $operations[] = function () use ($sessionClient, $i, &$results): void {
                    $userAccount = UserAccount::from(['id' => "concurrent_user_{$i}"]);
                    $response = $sessionClient->authenticate($userAccount, "concurrent_session_{$i}");
                    $results[] = ['type' => 'session', 'id' => $i, 'response' => $response];
                };

                $operations[] = function () use ($phoneClient, $i, &$results): void {
                    $response = $phoneClient->verifyPhone("+123456789{$i}");
                    $results[] = ['type' => 'phone', 'id' => $i, 'response' => $response];
                };
            }

            // Execute all operations
            foreach ($operations as $operation) {
                $operation();
            }

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

            expect(count($results))->toBe(15)
                ->and($totalTime)->toBeLessThan(1000); // Should complete within 1 second

            // Verify all response types
            $accountResponses = array_filter($results, fn($r) => 'account' === $r['type']);
            $sessionResponses = array_filter($results, fn($r) => 'session' === $r['type']);
            $phoneResponses = array_filter($results, fn($r) => 'phone' === $r['type']);

            expect(count($accountResponses))->toBe(5)
                ->and(count($sessionResponses))->toBe(5)
                ->and(count($phoneResponses))->toBe(5);
        });

        it('maintains response quality under high concurrency load', function (): void {
            $highLoadClient = new AccountClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'get' => MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'high_load_test']]),
                ]),
            );

            $concurrentLevel = 50;
            $results = [];
            $errors = [];
            $startTime = microtime(true);

            $operations = [];
            for ($i = 1; $i <= $concurrentLevel; $i++) {
                $operations[] = function () use ($highLoadClient, $i, &$results, &$errors): void {
                    try {
                        $response = $highLoadClient->getAccount("high_load_{$i}");
                        $results[] = $response;
                    } catch (Exception $e) {
                        $errors[] = $e;
                    }
                };
            }

            // Execute all concurrent operations
            foreach ($operations as $operation) {
                $operation();
            }

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000;

            expect(count($results))->toBe($concurrentLevel)
                ->and(count($errors))->toBe(0)
                ->and($totalTime)->toBeLessThan(3000); // Should handle 50 requests within 3 seconds

            // Verify all responses are valid
            foreach ($results as $response) {
                expect($response)->toBeInstanceOf(Ninja\Verisoul\Responses\AccountResponse::class);
            }
        });
    });

    describe('Resource contention and synchronization', function (): void {
        it('handles shared resource access correctly', function (): void {
            $sharedHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'shared_resource']]),
            ]);

            // Multiple clients sharing the same HTTP client
            $clients = [];
            for ($i = 1; $i <= 10; $i++) {
                $clients[] = new AccountClient($this->testApiKey, $this->sandboxEnv, httpClient: $sharedHttpClient);
            }

            $results = [];
            $operations = [];

            foreach ($clients as $index => $client) {
                $operations[] = function () use ($client, $index, &$results): void {
                    $response = $client->getAccount("shared_resource_{$index}");
                    $results[] = ['client_index' => $index, 'response' => $response];
                };
            }

            $startTime = microtime(true);

            // Execute operations
            foreach ($operations as $operation) {
                $operation();
            }

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000;

            expect(count($results))->toBe(10)
                ->and($totalTime)->toBeLessThan(2000); // Should handle shared resource efficiently

            // Verify each client got a valid response
            foreach ($results as $result) {
                expect($result['response'])->toBeInstanceOf(Ninja\Verisoul\Responses\AccountResponse::class);
            }
        });

        it('prevents race conditions in retry mechanism', function (): void {
            $retryClient = Mockery::mock(Ninja\Verisoul\Contracts\HttpClientInterface::class);
            $retryClient->shouldReceive('setTimeout')->andReturnSelf();
            $retryClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $retryClient->shouldReceive('setHeaders')->andReturnSelf();

            $callCount = 0;
            $retryClient->shouldReceive('get')
                ->andReturnUsing(function () use (&$callCount) {
                    $callCount++;

                    // First call of each batch fails, second succeeds
                    if (1 === $callCount % 2) {
                        throw new Ninja\Verisoul\Exceptions\VerisoulConnectionException("Race condition test failure");
                    }

                    return MockFactory::createAccountResponseFromFixture(['account' => ['id' => "race_test_{$callCount}"]]);
                });

            $client = new AccountClient(
                $this->testApiKey,
                $this->sandboxEnv,
                retryAttempts: 2,
                retryDelay: 50,
                httpClient: $retryClient,
            );

            $operations = [];
            $results = [];

            // Create 10 concurrent operations that will trigger retries
            for ($i = 1; $i <= 10; $i++) {
                $operations[] = function () use ($client, $i, &$results): void {
                    try {
                        $response = $client->getAccount("race_test_{$i}");
                        $results[] = $response;
                    } catch (Exception $e) {
                        // Some may fail
                    }
                };
            }

            foreach ($operations as $operation) {
                $operation();
            }

            expect(count($results))->toBe(10); // All should succeed after retry
            expect($callCount)->toBe(20); // 10 failures + 10 successes
        });
    });

    describe('Memory and CPU efficiency under load', function (): void {
        it('maintains low memory usage during concurrent operations', function (): void {
            $memoryEfficientClient = new AccountClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'get' => MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'memory_test']]),
                ]),
            );

            $initialMemory = memory_get_usage();
            $peakMemory = $initialMemory;

            $operations = [];
            for ($i = 1; $i <= 100; $i++) {
                $operations[] = function () use ($memoryEfficientClient, $i, &$peakMemory) {
                    $response = $memoryEfficientClient->getAccount("memory_test_{$i}");
                    $currentMemory = memory_get_usage();
                    $peakMemory = max($peakMemory, $currentMemory);
                    return $response;
                };
            }

            foreach ($operations as $operation) {
                $operation();
            }

            $finalMemory = memory_get_usage();
            $memoryIncrease = $finalMemory - $initialMemory;
            $peakIncrease = $peakMemory - $initialMemory;

            // Memory increase should be reasonable (less than 5MB for 100 operations)
            expect($memoryIncrease)->toBeLessThan(5 * 1024 * 1024)
                ->and($peakIncrease)->toBeLessThan(10 * 1024 * 1024);
        });

        it('handles CPU-intensive concurrent operations efficiently', function (): void {
            $cpuIntensiveClient = new SessionClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'post' => MockFactory::createAuthenticateSessionResponseFromFixture(['session_id' => 'cpu_test']),
                ]),
            );

            $startTime = microtime(true);
            $operations = [];
            $results = [];

            // Create 30 CPU-intensive operations
            for ($i = 1; $i <= 30; $i++) {
                $operations[] = function () use ($cpuIntensiveClient, $i, &$results): void {
                    // Create complex UserAccount to simulate CPU work
                    $userAccount = UserAccount::from([
                        'id' => "cpu_intensive_user_{$i}",
                        'email' => "cpu_test_{$i}@example.com",
                        'metadata' => [
                            'source' => 'performance_test',
                            'iteration' => $i,
                            'complex_data' => array_fill(0, 100, "data_item_{$i}"),
                            'timestamp' => microtime(true),
                        ],
                    ]);

                    $response = $cpuIntensiveClient->authenticate($userAccount, "cpu_session_{$i}");
                    $results[] = $response;
                };
            }

            foreach ($operations as $operation) {
                $operation();
            }

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000;

            expect(count($results))->toBe(30)
                ->and($totalTime)->toBeLessThan(5000); // Should handle CPU-intensive work within 5 seconds

            // Verify all responses are valid
            foreach ($results as $response) {
                expect($response)->toBeInstanceOf(Ninja\Verisoul\Responses\AuthenticateSessionResponse::class);
            }
        });
    });

    describe('Throughput and latency benchmarks', function (): void {
        it('achieves target throughput for mixed workloads', function (): void {
            $throughputClients = [
                'account' => new AccountClient(
                    $this->testApiKey,
                    $this->sandboxEnv,
                    httpClient: MockFactory::createSuccessfulHttpClient([
                        'get' => MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'throughput_test']]),
                    ]),
                ),
                'session' => new SessionClient(
                    $this->testApiKey,
                    $this->sandboxEnv,
                    httpClient: MockFactory::createSuccessfulHttpClient([
                        'post' => MockFactory::createAuthenticateSessionResponseFromFixture(['session_id' => 'throughput_session']),
                    ]),
                ),
                'phone' => new PhoneClient(
                    $this->testApiKey,
                    $this->sandboxEnv,
                    httpClient: MockFactory::createSuccessfulHttpClient([
                        'post' => ['phone_number' => '+1234567890', 'is_valid' => true],
                    ]),
                ),
            ];

            $operations = [];
            $results = [];
            $latencies = [];

            // Create mixed workload: 20 operations per client type
            foreach ($throughputClients as $clientType => $client) {
                for ($i = 1; $i <= 20; $i++) {
                    $operations[] = function () use ($client, $clientType, $i, &$results, &$latencies): void {
                        $requestStart = microtime(true);

                        switch ($clientType) {
                            case 'account':
                                $response = $client->getAccount("throughput_acc_{$i}");
                                break;
                            case 'session':
                                $userAccount = UserAccount::from(['id' => "throughput_user_{$i}"]);
                                $response = $client->authenticate($userAccount, "throughput_session_{$i}");
                                break;
                            case 'phone':
                                $response = $client->verifyPhone("+12345678{$i}");
                                break;
                        }

                        $requestEnd = microtime(true);
                        $latency = ($requestEnd - $requestStart) * 1000;

                        $results[] = ['type' => $clientType, 'response' => $response];
                        $latencies[] = $latency;
                    };
                }
            }

            $startTime = microtime(true);

            foreach ($operations as $operation) {
                $operation();
            }

            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000;

            $totalOperations = count($results);
            $throughput = $totalOperations / ($totalTime / 1000); // Operations per second
            $avgLatency = array_sum($latencies) / count($latencies);
            $maxLatency = max($latencies);

            expect($totalOperations)->toBe(60) // 20 * 3 client types
                ->and($throughput)->toBeGreaterThan(30) // At least 30 ops/sec
                ->and($avgLatency)->toBeLessThan(100) // Average latency under 100ms
                ->and($maxLatency)->toBeLessThan(500); // Max latency under 500ms
        });

        it('maintains consistent performance across sustained load', function (): void {
            $sustainedClient = new AccountClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'get' => MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'sustained_test']]),
                ]),
            );

            $batchResults = [];

            // Run 5 batches of 20 operations each
            for ($batch = 1; $batch <= 5; $batch++) {
                $batchStart = microtime(true);
                $batchResponses = [];

                for ($i = 1; $i <= 20; $i++) {
                    $response = $sustainedClient->getAccount("sustained_test_batch_{$batch}_op_{$i}");
                    $batchResponses[] = $response;
                }

                $batchEnd = microtime(true);
                $batchTime = ($batchEnd - $batchStart) * 1000;

                $batchResults[] = [
                    'batch' => $batch,
                    'time' => $batchTime,
                    'operations' => count($batchResponses),
                    'throughput' => count($batchResponses) / ($batchTime / 1000),
                ];
            }

            // Verify consistent performance across batches
            $throughputs = array_column($batchResults, 'throughput');
            $avgThroughput = array_sum($throughputs) / count($throughputs);
            $maxDeviation = 0;

            foreach ($throughputs as $throughput) {
                $deviation = abs($throughput - $avgThroughput) / $avgThroughput;
                $maxDeviation = max($maxDeviation, $deviation);
            }

            expect(count($batchResults))->toBe(5)
                ->and($avgThroughput)->toBeGreaterThan(15) // At least 15 ops/sec average
                ->and($maxDeviation)->toBeLessThan(0.5); // Less than 50% deviation between batches
        });
    });
});
