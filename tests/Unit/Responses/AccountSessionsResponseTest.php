<?php

use Ninja\Verisoul\Responses\AccountSessionsResponse;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('AccountSessionsResponse', function (): void {
    describe('construction and basic functionality', function (): void {
        it('can be created from fixture data', function (): void {
            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture();
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);
        });

        it('can be created with custom sessions data', function (): void {
            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'sessions' => ['session_1', 'session_2', 'session_3'],
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);
        });

        it('provides access to response identifier', function (): void {
            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'request_id' => 'test_request_456',
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('request_id')
                ->and($responseArray['request_id'])->toBe('test_request_456');
        });
    });

    describe('sessions array handling', function (): void {
        it('handles empty sessions array', function (): void {
            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'sessions' => [],
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);

            $responseArray = $response->array();
            expect($responseArray['sessions'])->toBeArray()
                ->and($responseArray['sessions'])->toBeEmpty();
        });

        it('handles single session', function (): void {
            $sessionId = 'single_session_123';
            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'sessions' => [$sessionId],
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);

            $responseArray = $response->array();
            expect($responseArray['sessions'])->toHaveCount(1)
                ->and($responseArray['sessions'])->toContain($sessionId);
        });

        it('handles multiple sessions', function (): void {
            $sessionIds = [
                'session_1_abc123',
                'session_2_def456',
                'session_3_ghi789',
                'session_4_jkl012',
                'session_5_mno345',
            ];
            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'sessions' => $sessionIds,
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);

            $responseArray = $response->array();
            expect($responseArray['sessions'])->toHaveCount(5);

            foreach ($sessionIds as $sessionId) {
                expect($responseArray['sessions'])->toContain($sessionId);
            }
        });

        it('handles large number of sessions', function (): void {
            $sessionIds = [];
            for ($i = 1; $i <= 100; $i++) {
                $sessionIds[] = "session_{$i}_" . bin2hex(random_bytes(8));
            }

            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'sessions' => $sessionIds,
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);

            $responseArray = $response->array();
            expect($responseArray['sessions'])->toHaveCount(100);
        });
    });

    describe('session ID format handling', function (): void {
        it('handles various session ID formats', function (): void {
            $sessionIdFormats = [
                'sess_12345678901234567890',
                'session_abc123',
                '550e8400-e29b-41d4-a716-446655440000',
                'simple_session',
                'session-with-dashes',
                'SessionWithCamelCase',
                'session_with_underscores_123',
                'UPPERCASE_SESSION',
                'mixedCASE_Session_456',
            ];

            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'sessions' => $sessionIdFormats,
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);

            $responseArray = $response->array();
            expect($responseArray['sessions'])->toHaveCount(count($sessionIdFormats));

            foreach ($sessionIdFormats as $sessionId) {
                expect($responseArray['sessions'])->toContain($sessionId);
            }
        });

        it('handles UUID-formatted session IDs', function (): void {
            $uuidSessions = [
                '550e8400-e29b-41d4-a716-446655440000',
                'f47ac10b-58cc-4372-a567-0e02b2c3d479',
                '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
                '123e4567-e89b-12d3-a456-426614174000',
            ];

            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'sessions' => $uuidSessions,
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);

            $responseArray = $response->array();
            foreach ($uuidSessions as $sessionId) {
                expect($responseArray['sessions'])->toContain($sessionId);
            }
        });

        it('handles very long session IDs', function (): void {
            $longSessionIds = [
                str_repeat('session_', 20) . 'end',
                'very_long_session_identifier_' . str_repeat('a', 100),
                'session_with_timestamp_' . time() . '_and_random_' . bin2hex(random_bytes(50)),
            ];

            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'sessions' => $longSessionIds,
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);

            $responseArray = $response->array();
            foreach ($longSessionIds as $sessionId) {
                expect($responseArray['sessions'])->toContain($sessionId);
            }
        });

        it('handles sessions with special characters', function (): void {
            $specialCharSessions = [
                'session@domain.com',
                'session+tag',
                'session.with.dots',
                'session#with#hashes',
                'session%20encoded',
                'session&with&ampersands',
            ];

            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'sessions' => $specialCharSessions,
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);
        });
    });

    describe('request ID handling', function (): void {
        it('handles various request ID formats', function (): void {
            $requestIds = [
                'req_12345678901234567890',
                'request_abc123',
                'sessions_req_test_789',
                '550e8400-e29b-41d4-a716-446655440000',
                'simple_request_id',
            ];

            foreach ($requestIds as $requestId) {
                $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                    'request_id' => $requestId,
                ]);
                $response = AccountSessionsResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(AccountSessionsResponse::class);

                $responseArray = $response->array();
                expect($responseArray['request_id'])->toBe($requestId);
            }
        });

        it('handles empty request ID', function (): void {
            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'request_id' => '',
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);
        });

        it('handles very long request ID', function (): void {
            $longRequestId = str_repeat('req_', 100) . 'end';
            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'request_id' => $longRequestId,
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);

            $responseArray = $response->array();
            expect($responseArray['request_id'])->toBe($longRequestId);
        });
    });

    describe('data integrity and serialization', function (): void {
        it('maintains data integrity through serialization', function (): void {
            $originalData = MockFactory::createAccountSessionsResponseFromFixture([
                'request_id' => 'integrity_test_request',
                'sessions' => ['session_1', 'session_2', 'session_3'],
            ]);
            $response = AccountSessionsResponse::from($originalData);
            $serializedData = $response->array();
            $recreatedResponse = AccountSessionsResponse::from($serializedData);

            expect($recreatedResponse)->toBeInstanceOf(AccountSessionsResponse::class);

            $originalArray = $response->array();
            $recreatedArray = $recreatedResponse->array();

            expect($recreatedArray['request_id'])->toBe($originalArray['request_id'])
                ->and($recreatedArray['sessions'])->toBe($originalArray['sessions']);
        });

        it('handles session order preservation', function (): void {
            $orderedSessions = ['first_session', 'second_session', 'third_session', 'fourth_session'];
            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'sessions' => $orderedSessions,
            ]);
            $response = AccountSessionsResponse::from($fixtureData);
            $serializedData = $response->array();
            $recreatedResponse = AccountSessionsResponse::from($serializedData);

            $recreatedArray = $recreatedResponse->array();
            expect($recreatedArray['sessions'])->toBe($orderedSessions);
        });

        it('serializes to correct structure', function (): void {
            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'request_id' => 'structure_test',
                'sessions' => ['test_session_1', 'test_session_2'],
            ]);
            $response = AccountSessionsResponse::from($fixtureData);
            $serialized = $response->array();

            expect($serialized)->toBeArray()
                ->and($serialized)->toHaveCount(2)
                ->and($serialized)->toHaveKey('request_id')
                ->and($serialized)->toHaveKey('sessions')
                ->and($serialized['request_id'])->toBe('structure_test')
                ->and($serialized['sessions'])->toBeArray()
                ->and($serialized['sessions'])->toHaveCount(2);
        });

        it('handles JSON serialization correctly', function (): void {
            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'request_id' => 'json_test',
                'sessions' => ['json_session_1', 'json_session_2'],
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            $jsonString = json_encode($response->array());
            $decodedData = json_decode($jsonString, true);
            $recreatedResponse = AccountSessionsResponse::from($decodedData);

            expect($recreatedResponse)->toBeInstanceOf(AccountSessionsResponse::class);
            expect(json_last_error())->toBe(JSON_ERROR_NONE);
        });
    });

    describe('readonly class behavior', function (): void {
        it('creates immutable instances', function (): void {
            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture();
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);

            // Since it's a readonly class, we can't modify properties
            // This test verifies the object was created successfully
            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('request_id')
                ->and($responseArray)->toHaveKey('sessions');
        });

        it('provides consistent data access', function (): void {
            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture();
            $response = AccountSessionsResponse::from($fixtureData);

            $firstAccess = $response->array();
            $secondAccess = $response->array();
            $thirdAccess = $response->array();

            expect($firstAccess)->toBe($secondAccess)
                ->and($secondAccess)->toBe($thirdAccess);
        });
    });

    describe('real-world account sessions scenarios', function (): void {
        it('handles new account with no sessions', function (): void {
            $newAccountData = MockFactory::createAccountSessionsResponseFromFixture([
                'request_id' => 'new_account_sessions_' . time(),
                'sessions' => [],
            ]);
            $response = AccountSessionsResponse::from($newAccountData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);

            $responseArray = $response->array();
            expect($responseArray['sessions'])->toBeEmpty();
        });

        it('handles active account with recent sessions', function (): void {
            $recentSessions = [
                'sess_recent_1_' . time(),
                'sess_recent_2_' . (time() - 3600),
                'sess_recent_3_' . (time() - 7200),
            ];
            $activeAccountData = MockFactory::createAccountSessionsResponseFromFixture([
                'request_id' => 'active_account_sessions_' . time(),
                'sessions' => $recentSessions,
            ]);
            $response = AccountSessionsResponse::from($activeAccountData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);

            $responseArray = $response->array();
            expect($responseArray['sessions'])->toHaveCount(3);
        });

        it('handles power user with many sessions', function (): void {
            $manySessions = [];
            for ($i = 1; $i <= 50; $i++) {
                $manySessions[] = "power_user_session_{$i}_" . bin2hex(random_bytes(8));
            }

            $powerUserData = MockFactory::createAccountSessionsResponseFromFixture([
                'request_id' => 'power_user_sessions_' . time(),
                'sessions' => $manySessions,
            ]);
            $response = AccountSessionsResponse::from($powerUserData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);

            $responseArray = $response->array();
            expect($responseArray['sessions'])->toHaveCount(50);
        });

        it('handles historical sessions query', function (): void {
            $historicalSessions = [
                'sess_2023_01_' . bin2hex(random_bytes(8)),
                'sess_2023_02_' . bin2hex(random_bytes(8)),
                'sess_2023_03_' . bin2hex(random_bytes(8)),
                'sess_2024_01_' . bin2hex(random_bytes(8)),
                'sess_2024_02_' . bin2hex(random_bytes(8)),
            ];

            $historicalData = MockFactory::createAccountSessionsResponseFromFixture([
                'request_id' => 'historical_sessions_' . time(),
                'sessions' => $historicalSessions,
            ]);
            $response = AccountSessionsResponse::from($historicalData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);
        });

        it('handles API response format', function (): void {
            // Simulate typical API response format
            $apiResponse = MockFactory::createAccountSessionsResponseFromFixture([
                'request_id' => 'api_format_' . uniqid(),
                'sessions' => [
                    'api_session_1_' . uniqid(),
                    'api_session_2_' . uniqid(),
                ],
            ]);
            $response = AccountSessionsResponse::from($apiResponse);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);

            // Verify it can be used in typical API response handling
            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('request_id')
                ->and($responseArray)->toHaveKey('sessions');
        });
    });

    describe('edge cases and error handling', function (): void {
        it('handles duplicate session IDs', function (): void {
            $duplicateSessions = [
                'duplicate_session_123',
                'duplicate_session_123',
                'unique_session_456',
                'duplicate_session_123',
            ];

            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'sessions' => $duplicateSessions,
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);

            $responseArray = $response->array();
            expect($responseArray['sessions'])->toHaveCount(4); // Preserves duplicates
        });

        it('handles empty string session IDs', function (): void {
            $sessionsWithEmpty = ['valid_session', '', 'another_valid_session'];

            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'sessions' => $sessionsWithEmpty,
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);
        });

        it('handles null values in sessions array', function (): void {
            $sessionsWithNull = ['valid_session', null, 'another_valid_session'];

            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'sessions' => $sessionsWithNull,
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);
        });

        it('handles extremely large session arrays', function (): void {
            $largeSessions = [];
            for ($i = 1; $i <= 10000; $i++) {
                $largeSessions[] = "massive_session_{$i}";
            }

            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'sessions' => $largeSessions,
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);

            $responseArray = $response->array();
            expect($responseArray['sessions'])->toHaveCount(10000);
        });

        it('handles sessions with whitespace', function (): void {
            $whitespaceIds = [
                ' leading_space_session',
                'trailing_space_session ',
                ' both_spaces_session ',
                'internal space session',
                "tab\tsession",
                "newline\nsession",
            ];

            $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                'sessions' => $whitespaceIds,
            ]);
            $response = AccountSessionsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);
        });
    });

    describe('performance and memory', function (): void {
        it('creates multiple instances efficiently', function (): void {
            $startTime = microtime(true);
            $responses = [];

            for ($i = 1; $i <= 100; $i++) {
                $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                    'request_id' => "performance_test_{$i}",
                    'sessions' => ["session_{$i}_1", "session_{$i}_2"],
                ]);
                $responses[] = AccountSessionsResponse::from($fixtureData);
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            expect(count($responses))->toBe(100)
                ->and($executionTime)->toBeLessThan(0.5);

            foreach ($responses as $response) {
                expect($response)->toBeInstanceOf(AccountSessionsResponse::class);
            }
        });

        it('maintains reasonable memory usage with large session arrays', function (): void {
            $initialMemory = memory_get_usage();

            $responses = [];
            for ($i = 1; $i <= 100; $i++) {
                $sessions = [];
                for ($j = 1; $j <= 20; $j++) {
                    $sessions[] = "memory_test_{$i}_{$j}";
                }

                $fixtureData = MockFactory::createAccountSessionsResponseFromFixture([
                    'request_id' => "memory_test_{$i}",
                    'sessions' => $sessions,
                ]);
                $responses[] = AccountSessionsResponse::from($fixtureData);
            }

            $finalMemory = memory_get_usage();
            $memoryUsed = $finalMemory - $initialMemory;

            expect(count($responses))->toBe(100)
                ->and($memoryUsed)->toBeLessThan(2 * 1024 * 1024); // Less than 2MB

            unset($responses);
        });
    });
});
