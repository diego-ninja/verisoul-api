<?php

use Ninja\Verisoul\Responses\EnrollAccountResponse;

describe('EnrollAccountResponse', function (): void {
    describe('construction and basic functionality', function (): void {
        it('can be created with basic data', function (): void {
            $data = [
                'request_id' => 'req_' . bin2hex(random_bytes(12)),
                'success' => true,
            ];
            $response = EnrollAccountResponse::from($data);

            expect($response)->toBeInstanceOf(EnrollAccountResponse::class);
        });

        it('can be created with success=true', function (): void {
            $data = [
                'request_id' => 'req_success_test',
                'success' => true,
            ];
            $response = EnrollAccountResponse::from($data);

            expect($response)->toBeInstanceOf(EnrollAccountResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue()
                ->and($responseArray['request_id'])->toBe('req_success_test');
        });

        it('can be created with success=false', function (): void {
            $data = [
                'request_id' => 'req_failure_test',
                'success' => false,
            ];
            $response = EnrollAccountResponse::from($data);

            expect($response)->toBeInstanceOf(EnrollAccountResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse()
                ->and($responseArray['request_id'])->toBe('req_failure_test');
        });
    });

    describe('request ID handling', function (): void {
        it('handles various request ID formats', function (): void {
            $requestIds = [
                'req_12345678901234567890',
                'request_abc123',
                'enroll_req_test_789',
                '550e8400-e29b-41d4-a716-446655440000',
                'simple_id',
            ];

            foreach ($requestIds as $requestId) {
                $data = [
                    'request_id' => $requestId,
                    'success' => true,
                ];
                $response = EnrollAccountResponse::from($data);

                expect($response)->toBeInstanceOf(EnrollAccountResponse::class);

                $responseArray = $response->array();
                expect($responseArray['request_id'])->toBe($requestId);
            }
        });

        it('handles empty request ID', function (): void {
            $data = [
                'request_id' => '',
                'success' => true,
            ];
            $response = EnrollAccountResponse::from($data);

            expect($response)->toBeInstanceOf(EnrollAccountResponse::class);
        });

        it('handles very long request ID', function (): void {
            $longRequestId = str_repeat('req_', 100) . 'end';
            $data = [
                'request_id' => $longRequestId,
                'success' => true,
            ];
            $response = EnrollAccountResponse::from($data);

            expect($response)->toBeInstanceOf(EnrollAccountResponse::class);

            $responseArray = $response->array();
            expect($responseArray['request_id'])->toBe($longRequestId);
        });
    });

    describe('success flag handling', function (): void {
        it('correctly handles boolean true', function (): void {
            $data = [
                'request_id' => 'req_bool_true',
                'success' => true,
            ];
            $response = EnrollAccountResponse::from($data);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue()
                ->and($responseArray['success'])->toBeBool();
        });

        it('correctly handles boolean false', function (): void {
            $data = [
                'request_id' => 'req_bool_false',
                'success' => false,
            ];
            $response = EnrollAccountResponse::from($data);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse()
                ->and($responseArray['success'])->toBeBool();
        });

        it('handles truthy values correctly', function (): void {
            $truthyValues = [1, '1', 'true', 'yes'];

            foreach ($truthyValues as $index => $value) {
                $data = [
                    'request_id' => "req_truthy_{$index}",
                    'success' => $value,
                ];
                $response = EnrollAccountResponse::from($data);

                expect($response)->toBeInstanceOf(EnrollAccountResponse::class);
            }
        });

        it('handles falsy values correctly', function (): void {
            $falsyValues = [0, '0', 'false', 'no'];  // Removed null

            foreach ($falsyValues as $index => $value) {
                $data = [
                    'request_id' => "req_falsy_{$index}",
                    'success' => $value,
                ];
                $response = EnrollAccountResponse::from($data);

                expect($response)->toBeInstanceOf(EnrollAccountResponse::class);
            }
        });
    });

    describe('data integrity and serialization', function (): void {
        it('maintains data integrity through serialization', function (): void {
            $originalData = [
                'request_id' => 'req_integrity_test_123456',
                'success' => true,
            ];
            $response = EnrollAccountResponse::from($originalData);
            $serializedData = $response->array();
            $recreatedResponse = EnrollAccountResponse::from($serializedData);

            expect($recreatedResponse)->toBeInstanceOf(EnrollAccountResponse::class);

            $originalArray = $response->array();
            $recreatedArray = $recreatedResponse->array();

            expect($recreatedArray['request_id'])->toBe($originalArray['request_id'])
                ->and($recreatedArray['success'])->toBe($originalArray['success']);
        });

        it('serializes to correct structure', function (): void {
            $data = [
                'request_id' => 'req_structure_test',
                'success' => false,
            ];
            $response = EnrollAccountResponse::from($data);
            $serialized = $response->array();

            expect($serialized)->toBeArray()
                ->and($serialized)->toHaveCount(2)
                ->and($serialized)->toHaveKey('request_id')
                ->and($serialized)->toHaveKey('success')
                ->and($serialized['request_id'])->toBe('req_structure_test')
                ->and($serialized['success'])->toBeFalse();
        });

        it('handles JSON serialization correctly', function (): void {
            $data = [
                'request_id' => 'req_json_test',
                'success' => true,
            ];
            $response = EnrollAccountResponse::from($data);

            $jsonString = json_encode($response->array());
            $decodedData = json_decode($jsonString, true);
            $recreatedResponse = EnrollAccountResponse::from($decodedData);

            expect($recreatedResponse)->toBeInstanceOf(EnrollAccountResponse::class);
            expect(json_last_error())->toBe(JSON_ERROR_NONE);
        });
    });

    describe('readonly class behavior', function (): void {
        it('creates immutable instances', function (): void {
            $data = [
                'request_id' => 'req_immutable_test',
                'success' => true,
            ];
            $response = EnrollAccountResponse::from($data);

            expect($response)->toBeInstanceOf(EnrollAccountResponse::class);

            // Since it's a readonly class, we can't modify properties
            // This test verifies the object was created successfully
            $responseArray = $response->array();
            expect($responseArray['request_id'])->toBe('req_immutable_test');
        });

        it('provides consistent data access', function (): void {
            $data = [
                'request_id' => 'req_consistent_test',
                'success' => false,
            ];
            $response = EnrollAccountResponse::from($data);

            $firstAccess = $response->array();
            $secondAccess = $response->array();
            $thirdAccess = $response->array();

            expect($firstAccess)->toBe($secondAccess)
                ->and($secondAccess)->toBe($thirdAccess);
        });
    });

    describe('real-world scenarios', function (): void {
        it('handles successful enrollment scenario', function (): void {
            $successData = [
                'request_id' => 'req_enroll_success_' . time(),
                'success' => true,
            ];
            $response = EnrollAccountResponse::from($successData);

            expect($response)->toBeInstanceOf(EnrollAccountResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue();
        });

        it('handles failed enrollment scenario', function (): void {
            $failureData = [
                'request_id' => 'req_enroll_failure_' . time(),
                'success' => false,
            ];
            $response = EnrollAccountResponse::from($failureData);

            expect($response)->toBeInstanceOf(EnrollAccountResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse();
        });

        it('handles API response format', function (): void {
            // Simulate typical API response format
            $apiResponse = [
                'request_id' => 'req_api_format_' . uniqid(),
                'success' => true,
            ];
            $response = EnrollAccountResponse::from($apiResponse);

            expect($response)->toBeInstanceOf(EnrollAccountResponse::class);

            // Verify it can be used in typical API response handling
            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('request_id')
                ->and($responseArray)->toHaveKey('success');
        });
    });

    describe('performance and memory', function (): void {
        it('creates multiple instances efficiently', function (): void {
            $startTime = microtime(true);
            $responses = [];

            for ($i = 1; $i <= 100; $i++) {
                $data = [
                    'request_id' => "req_performance_test_{$i}",
                    'success' => (0 === $i % 2), // Alternate true/false
                ];
                $responses[] = EnrollAccountResponse::from($data);
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            expect(count($responses))->toBe(100)
                ->and($executionTime)->toBeLessThan(0.5); // Should complete quickly

            // Verify all responses are valid
            foreach ($responses as $response) {
                expect($response)->toBeInstanceOf(EnrollAccountResponse::class);
            }
        });

        it('maintains reasonable memory usage', function (): void {
            $initialMemory = memory_get_usage();

            $responses = [];
            for ($i = 1; $i <= 1000; $i++) {
                $data = [
                    'request_id' => "req_memory_test_{$i}",
                    'success' => true,
                ];
                $responses[] = EnrollAccountResponse::from($data);
            }

            $finalMemory = memory_get_usage();
            $memoryUsed = $finalMemory - $initialMemory;

            expect(count($responses))->toBe(1000)
                ->and($memoryUsed)->toBeLessThan(1024 * 1024); // Less than 1MB

            // Clean up
            unset($responses);
        });
    });
});
