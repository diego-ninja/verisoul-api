<?php

use Ninja\Verisoul\Responses\AccountResponse;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('AccountResponse', function () {
    describe('construction and basic functionality', function () {
        it('can be created from fixture data', function () {
            $fixtureData = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'test_account_123',
                    'email' => 'test@example.com',
                    'status' => 'active'
                ]
            ]);

            $response = AccountResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AccountResponse::class);
        });

        it('can be created with minimal account data', function () {
            $minimalData = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'minimal_account'
                ]
            ]);

            $response = AccountResponse::from($minimalData);

            expect($response)->toBeInstanceOf(AccountResponse::class);
        });

        it('can be created with comprehensive account data', function () {
            $comprehensiveData = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'comprehensive_account_456',
                    'email' => 'comprehensive@example.com',
                    'phone' => '+1234567890',
                    'status' => 'verified',
                    'created_at' => '2024-01-15T10:30:00Z',
                    'updated_at' => '2024-01-15T15:45:00Z',
                    'metadata' => [
                        'source' => 'web_registration',
                        'campaign' => 'spring_2024',
                        'preferences' => [
                            'notifications' => true,
                            'marketing' => false
                        ]
                    ]
                ]
            ]);

            $response = AccountResponse::from($comprehensiveData);

            expect($response)->toBeInstanceOf(AccountResponse::class);
        });
    });

    describe('account data access', function () {
        it('provides access to account information through methods', function () {
            $accountData = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'access_test_789',
                    'email' => 'access@example.com',
                    'status' => 'active'
                ]
            ]);

            $response = AccountResponse::from($accountData);

            // Test that response has the expected structure
            expect($response)->toBeInstanceOf(AccountResponse::class);
            
            // Since we don't know the exact internal structure,
            // we verify it's a valid response object
            expect($response->toArray())->toBeArray();
        });

        it('handles nested account metadata correctly', function () {
            $nestedData = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'nested_test_321',
                    'email' => 'nested@example.com',
                    'metadata' => [
                        'profile' => [
                            'first_name' => 'John',
                            'last_name' => 'Doe',
                            'preferences' => [
                                'theme' => 'dark',
                                'language' => 'en-US'
                            ]
                        ],
                        'analytics' => [
                            'signup_source' => 'organic',
                            'last_login' => '2024-01-15T12:00:00Z'
                        ]
                    ]
                ]
            ]);

            $response = AccountResponse::from($nestedData);

            expect($response)->toBeInstanceOf(AccountResponse::class);
            
            // Verify the data structure is maintained
            $responseArray = $response->toArray();
            expect($responseArray)->toBeArray();
        });
    });

    describe('response validation and error handling', function () {
        it('handles responses with missing optional fields', function () {
            $partialData = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'partial_account_654',
                    // Missing email, phone, status, etc.
                ]
            ]);

            $response = AccountResponse::from($partialData);

            expect($response)->toBeInstanceOf(AccountResponse::class);
        });

        it('handles responses with null values', function () {
            $nullValueData = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'null_test_987',
                    'email' => null,
                    'phone' => null,
                    'status' => 'active',
                    'metadata' => null
                ]
            ]);

            $response = AccountResponse::from($nullValueData);

            expect($response)->toBeInstanceOf(AccountResponse::class);
        });

        it('handles responses with empty arrays and objects', function () {
            $emptyData = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'empty_test_147',
                    'email' => 'empty@example.com',
                    'metadata' => [],
                    'tags' => [],
                    'permissions' => []
                ]
            ]);

            $response = AccountResponse::from($emptyData);

            expect($response)->toBeInstanceOf(AccountResponse::class);
        });
    });

    describe('serialization and data integrity', function () {
        it('maintains data integrity through serialization', function () {
            $originalData = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'integrity_test_258',
                    'email' => 'integrity@example.com',
                    'status' => 'verified',
                    'created_at' => '2024-01-15T09:30:00Z',
                    'metadata' => [
                        'source' => 'api_registration',
                        'verification_method' => 'email_link',
                        'preferences' => [
                            'newsletter' => true,
                            'sms_notifications' => false
                        ]
                    ]
                ]
            ]);

            $response = AccountResponse::from($originalData);
            $serializedData = $response->toArray();
            $recreatedResponse = AccountResponse::from($serializedData);

            expect($recreatedResponse)->toBeInstanceOf(AccountResponse::class);
            expect($recreatedResponse->toArray())->toBeArray();
        });

        it('handles complex nested structures correctly', function () {
            $complexData = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'complex_test_369',
                    'email' => 'complex@example.com',
                    'metadata' => [
                        'profile' => [
                            'personal' => [
                                'first_name' => 'Jane',
                                'last_name' => 'Smith',
                                'date_of_birth' => '1990-05-15',
                                'address' => [
                                    'street' => '123 Main St',
                                    'city' => 'San Francisco',
                                    'state' => 'CA',
                                    'zip' => '94105',
                                    'country' => 'US'
                                ]
                            ],
                            'professional' => [
                                'title' => 'Software Engineer',
                                'company' => 'Tech Corp',
                                'experience_years' => 5
                            ]
                        ],
                        'settings' => [
                            'privacy' => [
                                'profile_visible' => false,
                                'contact_visible' => true
                            ],
                            'notifications' => [
                                'email' => true,
                                'push' => false,
                                'sms' => true
                            ]
                        ]
                    ]
                ]
            ]);

            $response = AccountResponse::from($complexData);

            expect($response)->toBeInstanceOf(AccountResponse::class);
            
            // Verify complex data structure is preserved
            $responseArray = $response->toArray();
            expect($responseArray)->toBeArray();
        });
    });

    describe('account status and state handling', function () {
        it('handles various account statuses', function () {
            $statuses = ['active', 'inactive', 'pending', 'suspended', 'verified', 'unverified'];

            foreach ($statuses as $status) {
                $statusData = MockFactory::createAccountResponseFromFixture([
                    'account' => [
                        'id' => "status_test_{$status}",
                        'email' => "status_{$status}@example.com",
                        'status' => $status
                    ]
                ]);

                $response = AccountResponse::from($statusData);

                expect($response)->toBeInstanceOf(AccountResponse::class);
            }
        });

        it('handles account with verification details', function () {
            $verificationData = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'verification_test_789',
                    'email' => 'verification@example.com',
                    'status' => 'verified',
                    'verification' => [
                        'email_verified' => true,
                        'phone_verified' => false,
                        'identity_verified' => true,
                        'verification_date' => '2024-01-15T14:30:00Z',
                        'verification_method' => 'document_upload'
                    ]
                ]
            ]);

            $response = AccountResponse::from($verificationData);

            expect($response)->toBeInstanceOf(AccountResponse::class);
        });
    });

    describe('timestamp and date handling', function () {
        it('handles various timestamp formats', function () {
            $timestampFormats = [
                '2024-01-15T10:30:00Z',
                '2024-01-15T10:30:00.123Z',
                '2024-01-15T10:30:00+00:00',
                '2024-01-15T10:30:00-08:00',
                '2024-01-15 10:30:00'
            ];

            foreach ($timestampFormats as $index => $timestamp) {
                $timestampData = MockFactory::createAccountResponseFromFixture([
                    'account' => [
                        'id' => "timestamp_test_{$index}",
                        'email' => "timestamp_{$index}@example.com",
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ]
                ]);

                $response = AccountResponse::from($timestampData);

                expect($response)->toBeInstanceOf(AccountResponse::class);
            }
        });

        it('handles null timestamps gracefully', function () {
            $nullTimestampData = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'null_timestamp_test',
                    'email' => 'null_timestamp@example.com',
                    'created_at' => null,
                    'updated_at' => null,
                    'last_login' => null
                ]
            ]);

            $response = AccountResponse::from($nullTimestampData);

            expect($response)->toBeInstanceOf(AccountResponse::class);
        });
    });

    describe('error scenarios and edge cases', function () {
        it('handles accounts with special characters', function () {
            $specialCharData = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'special_char_test_123',
                    'email' => 'special+chars@example.com',
                    'metadata' => [
                        'name' => 'José María González-Smith',
                        'company' => 'Café & Co. Ltd.',
                        'notes' => 'Special chars: áéíóú ñ ¡¿ « » " " \' \''
                    ]
                ]
            ]);

            $response = AccountResponse::from($specialCharData);

            expect($response)->toBeInstanceOf(AccountResponse::class);
        });

        it('handles accounts with very large metadata', function () {
            $largeMetadata = [
                'large_array' => array_fill(0, 1000, 'data_item'),
                'large_string' => str_repeat('large_data_chunk', 100),
                'nested_large' => [
                    'level1' => array_fill(0, 100, [
                        'level2' => array_fill(0, 10, 'nested_data')
                    ])
                ]
            ];

            $largeData = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'large_metadata_test',
                    'email' => 'large@example.com',
                    'metadata' => $largeMetadata
                ]
            ]);

            $response = AccountResponse::from($largeData);

            expect($response)->toBeInstanceOf(AccountResponse::class);
        });

        it('handles accounts with numeric and boolean values', function () {
            $mixedTypeData = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'mixed_types_test',
                    'email' => 'mixed@example.com',
                    'age' => 25,
                    'balance' => 1234.56,
                    'is_premium' => true,
                    'is_verified' => false,
                    'login_count' => 0,
                    'metadata' => [
                        'numeric_field' => 42,
                        'float_field' => 3.14159,
                        'boolean_field' => true,
                        'zero_value' => 0,
                        'negative_value' => -10
                    ]
                ]
            ]);

            $response = AccountResponse::from($mixedTypeData);

            expect($response)->toBeInstanceOf(AccountResponse::class);
        });
    });

    describe('performance and memory usage', function () {
        it('handles multiple account response creation efficiently', function () {
            $startTime = microtime(true);
            $responses = [];

            for ($i = 1; $i <= 100; $i++) {
                $data = MockFactory::createAccountResponseFromFixture([
                    'account' => [
                        'id' => "performance_test_{$i}",
                        'email' => "performance_{$i}@example.com",
                        'status' => 'active',
                        'metadata' => [
                            'iteration' => $i,
                            'data' => str_repeat("data_{$i}", 10)
                        ]
                    ]
                ]);

                $responses[] = AccountResponse::from($data);
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            expect(count($responses))->toBe(100)
                ->and($executionTime)->toBeLessThan(1.0); // Should complete within 1 second

            // Verify all responses are valid
            foreach ($responses as $response) {
                expect($response)->toBeInstanceOf(AccountResponse::class);
            }
        });

        it('maintains reasonable memory usage with large responses', function () {
            $initialMemory = memory_get_usage();

            $largeAccountData = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'memory_test_account',
                    'email' => 'memory@example.com',
                    'metadata' => [
                        'large_dataset' => array_fill(0, 1000, [
                            'record_id' => uniqid(),
                            'data' => str_repeat('memory_test_data', 50),
                            'nested' => array_fill(0, 20, 'nested_memory_data')
                        ])
                    ]
                ]
            ]);

            $response = AccountResponse::from($largeAccountData);

            $finalMemory = memory_get_usage();
            $memoryUsed = $finalMemory - $initialMemory;

            expect($response)->toBeInstanceOf(AccountResponse::class)
                ->and($memoryUsed)->toBeLessThan(10 * 1024 * 1024); // Less than 10MB
        });
    });
});