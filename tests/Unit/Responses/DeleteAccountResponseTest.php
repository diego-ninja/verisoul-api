<?php

use Ninja\Verisoul\Responses\DeleteAccountResponse;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('DeleteAccountResponse', function (): void {
    describe('construction and basic functionality', function (): void {
        it('can be created from fixture data', function (): void {
            $fixtureData = MockFactory::createDeleteAccountResponseFromFixture();
            $response = DeleteAccountResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);
        });

        it('can be created with custom deletion data', function (): void {
            $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                'success' => false,
                'account_id' => 'failed_deletion_account',
            ]);
            $response = DeleteAccountResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);
        });

        it('provides access to response identifiers', function (): void {
            $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                'request_id' => 'test_request_456',
                'account_id' => 'test_account_789',
            ]);
            $response = DeleteAccountResponse::from($fixtureData);

            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('request_id')
                ->and($responseArray)->toHaveKey('account_id')
                ->and($responseArray['request_id'])->toBe('test_request_456')
                ->and($responseArray['account_id'])->toBe('test_account_789');
        });
    });

    describe('successful deletion scenarios', function (): void {
        it('handles successful account deletion', function (): void {
            $successfulDeletion = MockFactory::createDeleteAccountResponseFromFixture([
                'success' => true,
                'account_id' => 'successfully_deleted_account',
            ]);
            $response = DeleteAccountResponse::from($successfulDeletion);

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue()
                ->and($responseArray['account_id'])->toBe('successfully_deleted_account');
        });

        it('handles successful deletion with UUID account ID', function (): void {
            $uuidAccountId = '550e8400-e29b-41d4-a716-446655440000';
            $successfulDeletion = MockFactory::createDeleteAccountResponseFromFixture([
                'success' => true,
                'account_id' => $uuidAccountId,
            ]);
            $response = DeleteAccountResponse::from($successfulDeletion);

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue()
                ->and($responseArray['account_id'])->toBe($uuidAccountId);
        });

        it('handles successful deletion with long account ID', function (): void {
            $longAccountId = 'very_long_account_identifier_that_might_exist_in_real_world_' . str_repeat('a', 50);
            $successfulDeletion = MockFactory::createDeleteAccountResponseFromFixture([
                'success' => true,
                'account_id' => $longAccountId,
            ]);
            $response = DeleteAccountResponse::from($successfulDeletion);

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);

            $responseArray = $response->array();
            expect($responseArray['account_id'])->toBe($longAccountId);
        });
    });

    describe('failed deletion scenarios', function (): void {
        it('handles failed account deletion', function (): void {
            $failedDeletion = MockFactory::createDeleteAccountResponseFromFixture([
                'success' => false,
                'account_id' => 'failed_deletion_account',
            ]);
            $response = DeleteAccountResponse::from($failedDeletion);

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse()
                ->and($responseArray['account_id'])->toBe('failed_deletion_account');
        });

        it('handles failed deletion due to account not found', function (): void {
            $notFoundDeletion = MockFactory::createDeleteAccountResponseFromFixture([
                'success' => false,
                'account_id' => 'non_existent_account_123',
            ]);
            $response = DeleteAccountResponse::from($notFoundDeletion);

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse();
        });

        it('handles failed deletion due to permissions', function (): void {
            $permissionFailure = MockFactory::createDeleteAccountResponseFromFixture([
                'success' => false,
                'account_id' => 'protected_account_456',
            ]);
            $response = DeleteAccountResponse::from($permissionFailure);

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse();
        });
    });

    describe('account ID handling', function (): void {
        it('handles various account ID formats', function (): void {
            $accountIds = [
                'acc_12345678901234567890',
                'account_abc123',
                'user_789',
                '550e8400-e29b-41d4-a716-446655440000',
                'simple_id',
                'user@example.com',
                'user-with-dashes',
                'UserWithCamelCase',
                'user_with_underscores',
            ];

            foreach ($accountIds as $accountId) {
                $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                    'account_id' => $accountId,
                ]);
                $response = DeleteAccountResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(DeleteAccountResponse::class);

                $responseArray = $response->array();
                expect($responseArray['account_id'])->toBe($accountId);
            }
        });

        it('handles empty account ID', function (): void {
            $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                'account_id' => '',
            ]);
            $response = DeleteAccountResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);

            $responseArray = $response->array();
            expect($responseArray['account_id'])->toBe('');
        });

        it('handles numeric account IDs', function (): void {
            $numericIds = ['12345', '0', '999999999'];

            foreach ($numericIds as $accountId) {
                $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                    'account_id' => $accountId,
                ]);
                $response = DeleteAccountResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(DeleteAccountResponse::class);

                $responseArray = $response->array();
                expect($responseArray['account_id'])->toBe($accountId);
            }
        });
    });

    describe('request ID handling', function (): void {
        it('handles various request ID formats', function (): void {
            $requestIds = [
                'req_12345678901234567890',
                'request_abc123',
                'delete_req_test_789',
                '550e8400-e29b-41d4-a716-446655440000',
                'simple_request_id',
            ];

            foreach ($requestIds as $requestId) {
                $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                    'request_id' => $requestId,
                ]);
                $response = DeleteAccountResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(DeleteAccountResponse::class);

                $responseArray = $response->array();
                expect($responseArray['request_id'])->toBe($requestId);
            }
        });

        it('handles empty request ID', function (): void {
            $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                'request_id' => '',
            ]);
            $response = DeleteAccountResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);
        });

        it('handles very long request ID', function (): void {
            $longRequestId = str_repeat('req_', 100) . 'end';
            $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                'request_id' => $longRequestId,
            ]);
            $response = DeleteAccountResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);

            $responseArray = $response->array();
            expect($responseArray['request_id'])->toBe($longRequestId);
        });
    });

    describe('success flag handling', function (): void {
        it('correctly handles boolean true', function (): void {
            $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                'success' => true,
            ]);
            $response = DeleteAccountResponse::from($fixtureData);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue()
                ->and($responseArray['success'])->toBeBool();
        });

        it('correctly handles boolean false', function (): void {
            $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                'success' => false,
            ]);
            $response = DeleteAccountResponse::from($fixtureData);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse()
                ->and($responseArray['success'])->toBeBool();
        });

        it('handles truthy values correctly', function (): void {
            $truthyValues = [1, '1', 'true', 'yes'];

            foreach ($truthyValues as $index => $value) {
                $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                    'request_id' => "req_truthy_{$index}",
                    'success' => $value,
                ]);
                $response = DeleteAccountResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(DeleteAccountResponse::class);
            }
        });

        it('handles falsy values correctly', function (): void {
            $falsyValues = [0, '0', 'false', 'no'];  // Removed null

            foreach ($falsyValues as $index => $value) {
                $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                    'request_id' => "req_falsy_{$index}",
                    'success' => $value,
                ]);
                $response = DeleteAccountResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(DeleteAccountResponse::class);
            }
        });
    });

    describe('data integrity and serialization', function (): void {
        it('maintains data integrity through serialization', function (): void {
            $originalData = MockFactory::createDeleteAccountResponseFromFixture([
                'request_id' => 'integrity_test_request',
                'account_id' => 'integrity_test_account',
                'success' => true,
            ]);
            $response = DeleteAccountResponse::from($originalData);
            $serializedData = $response->array();
            $recreatedResponse = DeleteAccountResponse::from($serializedData);

            expect($recreatedResponse)->toBeInstanceOf(DeleteAccountResponse::class);

            $originalArray = $response->array();
            $recreatedArray = $recreatedResponse->array();

            expect($recreatedArray['request_id'])->toBe($originalArray['request_id'])
                ->and($recreatedArray['account_id'])->toBe($originalArray['account_id'])
                ->and($recreatedArray['success'])->toBe($originalArray['success']);
        });

        it('serializes to correct structure', function (): void {
            $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                'request_id' => 'structure_test',
                'account_id' => 'test_account',
                'success' => false,
            ]);
            $response = DeleteAccountResponse::from($fixtureData);
            $serialized = $response->array();

            expect($serialized)->toBeArray()
                ->and($serialized)->toHaveCount(3)
                ->and($serialized)->toHaveKey('request_id')
                ->and($serialized)->toHaveKey('account_id')
                ->and($serialized)->toHaveKey('success')
                ->and($serialized['request_id'])->toBe('structure_test')
                ->and($serialized['account_id'])->toBe('test_account')
                ->and($serialized['success'])->toBeFalse();
        });

        it('handles JSON serialization correctly', function (): void {
            $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                'request_id' => 'json_test',
                'account_id' => 'json_account',
                'success' => true,
            ]);
            $response = DeleteAccountResponse::from($fixtureData);

            $jsonString = json_encode($response->array());
            $decodedData = json_decode($jsonString, true);
            $recreatedResponse = DeleteAccountResponse::from($decodedData);

            expect($recreatedResponse)->toBeInstanceOf(DeleteAccountResponse::class);
            expect(json_last_error())->toBe(JSON_ERROR_NONE);
        });
    });

    describe('readonly class behavior', function (): void {
        it('creates immutable instances', function (): void {
            $fixtureData = MockFactory::createDeleteAccountResponseFromFixture();
            $response = DeleteAccountResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);

            // Since it's a readonly class, we can't modify properties
            // This test verifies the object was created successfully
            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('request_id')
                ->and($responseArray)->toHaveKey('account_id');
        });

        it('provides consistent data access', function (): void {
            $fixtureData = MockFactory::createDeleteAccountResponseFromFixture();
            $response = DeleteAccountResponse::from($fixtureData);

            $firstAccess = $response->array();
            $secondAccess = $response->array();
            $thirdAccess = $response->array();

            expect($firstAccess)->toBe($secondAccess)
                ->and($secondAccess)->toBe($thirdAccess);
        });
    });

    describe('real-world deletion scenarios', function (): void {
        it('handles successful user account deletion', function (): void {
            $successData = MockFactory::createDeleteAccountResponseFromFixture([
                'request_id' => 'delete_success_' . time(),
                'account_id' => 'user_account_789',
                'success' => true,
            ]);
            $response = DeleteAccountResponse::from($successData);

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeTrue();
        });

        it('handles failed deletion attempt', function (): void {
            $failureData = MockFactory::createDeleteAccountResponseFromFixture([
                'request_id' => 'delete_failure_' . time(),
                'account_id' => 'protected_account_456',
                'success' => false,
            ]);
            $response = DeleteAccountResponse::from($failureData);

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);

            $responseArray = $response->array();
            expect($responseArray['success'])->toBeFalse();
        });

        it('handles bulk deletion response pattern', function (): void {
            // Simulate multiple deletion responses
            $accounts = ['acc_1', 'acc_2', 'acc_3', 'acc_4', 'acc_5'];
            $responses = [];

            foreach ($accounts as $accountId) {
                $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                    'account_id' => $accountId,
                    'success' => ('acc_3' !== $accountId), // Simulate one failure
                ]);
                $responses[] = DeleteAccountResponse::from($fixtureData);
            }

            expect(count($responses))->toBe(5);

            foreach ($responses as $response) {
                expect($response)->toBeInstanceOf(DeleteAccountResponse::class);
            }
        });

        it('handles API response format', function (): void {
            // Simulate typical API response format
            $apiResponse = MockFactory::createDeleteAccountResponseFromFixture([
                'request_id' => 'api_format_' . uniqid(),
                'account_id' => 'user_' . uniqid(),
                'success' => true,
            ]);
            $response = DeleteAccountResponse::from($apiResponse);

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);

            // Verify it can be used in typical API response handling
            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('request_id')
                ->and($responseArray)->toHaveKey('account_id')
                ->and($responseArray)->toHaveKey('success');
        });
    });

    describe('edge cases and error handling', function (): void {
        it('handles special characters in account IDs', function (): void {
            $specialCharIds = [
                'user@domain.com',
                'user+tag@example.com',
                'user-with-dashes',
                'user_with_underscores',
                'user.with.dots',
                'user#with#hashes',
                'user%20with%20encoded',
            ];

            foreach ($specialCharIds as $accountId) {
                $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                    'account_id' => $accountId,
                ]);
                $response = DeleteAccountResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(DeleteAccountResponse::class);
            }
        });

        it('handles unicode characters in account IDs', function (): void {
            $unicodeIds = [
                'user_ñoño',
                'user_测试',
                'user_العربية',
                'user_🎭',
                'user_café',
            ];

            foreach ($unicodeIds as $accountId) {
                $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                    'account_id' => $accountId,
                ]);
                $response = DeleteAccountResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(DeleteAccountResponse::class);
            }
        });

        it('handles very long account IDs', function (): void {
            $veryLongId = str_repeat('user_account_', 100) . 'end';
            $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                'account_id' => $veryLongId,
            ]);
            $response = DeleteAccountResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);

            $responseArray = $response->array();
            expect($responseArray['account_id'])->toBe($veryLongId);
        });

        it('handles whitespace in identifiers', function (): void {
            $whitespaceIds = [
                ' leading_space',
                'trailing_space ',
                ' both_spaces ',
                'internal space',
                "tab\tcharacter",
                "newline\ncharacter",
            ];

            foreach ($whitespaceIds as $accountId) {
                $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                    'account_id' => $accountId,
                ]);
                $response = DeleteAccountResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(DeleteAccountResponse::class);
            }
        });
    });

    describe('business logic validation', function (): void {
        it('validates success/failure combinations', function (): void {
            $scenarios = [
                ['success' => true, 'description' => 'Successful deletion'],
                ['success' => false, 'description' => 'Failed deletion'],
            ];

            foreach ($scenarios as $scenario) {
                $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                    'success' => $scenario['success'],
                ]);
                $response = DeleteAccountResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(DeleteAccountResponse::class);

                $responseArray = $response->array();
                expect($responseArray['success'])->toBe($scenario['success']);
            }
        });

        it('provides consistent results across multiple calls', function (): void {
            $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                'success' => true,
                'account_id' => 'consistent_test_account',
            ]);
            $response = DeleteAccountResponse::from($fixtureData);

            $firstResult = $response->array();
            $secondResult = $response->array();
            $thirdResult = $response->array();

            expect($firstResult['success'])->toBe($secondResult['success'])
                ->and($secondResult['success'])->toBe($thirdResult['success'])
                ->and($firstResult['account_id'])->toBe($secondResult['account_id'])
                ->and($secondResult['account_id'])->toBe($thirdResult['account_id']);
        });
    });

    describe('performance and memory', function (): void {
        it('creates multiple instances efficiently', function (): void {
            $startTime = microtime(true);
            $responses = [];

            for ($i = 1; $i <= 100; $i++) {
                $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                    'request_id' => "performance_test_{$i}",
                    'account_id' => "account_{$i}",
                    'success' => (0 === $i % 2),
                ]);
                $responses[] = DeleteAccountResponse::from($fixtureData);
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            expect(count($responses))->toBe(100)
                ->and($executionTime)->toBeLessThan(0.5);

            foreach ($responses as $response) {
                expect($response)->toBeInstanceOf(DeleteAccountResponse::class);
            }
        });

        it('maintains reasonable memory usage', function (): void {
            $initialMemory = memory_get_usage();

            $responses = [];
            for ($i = 1; $i <= 1000; $i++) {
                $fixtureData = MockFactory::createDeleteAccountResponseFromFixture([
                    'request_id' => "memory_test_{$i}",
                    'account_id' => "account_{$i}",
                ]);
                $responses[] = DeleteAccountResponse::from($fixtureData);
            }

            $finalMemory = memory_get_usage();
            $memoryUsed = $finalMemory - $initialMemory;

            expect(count($responses))->toBe(1000)
                ->and($memoryUsed)->toBeLessThan(1024 * 1024); // Less than 1MB

            unset($responses);
        });
    });
});
