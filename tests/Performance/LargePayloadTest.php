<?php

use Ninja\Verisoul\Clients\AccountClient;
use Ninja\Verisoul\Clients\SessionClient;
use Ninja\Verisoul\DTO\UserAccount;
use Ninja\Verisoul\Enums\VerisoulEnvironment;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('Large Payload Performance Tests', function (): void {
    beforeEach(function (): void {
        $this->testApiKey = 'large_payload_test_key';
        $this->sandboxEnv = VerisoulEnvironment::Sandbox;
    });

    describe('Large request payload handling', function (): void {
        it('efficiently processes large UserAccount objects with extensive metadata', function (): void {
            $largePayloadClient = new SessionClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'post' => MockFactory::createAuthenticateSessionResponseFromFixture(['session_id' => 'large_payload_test']),
                ]),
            );

            // Create UserAccount with ~1MB of metadata
            $largeMetadata = [
                'profile' => [
                    'personal_info' => array_fill(0, 1000, [
                        'field' => 'value',
                        'data' => str_repeat('x', 100),
                        'timestamp' => microtime(true),
                        'nested' => array_fill(0, 10, 'nested_value'),
                    ]),
                ],
                'preferences' => array_fill(0, 500, [
                    'category' => 'test_category',
                    'settings' => array_fill(0, 20, ['key' => 'value', 'data' => str_repeat('y', 50)]),
                ]),
                'history' => array_fill(0, 200, [
                    'event' => 'user_action',
                    'timestamp' => time(),
                    'details' => str_repeat('z', 500),
                    'context' => array_fill(0, 5, ['context_key' => 'context_value']),
                ]),
                'analytics' => [
                    'sessions' => array_fill(0, 100, [
                        'session_id' => 'sess_' . uniqid(),
                        'duration' => random_int(60, 3600),
                        'events' => array_fill(0, 50, [
                            'type' => 'click',
                            'data' => str_repeat('a', 100),
                        ]),
                    ]),
                ],
            ];

            $largeUserAccount = UserAccount::from([
                'id' => 'large_payload_user',
                'email' => 'large.payload@example.com',
                'metadata' => $largeMetadata,
            ]);

            $startTime = microtime(true);
            $startMemory = memory_get_usage(true);

            $response = $largePayloadClient->authenticate($largeUserAccount, 'large_payload_session');

            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);

            $processingTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
            $memoryUsed = $endMemory - $startMemory;

            expect($response)->toBeInstanceOf(Ninja\Verisoul\Responses\AuthenticateSessionResponse::class)
                ->and($processingTime)->toBeLessThan(1000) // Should process within 1 second
                ->and($memoryUsed)->toBeLessThan(10 * 1024 * 1024); // Should use less than 10MB
        });

        it('handles multiple large payloads sequentially without performance degradation', function (): void {
            $sequentialClient = new SessionClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'post' => MockFactory::createAuthenticateSessionResponseFromFixture(['session_id' => 'sequential_large']),
                ]),
            );

            $processingTimes = [];
            $memoryUsages = [];

            // Process 10 large payloads sequentially
            for ($i = 1; $i <= 10; $i++) {
                // Create unique large payload for each iteration
                $largeBinaryData = str_repeat(chr(random_int(65, 90)), 100000); // 100KB of random data

                $largeUserAccount = UserAccount::from([
                    'id' => "sequential_user_{$i}",
                    'email' => "sequential_{$i}@example.com",
                    'metadata' => [
                        'iteration' => $i,
                        'large_binary' => base64_encode($largeBinaryData),
                        'large_array' => array_fill(0, 1000, "data_chunk_{$i}"),
                        'complex_structure' => [
                            'level1' => array_fill(0, 100, [
                                'level2' => array_fill(0, 10, [
                                    'level3' => str_repeat("nested_{$i}", 50),
                                ]),
                            ]),
                        ],
                    ],
                ]);

                $startTime = microtime(true);
                $startMemory = memory_get_usage(true);

                $response = $sequentialClient->authenticate($largeUserAccount, "sequential_session_{$i}");

                $endTime = microtime(true);
                $endMemory = memory_get_usage(true);

                $processingTimes[] = ($endTime - $startTime) * 1000;
                $memoryUsages[] = $endMemory - $startMemory;

                // Clean up to prevent accumulation
                unset($largeUserAccount, $response, $largeBinaryData);
                gc_collect_cycles();
            }

            // Analyze performance consistency
            $avgProcessingTime = array_sum($processingTimes) / count($processingTimes);
            $maxProcessingTime = max($processingTimes);
            $minProcessingTime = min($processingTimes);

            $avgMemoryUsage = array_sum($memoryUsages) / count($memoryUsages);
            $maxMemoryUsage = max($memoryUsages);

            expect(count($processingTimes))->toBe(10)
                ->and($avgProcessingTime)->toBeLessThan(500) // Average under 500ms
                ->and($maxProcessingTime)->toBeLessThan(1000) // Max under 1 second
                ->and($maxMemoryUsage)->toBeLessThan(15 * 1024 * 1024); // Max memory under 15MB

            // Check for performance degradation (last requests shouldn't be significantly slower)
            $firstHalfAvg = array_sum(array_slice($processingTimes, 0, 5)) / 5;
            $secondHalfAvg = array_sum(array_slice($processingTimes, 5, 5)) / 5;
            $performanceDegradation = ($secondHalfAvg - $firstHalfAvg) / $firstHalfAvg;

            expect($performanceDegradation)->toBeLessThan(0.5); // Less than 50% degradation
        });
    });

    describe('Large response payload handling', function (): void {
        it('efficiently processes large API responses', function (): void {
            // Create mock response with large amount of data
            $largeAccountData = [
                'account' => [
                    'id' => 'large_response_account',
                    'email' => 'large.response@example.com',
                    'metadata' => array_fill(0, 2000, [
                        'key' => 'metadata_key',
                        'value' => str_repeat('response_data', 100),
                        'timestamp' => time(),
                        'nested' => array_fill(0, 20, 'nested_response_data'),
                    ]),
                    'sessions' => array_fill(0, 1000, [
                        'session_id' => 'large_session_' . uniqid(),
                        'created_at' => date('c'),
                        'data' => str_repeat('session_data', 200),
                        'events' => array_fill(0, 100, [
                            'type' => 'event_type',
                            'payload' => str_repeat('event_payload', 50),
                        ]),
                    ]),
                    'analytics' => [
                        'metrics' => array_fill(0, 500, [
                            'name' => 'metric_name',
                            'value' => random_int(1, 1000),
                            'history' => array_fill(0, 100, random_int(1, 100)),
                        ]),
                    ],
                ],
            ];

            $largeResponseClient = new AccountClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'get' => MockFactory::createAccountResponseFromFixture($largeAccountData),
                ]),
            );

            $startTime = microtime(true);
            $startMemory = memory_get_usage(true);

            $response = $largeResponseClient->getAccount('large_response_account');

            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);

            $processingTime = ($endTime - $startTime) * 1000;
            $memoryUsed = $endMemory - $startMemory;

            expect($response)->toBeInstanceOf(Ninja\Verisoul\Responses\AccountResponse::class)
                ->and($processingTime)->toBeLessThan(2000) // Should process within 2 seconds
                ->and($memoryUsed)->toBeLessThan(20 * 1024 * 1024); // Should use less than 20MB

            // Test that response object is fully functional
            expect($response)->toBeInstanceOf(Ninja\Verisoul\Responses\AccountResponse::class);
        });

        it('handles streaming of very large responses efficiently', function (): void {
            $streamingClient = new AccountClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'get' => MockFactory::createAccountResponseFromFixture([
                        'account' => [
                            'id' => 'streaming_test',
                            'large_dataset' => array_fill(0, 5000, [
                                'record_id' => uniqid(),
                                'data' => str_repeat('streaming_data', 500),
                                'metadata' => array_fill(0, 50, 'streaming_metadata'),
                            ]),
                        ],
                    ]),
                ]),
            );

            $memoryReadings = [];
            $timeReadings = [];

            // Simulate processing large response in chunks
            $startOverall = microtime(true);

            for ($chunk = 1; $chunk <= 5; $chunk++) {
                $chunkStart = microtime(true);
                $chunkMemoryStart = memory_get_usage(true);

                $response = $streamingClient->getAccount('streaming_test');

                $chunkEnd = microtime(true);
                $chunkMemoryEnd = memory_get_usage(true);

                $timeReadings[] = ($chunkEnd - $chunkStart) * 1000;
                $memoryReadings[] = $chunkMemoryEnd - $chunkMemoryStart;

                // Simulate processing and cleanup
                unset($response);
                gc_collect_cycles();
            }

            $endOverall = microtime(true);
            $totalTime = ($endOverall - $startOverall) * 1000;

            $avgChunkTime = array_sum($timeReadings) / count($timeReadings);
            $avgChunkMemory = array_sum($memoryReadings) / count($memoryReadings);

            expect(count($timeReadings))->toBe(5)
                ->and($totalTime)->toBeLessThan(10000) // Total under 10 seconds
                ->and($avgChunkTime)->toBeLessThan(3000) // Average chunk under 3 seconds
                ->and($avgChunkMemory)->toBeLessThan(30 * 1024 * 1024); // Average chunk memory under 30MB
        });
    });

    describe('Payload compression and optimization', function (): void {
        it('handles compressed payloads efficiently', function (): void {
            $compressionTestData = [
                'repetitive_data' => str_repeat('compress_me', 10000), // Highly compressible
                'random_data' => str_repeat(chr(random_int(0, 255)), 50000), // Less compressible
                'structured_data' => array_fill(0, 1000, [
                    'id' => uniqid(),
                    'repeated_field' => 'repeated_value',
                    'unique_field' => random_int(1, 1000000),
                ]),
            ];

            $compressionClient = new SessionClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'post' => MockFactory::createAuthenticateSessionResponseFromFixture(['session_id' => 'compression_test']),
                ]),
            );

            $userAccount = UserAccount::from([
                'id' => 'compression_user',
                'email' => 'compression@example.com',
                'metadata' => $compressionTestData,
            ]);

            $startTime = microtime(true);
            $startMemory = memory_get_usage(true);

            $response = $compressionClient->authenticate($userAccount, 'compression_session');

            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);

            $processingTime = ($endTime - $startTime) * 1000;
            $memoryUsed = $endMemory - $startMemory;

            // Test that compression-friendly data is handled efficiently
            expect($response)->toBeInstanceOf(Ninja\Verisoul\Responses\AuthenticateSessionResponse::class)
                ->and($processingTime)->toBeLessThan(1500) // Should process within 1.5 seconds
                ->and($memoryUsed)->toBeLessThan(12 * 1024 * 1024); // Should use less than 12MB
        });

        it('optimizes payload serialization for large objects', function (): void {
            $serializationClient = new SessionClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'post' => MockFactory::createAuthenticateSessionResponseFromFixture(['session_id' => 'serialization_test']),
                ]),
            );

            // Create object with various data types that test serialization efficiency
            $complexSerializationData = [
                'integers' => range(1, 10000),
                'floats' => array_map(fn($i) => $i / 3.14159, range(1, 5000)),
                'strings' => array_map(fn($i) => "string_value_{$i}", range(1, 3000)),
                'booleans' => array_map(fn($i) => 0 === $i % 2, range(1, 2000)),
                'nulls' => array_fill(0, 1000, null),
                'mixed_arrays' => array_fill(0, 500, [
                    'int' => random_int(1, 1000),
                    'string' => uniqid(),
                    'bool' => (bool) random_int(0, 1),
                    'null' => null,
                    'nested' => ['deep' => ['deeper' => 'value']],
                ]),
            ];

            $userAccount = UserAccount::from([
                'id' => 'serialization_user',
                'email' => 'serialization@example.com',
                'metadata' => $complexSerializationData,
            ]);

            $serializationTimes = [];
            $memoryUsages = [];

            // Test serialization performance across multiple iterations
            for ($i = 1; $i <= 5; $i++) {
                $startTime = microtime(true);
                $startMemory = memory_get_usage(true);

                $response = $serializationClient->authenticate($userAccount, "serialization_session_{$i}");

                $endTime = microtime(true);
                $endMemory = memory_get_usage(true);

                $serializationTimes[] = ($endTime - $startTime) * 1000;
                $memoryUsages[] = $endMemory - $startMemory;

                unset($response);
            }

            $avgSerializationTime = array_sum($serializationTimes) / count($serializationTimes);
            $avgMemoryUsage = array_sum($memoryUsages) / count($memoryUsages);

            expect(count($serializationTimes))->toBe(5)
                ->and($avgSerializationTime)->toBeLessThan(800) // Average under 800ms
                ->and($avgMemoryUsage)->toBeLessThan(15 * 1024 * 1024); // Average memory under 15MB

            // Check consistency of serialization performance
            $maxTime = max($serializationTimes);
            $minTime = min($serializationTimes);
            $timeVariation = ($maxTime - $minTime) / $avgSerializationTime;

            expect($timeVariation)->toBeLessThan(0.3); // Less than 30% variation in serialization time
        });
    });

    describe('Edge cases with extreme payloads', function (): void {
        it('handles maximum payload sizes gracefully', function (): void {
            $maxPayloadClient = new AccountClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'put' => MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'max_payload_test']]),
                ]),
            );

            // Create maximum reasonable payload (~5MB)
            $maxPayloadData = [
                'section1' => array_fill(0, 10000, str_repeat('x', 100)),
                'section2' => array_fill(0, 5000, [
                    'field1' => str_repeat('y', 200),
                    'field2' => array_fill(0, 20, 'nested_data'),
                ]),
                'section3' => str_repeat('z', 1000000), // 1MB string
            ];

            $startTime = microtime(true);
            $startMemory = memory_get_usage(true);

            try {
                $response = $maxPayloadClient->updateAccount('max_payload_test', $maxPayloadData);

                $endTime = microtime(true);
                $endMemory = memory_get_usage(true);

                $processingTime = ($endTime - $startTime) * 1000;
                $memoryUsed = $endMemory - $startMemory;

                expect($response)->toBeInstanceOf(Ninja\Verisoul\Responses\AccountResponse::class)
                    ->and($processingTime)->toBeLessThan(5000) // Should process within 5 seconds
                    ->and($memoryUsed)->toBeLessThan(50 * 1024 * 1024); // Should use less than 50MB

            } catch (Exception $e) {
                // If payload is too large, ensure graceful handling
                expect($e)->toBeInstanceOf(Ninja\Verisoul\Exceptions\VerisoulApiException::class);
            }
        });

        it('handles deeply nested structures efficiently', function (): void {
            $deepNestingClient = new SessionClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'post' => MockFactory::createAuthenticateSessionResponseFromFixture(['session_id' => 'deep_nesting_test']),
                ]),
            );

            // Create deeply nested structure (50 levels deep)
            $deepStructure = 'deepest_value';
            for ($level = 1; $level <= 50; $level++) {
                $deepStructure = [
                    "level_{$level}" => $deepStructure,
                    "level_{$level}_data" => array_fill(0, 10, "data_at_level_{$level}"),
                ];
            }

            $userAccount = UserAccount::from([
                'id' => 'deep_nesting_user',
                'email' => 'deep.nesting@example.com',
                'metadata' => [
                    'deep_structure' => $deepStructure,
                    'flat_data' => array_fill(0, 1000, 'flat_value'), // Mix with flat data
                ],
            ]);

            $startTime = microtime(true);
            $startMemory = memory_get_usage(true);

            $response = $deepNestingClient->authenticate($userAccount, 'deep_nesting_session');

            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);

            $processingTime = ($endTime - $startTime) * 1000;
            $memoryUsed = $endMemory - $startMemory;

            expect($response)->toBeInstanceOf(Ninja\Verisoul\Responses\AuthenticateSessionResponse::class)
                ->and($processingTime)->toBeLessThan(2000) // Should handle deep nesting within 2 seconds
                ->and($memoryUsed)->toBeLessThan(25 * 1024 * 1024); // Should use less than 25MB
        });

        it('handles payloads with special characters and encoding', function (): void {
            $encodingClient = new SessionClient(
                $this->testApiKey,
                $this->sandboxEnv,
                httpClient: MockFactory::createSuccessfulHttpClient([
                    'post' => MockFactory::createAuthenticateSessionResponseFromFixture(['session_id' => 'encoding_test']),
                ]),
            );

            // Create payload with various encodings and special characters
            $specialCharData = [
                'unicode_strings' => [
                    'emoji' => str_repeat('🚀🎉🔥💯', 1000),
                    'chinese' => str_repeat('你好世界', 2000),
                    'arabic' => str_repeat('مرحبا بالعالم', 1500),
                    'japanese' => str_repeat('こんにちは世界', 1800),
                    'russian' => str_repeat('Привет мир', 2200),
                ],
                'special_chars' => [
                    'quotes' => str_repeat('"\'`', 3000),
                    'slashes' => str_repeat('\\//', 2500),
                    'control_chars' => str_repeat("\n\r\t", 2000),
                    'high_ascii' => str_repeat(chr(200) . chr(220) . chr(240), 1000),
                ],
                'binary_data' => base64_encode(random_bytes(50000)),
                'json_strings' => array_fill(0, 500, json_encode([
                    'nested' => ['special' => '{"key": "value with \\"quotes\\""}'],
                ])),
            ];

            $userAccount = UserAccount::from([
                'id' => 'encoding_user',
                'email' => 'encoding@example.com',
                'metadata' => $specialCharData,
            ]);

            $startTime = microtime(true);
            $startMemory = memory_get_usage(true);

            $response = $encodingClient->authenticate($userAccount, 'encoding_session');

            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);

            $processingTime = ($endTime - $startTime) * 1000;
            $memoryUsed = $endMemory - $startMemory;

            expect($response)->toBeInstanceOf(Ninja\Verisoul\Responses\AuthenticateSessionResponse::class)
                ->and($processingTime)->toBeLessThan(3000) // Should handle encoding within 3 seconds
                ->and($memoryUsed)->toBeLessThan(30 * 1024 * 1024); // Should use less than 30MB
        });
    });
});
