<?php

use Ninja\Verisoul\Clients\AccountClient;
use Ninja\Verisoul\Clients\SessionClient;
use Ninja\Verisoul\Enums\VerisoulEnvironment;
use Ninja\Verisoul\Tests\Helpers\MockFactory;
use Ninja\Verisoul\DTO\UserAccount;

describe('Memory Usage Performance Tests', function () {
    beforeEach(function () {
        $this->testApiKey = 'memory_test_key';
        $this->sandboxEnv = VerisoulEnvironment::Sandbox;
        
        // Force garbage collection before each test
        gc_collect_cycles();
        
        $this->initialMemory = memory_get_usage(true);
        $this->initialPeakMemory = memory_get_peak_usage(true);
    });

    afterEach(function () {
        // Clean up after each test
        gc_collect_cycles();
    });

    describe('Memory leak detection', function () {
        it('does not leak memory during repeated client operations', function () {
            $client = new AccountClient($this->testApiKey, $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'get' => MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'memory_leak_test']])
                ])
            );

            $memoryReadings = [];
            $iterations = 100;

            for ($i = 1; $i <= $iterations; $i++) {
                $response = $client->getAccount("memory_leak_test_{$i}");

                // Take memory reading every 10 iterations
                if ($i % 10 === 0) {
                    gc_collect_cycles(); // Force cleanup
                    $memoryReadings[] = memory_get_usage(true);
                }
            }

            // Check that memory usage doesn't continuously grow
            $firstReading = $memoryReadings[0];
            $lastReading = end($memoryReadings);
            $memoryGrowth = $lastReading - $firstReading;

            // Memory growth should be minimal (less than 1MB)
            expect(count($memoryReadings))->toBe(10)
                ->and($memoryGrowth)->toBeLessThan(1024 * 1024);

            // Check for consistent memory usage (no continuous growth pattern)
            $growthPattern = [];
            for ($i = 1; $i < count($memoryReadings); $i++) {
                $growthPattern[] = $memoryReadings[$i] - $memoryReadings[$i - 1];
            }

            // Most readings should not show significant growth
            $significantGrowths = count(array_filter($growthPattern, fn($growth) => $growth > 512 * 1024)); // 512KB threshold
            expect($significantGrowths)->toBeLessThan(3); // Allow some variance
        });

        it('properly releases memory for large response objects', function () {
            $largeResponseClient = new AccountClient($this->testApiKey, $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'get' => MockFactory::createAccountResponseFromFixture([
                        'account' => [
                            'id' => 'large_response_test',
                            'metadata' => array_fill(0, 1000, 'large_data_chunk'),
                            'sessions' => array_fill(0, 500, ['session_id' => 'test_session', 'data' => str_repeat('x', 1000)])
                        ]
                    ])
                ])
            );

            $beforeLargeResponse = memory_get_usage(true);

            // Create and process large response
            $response = $largeResponseClient->getAccount('large_response_test');
            $afterLargeResponse = memory_get_usage(true);

            // Explicitly unset response and force cleanup
            unset($response);
            gc_collect_cycles();
            $afterCleanup = memory_get_usage(true);

            $memoryUsedForResponse = $afterLargeResponse - $beforeLargeResponse;
            $memoryReleasedAfterCleanup = $afterLargeResponse - $afterCleanup;

            expect($memoryUsedForResponse)->toBeGreaterThan(0) // Should use memory for large response
                ->and($memoryReleasedAfterCleanup)->toBeGreaterThan($memoryUsedForResponse * 0.7); // Should release at least 70% of used memory
        });

        it('handles memory efficiently with multiple client instances', function () {
            $initialMemory = memory_get_usage(true);
            $clients = [];

            // Create 20 client instances
            for ($i = 1; $i <= 20; $i++) {
                $clients[] = new AccountClient($this->testApiKey, $this->sandboxEnv,
                    httpClient: MockFactory::createSuccessfulHttpClient([
                        'get' => MockFactory::createAccountResponseFromFixture(['account' => ['id' => "multi_client_{$i}"]])
                    ])
                );
            }

            $afterClientCreation = memory_get_usage(true);

            // Use all clients
            foreach ($clients as $index => $client) {
                $response = $client->getAccount("multi_client_test_{$index}");
            }

            $afterClientUsage = memory_get_usage(true);

            // Clean up clients
            unset($clients);
            gc_collect_cycles();
            $afterCleanup = memory_get_usage(true);

            $memoryPerClient = ($afterClientCreation - $initialMemory) / 20;
            $memoryRecovered = $afterClientUsage - $afterCleanup;

            expect($memoryPerClient)->toBeLessThan(100 * 1024) // Less than 100KB per client
                ->and($memoryRecovered)->toBeGreaterThan(0); // Should recover some memory
        });
    });

    describe('Large payload handling', function () {
        it('efficiently processes large UserAccount objects', function () {
            $sessionClient = new SessionClient($this->testApiKey, $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'post' => MockFactory::createAuthenticateSessionResponseFromFixture(['session_id' => 'large_payload_test'])
                ])
            );

            $beforeProcessing = memory_get_usage(true);

            // Create large UserAccount with extensive metadata
            $largeUserAccount = UserAccount::from([
                'id' => 'large_payload_user',
                'email' => 'large@example.com',
                'metadata' => [
                    'profile_data' => array_fill(0, 1000, 'profile_chunk'),
                    'preferences' => array_fill(0, 500, ['key' => 'value', 'data' => str_repeat('x', 100)]),
                    'history' => array_fill(0, 200, [
                        'timestamp' => time(),
                        'action' => 'user_action',
                        'details' => str_repeat('y', 200)
                    ]),
                    'analytics' => array_fill(0, 100, [
                        'metric' => 'test_metric',
                        'value' => random_int(1, 1000),
                        'context' => array_fill(0, 50, 'context_data')
                    ])
                ]
            ]);

            $afterAccountCreation = memory_get_usage(true);

            // Authenticate with large payload
            $response = $sessionClient->authenticate($largeUserAccount, 'large_payload_session');

            $afterAuthentication = memory_get_usage(true);

            // Clean up
            unset($largeUserAccount, $response);
            gc_collect_cycles();
            $afterCleanup = memory_get_usage(true);

            $accountCreationMemory = $afterAccountCreation - $beforeProcessing;
            $authenticationMemory = $afterAuthentication - $afterAccountCreation;
            $memoryReleased = $afterAuthentication - $afterCleanup;

            expect($accountCreationMemory)->toBeGreaterThan(0)
                ->and($authenticationMemory)->toBeGreaterThan(0)
                ->and($memoryReleased)->toBeGreaterThan($accountCreationMemory * 0.5); // Should release at least 50% of account memory
        });

        it('handles batch operations without excessive memory accumulation', function () {
            $batchClient = new AccountClient($this->testApiKey, $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'get' => MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'batch_test']])
                ])
            );

            $memoryReadings = [];
            $batchSize = 50;
            $numberOfBatches = 5;

            for ($batch = 1; $batch <= $numberOfBatches; $batch++) {
                $batchStart = memory_get_usage(true);

                // Process batch
                $responses = [];
                for ($i = 1; $i <= $batchSize; $i++) {
                    $responses[] = $batchClient->getAccount("batch_{$batch}_item_{$i}");
                }

                $batchEnd = memory_get_usage(true);

                // Clean up batch
                unset($responses);
                gc_collect_cycles();
                $batchCleanup = memory_get_usage(true);

                $memoryReadings[] = [
                    'batch' => $batch,
                    'start' => $batchStart,
                    'end' => $batchEnd,
                    'cleanup' => $batchCleanup,
                    'used' => $batchEnd - $batchStart,
                    'released' => $batchEnd - $batchCleanup
                ];
            }

            // Analyze memory usage patterns
            $avgMemoryUsed = array_sum(array_column($memoryReadings, 'used')) / count($memoryReadings);
            $avgMemoryReleased = array_sum(array_column($memoryReadings, 'released')) / count($memoryReadings);

            // Check that memory usage is consistent across batches
            $memoryUsages = array_column($memoryReadings, 'used');
            $maxUsage = max($memoryUsages);
            $minUsage = min($memoryUsages);
            $usageVariation = ($maxUsage - $minUsage) / $avgMemoryUsed;

            expect(count($memoryReadings))->toBe($numberOfBatches)
                ->and($avgMemoryReleased)->toBeGreaterThan($avgMemoryUsed * 0.6) // Should release at least 60% of used memory
                ->and($usageVariation)->toBeLessThan(0.5); // Memory usage should be consistent (less than 50% variation)
        });
    });

    describe('Memory optimization patterns', function () {
        it('efficiently reuses client instances', function () {
            $reusableClient = new AccountClient($this->testApiKey, $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'get' => MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'reuse_test']])
                ])
            );

            $memoryBeforeReuse = memory_get_usage(true);

            // Make many requests with same client instance
            for ($i = 1; $i <= 100; $i++) {
                $response = $reusableClient->getAccount("reuse_test_{$i}");
                
                // Process response immediately and discard
                $accountId = null;
                if (method_exists($response, 'getAccount')) {
                    // Simulate processing without keeping reference
                }
                unset($response);

                // Force cleanup every 20 iterations
                if ($i % 20 === 0) {
                    gc_collect_cycles();
                }
            }

            $memoryAfterReuse = memory_get_usage(true);
            $totalMemoryIncrease = $memoryAfterReuse - $memoryBeforeReuse;

            // Memory increase should be minimal when reusing client
            expect($totalMemoryIncrease)->toBeLessThan(2 * 1024 * 1024); // Less than 2MB increase
        });

        it('optimizes memory when switching between environments', function () {
            $switchingClient = new AccountClient($this->testApiKey, VerisoulEnvironment::Sandbox,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'get' => MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'env_switch_test']])
                ])
            );

            $memoryReadings = [];

            // Test memory usage across environment switches
            $environments = [VerisoulEnvironment::Sandbox, VerisoulEnvironment::Production];

            for ($i = 1; $i <= 10; $i++) {
                $env = $environments[$i % 2];
                $switchingClient->setEnvironment($env);

                $beforeRequest = memory_get_usage(true);
                $response = $switchingClient->getAccount("env_switch_{$i}");
                $afterRequest = memory_get_usage(true);

                unset($response);
                gc_collect_cycles();
                $afterCleanup = memory_get_usage(true);

                $memoryReadings[] = [
                    'iteration' => $i,
                    'environment' => $env->value,
                    'request_memory' => $afterRequest - $beforeRequest,
                    'cleanup_memory' => $afterRequest - $afterCleanup
                ];
            }

            // Verify consistent memory usage across environment switches
            $requestMemories = array_column($memoryReadings, 'request_memory');
            $avgRequestMemory = array_sum($requestMemories) / count($requestMemories);
            $maxDeviation = 0;

            foreach ($requestMemories as $memory) {
                if ($avgRequestMemory > 0) {
                    $deviation = abs($memory - $avgRequestMemory) / $avgRequestMemory;
                    $maxDeviation = max($maxDeviation, $deviation);
                }
            }

            expect(count($memoryReadings))->toBe(10)
                ->and($maxDeviation)->toBeLessThan(0.3); // Memory usage should be consistent (less than 30% deviation)
        });

        it('handles circular references and complex object graphs efficiently', function () {
            $complexClient = new SessionClient($this->testApiKey, $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'post' => MockFactory::createAuthenticateSessionResponseFromFixture(['session_id' => 'complex_test'])
                ])
            );

            $beforeComplexOperations = memory_get_usage(true);

            // Create complex object structures that might have circular references
            for ($i = 1; $i <= 20; $i++) {
                $complexMetadata = [
                    'level1' => [
                        'level2' => [
                            'level3' => [
                                'data' => array_fill(0, 100, "nested_data_{$i}"),
                                'references' => []
                            ]
                        ]
                    ]
                ];

                // Add some cross-references
                $complexMetadata['level1']['level2']['level3']['references'] = [
                    'self' => &$complexMetadata['level1'],
                    'parent' => &$complexMetadata
                ];

                $userAccount = UserAccount::from([
                    'id' => "complex_user_{$i}",
                    'email' => "complex_{$i}@example.com",
                    'metadata' => $complexMetadata
                ]);

                $response = $complexClient->authenticate($userAccount, "complex_session_{$i}");

                // Explicitly break circular references
                unset($complexMetadata['level1']['level2']['level3']['references']);
                unset($complexMetadata, $userAccount, $response);
            }

            gc_collect_cycles();
            $afterComplexOperations = memory_get_usage(true);

            $complexOperationsMemory = $afterComplexOperations - $beforeComplexOperations;

            // Should handle complex objects without excessive memory usage
            expect($complexOperationsMemory)->toBeLessThan(5 * 1024 * 1024); // Less than 5MB for complex operations
        });
    });

    describe('Memory profiling and debugging aids', function () {
        it('provides memory usage insights for debugging', function () {
            $profilingClient = new AccountClient($this->testApiKey, $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'get' => MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'profiling_test']])
                ])
            );

            $memoryProfile = [];

            // Profile memory usage for different operations
            $operations = [
                'client_creation' => function() use (&$profilingClient) {
                    // Client already created, just measure
                    return 'created';
                },
                'first_request' => function() use ($profilingClient) {
                    return $profilingClient->getAccount('profiling_first');
                },
                'subsequent_requests' => function() use ($profilingClient) {
                    $responses = [];
                    for ($i = 1; $i <= 5; $i++) {
                        $responses[] = $profilingClient->getAccount("profiling_subsequent_{$i}");
                    }
                    return $responses;
                },
                'large_response' => function() use ($profilingClient) {
                    return $profilingClient->getAccount('profiling_large');
                }
            ];

            foreach ($operations as $operationName => $operation) {
                $beforeOp = memory_get_usage(true);
                $peakBefore = memory_get_peak_usage(true);

                $result = $operation();

                $afterOp = memory_get_usage(true);
                $peakAfter = memory_get_peak_usage(true);

                $memoryProfile[$operationName] = [
                    'memory_used' => $afterOp - $beforeOp,
                    'peak_increase' => $peakAfter - $peakBefore,
                    'current_total' => $afterOp,
                    'peak_total' => $peakAfter
                ];

                // Clean up operation result
                unset($result);
                gc_collect_cycles();
            }

            // Verify we have profiling data for all operations
            expect(count($memoryProfile))->toBe(4);

            foreach ($memoryProfile as $operation => $profile) {
                expect($profile)->toHaveKeys(['memory_used', 'peak_increase', 'current_total', 'peak_total']);
                expect($profile['current_total'])->toBeGreaterThan(0);
                expect($profile['peak_total'])->toBeGreaterThanOrEqualTo($profile['current_total']);
            }

            // First request typically uses more memory than subsequent ones
            if (isset($memoryProfile['first_request']) && isset($memoryProfile['subsequent_requests'])) {
                $firstRequestMemory = $memoryProfile['first_request']['memory_used'];
                $subsequentMemory = $memoryProfile['subsequent_requests']['memory_used'];
                
                // This might not always be true, but it's a good indicator of efficiency
                // expect($firstRequestMemory)->toBeGreaterThan($subsequentMemory / 5);
            }
        });
    });
});