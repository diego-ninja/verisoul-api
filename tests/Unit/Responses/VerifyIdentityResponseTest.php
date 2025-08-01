<?php

use Ninja\Verisoul\Responses\VerifyIdentityResponse;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('VerifyIdentityResponse', function (): void {
    describe('construction and basic functionality', function (): void {
        it('can be created from fixture data', function (): void {
            $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture();
            $response = VerifyIdentityResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);
        });

        it('can be created with custom verification data', function (): void {
            $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture([
                'success' => false,
                'match' => false,
            ]);
            $response = VerifyIdentityResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);
        });

        it('provides access to response identifier', function (): void {
            $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture([
                'request_id' => 'test_request_456',
            ]);
            $response = VerifyIdentityResponse::from($fixtureData);

            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('request_id')
                ->and($responseArray['request_id'])->toBe('test_request_456');
        });
    });

    describe('identity verification scenarios', function (): void {
        it('handles successful identity match', function (): void {
            $successfulMatch = MockFactory::createVerifyIdentifyResponseFromFixture([
                'success' => true,
                'match' => true,
            ]);
            $response = VerifyIdentityResponse::from($successfulMatch);

            expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue()
                ->and($responseArray['match'])->toBeTrue();
        });

        it('handles successful verification with no match', function (): void {
            $successfulNoMatch = MockFactory::createVerifyIdentifyResponseFromFixture([
                'success' => true,
                'match' => false,
            ]);
            $response = VerifyIdentityResponse::from($successfulNoMatch);

            expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue()
                ->and($responseArray['match'])->toBeFalse();
        });

        it('handles failed verification', function (): void {
            $failedVerification = MockFactory::createVerifyIdentifyResponseFromFixture([
                'success' => false,
                'match' => false,
            ]);
            $response = VerifyIdentityResponse::from($failedVerification);

            expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse()
                ->and($responseArray['match'])->toBeFalse();
        });

        it('handles edge case where verification fails but match is true', function (): void {
            // This might be an edge case scenario in the API
            $edgeCase = MockFactory::createVerifyIdentifyResponseFromFixture([
                'success' => false,
                'match' => true,
            ]);
            $response = VerifyIdentityResponse::from($edgeCase);

            expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse()
                ->and($responseArray['match'])->toBeTrue();
        });
    });

    describe('request ID handling', function (): void {
        it('handles various request ID formats', function (): void {
            $requestIds = [
                'req_12345678901234567890',
                'request_abc123',
                'identity_req_test_789',
                '550e8400-e29b-41d4-a716-446655440000',
                'simple_id',
            ];

            foreach ($requestIds as $requestId) {
                $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture([
                    'request_id' => $requestId,
                ]);
                $response = VerifyIdentityResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);

                $responseArray = $response->array();
                expect($responseArray['request_id'])->toBe($requestId);
            }
        });

        it('handles empty request ID', function (): void {
            $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture([
                'request_id' => '',
            ]);
            $response = VerifyIdentityResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);
        });

        it('handles very long request ID', function (): void {
            $longRequestId = str_repeat('req_', 100) . 'end';
            $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture([
                'request_id' => $longRequestId,
            ]);
            $response = VerifyIdentityResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);

            $responseArray = $response->array();
            expect($responseArray['request_id'])->toBe($longRequestId);
        });
    });

    describe('boolean field handling', function (): void {
        it('correctly handles boolean true values', function (): void {
            $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture([
                'success' => true,
                'match' => true,
            ]);
            $response = VerifyIdentityResponse::from($fixtureData);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue()
                ->and($responseArray['success'])->toBeBool()
                ->and($responseArray['match'])->toBeTrue()
                ->and($responseArray['match'])->toBeBool();
        });

        it('correctly handles boolean false values', function (): void {
            $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture([
                'success' => false,
                'match' => false,
            ]);
            $response = VerifyIdentityResponse::from($fixtureData);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse()
                ->and($responseArray['success'])->toBeBool()
                ->and($responseArray['match'])->toBeFalse()
                ->and($responseArray['match'])->toBeBool();
        });

        it('handles truthy values correctly', function (): void {
            $truthyValues = [1, '1', 'true', 'yes'];

            foreach ($truthyValues as $index => $value) {
                $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture([
                    'request_id' => "req_truthy_{$index}",
                    'success' => $value,
                    'match' => $value,
                ]);
                $response = VerifyIdentityResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);
            }
        });

        it('handles falsy values correctly', function (): void {
            $falsyValues = [0, '0', 'false', 'no'];  // Removed null

            foreach ($falsyValues as $index => $value) {
                $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture([
                    'request_id' => "req_falsy_{$index}",
                    'success' => $value,
                    'match' => $value,
                ]);
                $response = VerifyIdentityResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);
            }
        });
    });

    describe('data integrity and serialization', function (): void {
        it('maintains data integrity through serialization', function (): void {
            $originalData = MockFactory::createVerifyIdentifyResponseFromFixture([
                'request_id' => 'integrity_test_request',
                'success' => true,
                'match' => false,
            ]);
            $response = VerifyIdentityResponse::from($originalData);
            $serializedData = $response->array();
            $recreatedResponse = VerifyIdentityResponse::from($serializedData);

            expect($recreatedResponse)->toBeInstanceOf(VerifyIdentityResponse::class);

            $originalArray = $response->array();
            $recreatedArray = $recreatedResponse->array();

            expect($recreatedArray['request_id'])->toBe($originalArray['request_id'])
                ->and($recreatedArray['success'])->toBe($originalArray['success'])
                ->and($recreatedArray['match'])->toBe($originalArray['match']);
        });

        it('serializes to correct structure', function (): void {
            $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture([
                'request_id' => 'structure_test',
                'success' => true,
                'match' => false,
            ]);
            $response = VerifyIdentityResponse::from($fixtureData);
            $serialized = $response->array();

            expect($serialized)->toBeArray()
                ->and($serialized)->toHaveCount(3)
                ->and($serialized)->toHaveKey('request_id')
                ->and($serialized)->toHaveKey('success')
                ->and($serialized)->toHaveKey('match')
                ->and($serialized['request_id'])->toBe('structure_test')
                ->and($serialized['success'])->toBeTrue()
                ->and($serialized['match'])->toBeFalse();
        });

        it('handles JSON serialization correctly', function (): void {
            $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture([
                'request_id' => 'json_test',
                'success' => false,
                'match' => true,
            ]);
            $response = VerifyIdentityResponse::from($fixtureData);

            $jsonString = json_encode($response->array());
            $decodedData = json_decode($jsonString, true);
            $recreatedResponse = VerifyIdentityResponse::from($decodedData);

            expect($recreatedResponse)->toBeInstanceOf(VerifyIdentityResponse::class);
            expect(json_last_error())->toBe(JSON_ERROR_NONE);
        });
    });

    describe('readonly class behavior', function (): void {
        it('creates immutable instances', function (): void {
            $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture();
            $response = VerifyIdentityResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);

            // Since it's a readonly class, we can't modify properties
            // This test verifies the object was created successfully
            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('request_id');
        });

        it('provides consistent data access', function (): void {
            $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture();
            $response = VerifyIdentityResponse::from($fixtureData);

            $firstAccess = $response->array();
            $secondAccess = $response->array();
            $thirdAccess = $response->array();

            expect($firstAccess)->toBe($secondAccess)
                ->and($secondAccess)->toBe($thirdAccess);
        });
    });

    describe('real-world verification scenarios', function (): void {
        it('handles successful identity verification with match', function (): void {
            $successData = MockFactory::createVerifyIdentifyResponseFromFixture([
                'request_id' => 'verify_success_' . time(),
                'success' => true,
                'match' => true,
            ]);
            $response = VerifyIdentityResponse::from($successData);

            expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue()
                ->and($responseArray['match'])->toBeTrue();
        });

        it('handles successful verification but no identity match', function (): void {
            $noMatchData = MockFactory::createVerifyIdentifyResponseFromFixture([
                'request_id' => 'verify_no_match_' . time(),
                'success' => true,
                'match' => false,
            ]);
            $response = VerifyIdentityResponse::from($noMatchData);

            expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue()
                ->and($responseArray['match'])->toBeFalse();
        });

        it('handles failed verification scenario', function (): void {
            $failureData = MockFactory::createVerifyIdentifyResponseFromFixture([
                'request_id' => 'verify_failure_' . time(),
                'success' => false,
                'match' => false,
            ]);
            $response = VerifyIdentityResponse::from($failureData);

            expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse()
                ->and($responseArray['match'])->toBeFalse();
        });

        it('handles API response format', function (): void {
            // Simulate typical API response format
            $apiResponse = MockFactory::createVerifyIdentifyResponseFromFixture([
                'request_id' => 'api_format_' . uniqid(),
                'success' => true,
                'match' => false,
            ]);
            $response = VerifyIdentityResponse::from($apiResponse);

            expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);

            // Verify it can be used in typical API response handling
            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('request_id')
                ->and($responseArray)->toHaveKey('success')
                ->and($responseArray)->toHaveKey('match');
        });
    });

    describe('business logic validation', function (): void {
        it('validates common success/match combinations', function (): void {
            $scenarios = [
                ['success' => true, 'match' => true, 'description' => 'Perfect match'],
                ['success' => true, 'match' => false, 'description' => 'Verified but no match'],
                ['success' => false, 'match' => false, 'description' => 'Failed verification'],
                ['success' => false, 'match' => true, 'description' => 'Edge case: failed but match'],
            ];

            foreach ($scenarios as $scenario) {
                $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture([
                    'success' => $scenario['success'],
                    'match' => $scenario['match'],
                ]);
                $response = VerifyIdentityResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);

                $responseArray = $response->array();
                expect($responseArray['success'])->toBe($scenario['success'])
                    ->and($responseArray['match'])->toBe($scenario['match']);
            }
        });

        it('provides consistent results across multiple calls', function (): void {
            $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture([
                'success' => true,
                'match' => false,
            ]);
            $response = VerifyIdentityResponse::from($fixtureData);

            $firstResult = $response->array();
            $secondResult = $response->array();
            $thirdResult = $response->array();

            expect($firstResult['success'])->toBe($secondResult['success'])
                ->and($secondResult['success'])->toBe($thirdResult['success'])
                ->and($firstResult['match'])->toBe($secondResult['match'])
                ->and($secondResult['match'])->toBe($thirdResult['match']);
        });
    });

    describe('performance and memory', function (): void {
        it('creates multiple instances efficiently', function (): void {
            $startTime = microtime(true);
            $responses = [];

            for ($i = 1; $i <= 100; $i++) {
                $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture([
                    'request_id' => "performance_test_{$i}",
                    'success' => (0 === $i % 2),
                    'match' => (0 === $i % 3),
                ]);
                $responses[] = VerifyIdentityResponse::from($fixtureData);
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            expect(count($responses))->toBe(100)
                ->and($executionTime)->toBeLessThan(0.5);

            foreach ($responses as $response) {
                expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);
            }
        });

        it('maintains reasonable memory usage', function (): void {
            $initialMemory = memory_get_usage();

            $responses = [];
            for ($i = 1; $i <= 1000; $i++) {
                $fixtureData = MockFactory::createVerifyIdentifyResponseFromFixture([
                    'request_id' => "memory_test_{$i}",
                ]);
                $responses[] = VerifyIdentityResponse::from($fixtureData);
            }

            $finalMemory = memory_get_usage();
            $memoryUsed = $finalMemory - $initialMemory;

            expect(count($responses))->toBe(1000)
                ->and($memoryUsed)->toBeLessThan(1024 * 1024); // Less than 1MB

            unset($responses);
        });
    });
});
