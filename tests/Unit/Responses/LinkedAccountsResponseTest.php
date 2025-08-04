<?php

use Ninja\Verisoul\Responses\LinkedAccountsResponse;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('LinkedAccountsResponse', function (): void {
    describe('construction and basic functionality', function (): void {
        it('can be created from fixture data', function (): void {
            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture();
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });

        it('can be created with custom linked accounts data', function (): void {
            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'accounts_linked' => [],
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });

        it('provides access to response identifier', function (): void {
            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'request_id' => 'test_request_456',
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('request_id')
                ->and($responseArray['request_id'])->toBe('test_request_456');
        });
    });

    describe('linked accounts collection handling', function (): void {
        it('handles empty linked accounts collection', function (): void {
            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'accounts_linked' => [],
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);

            $responseArray = $response->array();
            expect($responseArray['accounts_linked'])->toBeArray()
                ->and($responseArray['accounts_linked'])->toBeEmpty();
        });

        it('handles single linked account', function (): void {
            $linkedAccount = [
                'account_id' => 'single_linked_account',
                'score' => 0.75,
                'match_type' => ['email', 'device'],
                'email' => 'single@example.com',
                'lists' => ['verified_users'],
                'metadata' => ['type' => 'personal'],
            ];

            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'accounts_linked' => [$linkedAccount],
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);

            $responseArray = $response->array();
            expect($responseArray['accounts_linked'])->toHaveCount(1);
        });

        it('handles multiple linked accounts', function (): void {
            $linkedAccounts = [
                [
                    'account_id' => 'linked_account_1',
                    'score' => 0.85,
                    'match_type' => ['email', 'device', 'network'],
                    'email' => 'user1@example.com',
                    'lists' => ['premium_users'],
                    'metadata' => ['tier' => 'gold'],
                ],
                [
                    'account_id' => 'linked_account_2',
                    'score' => 0.67,
                    'match_type' => ['device', 'browser'],
                    'email' => 'user2@example.com',
                    'lists' => ['standard_users'],
                    'metadata' => ['tier' => 'silver'],
                ],
                [
                    'account_id' => 'linked_account_3',
                    'score' => 0.92,
                    'match_type' => ['email', 'device', 'network', 'browser'],
                    'email' => 'user3@example.com',
                    'lists' => ['premium_users', 'verified_users'],
                    'metadata' => ['tier' => 'platinum'],
                ],
            ];

            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'accounts_linked' => $linkedAccounts,
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);

            $responseArray = $response->array();
            expect($responseArray['accounts_linked'])->toHaveCount(3);
        });
    });

    describe('linked account data structure validation', function (): void {
        it('handles various account ID formats', function (): void {
            $accountIdFormats = [
                'acc_12345678901234567890',
                'account_abc123',
                '550e8400-e29b-41d4-a716-446655440000',
                'user@example.com',
                'simple_id',
                'account-with-dashes',
                'AccountWithCamelCase',
            ];

            $linkedAccounts = [];
            foreach ($accountIdFormats as $accountId) {
                $linkedAccounts[] = [
                    'account_id' => $accountId,
                    'score' => 0.5,
                    'match_type' => ['device'],
                    'email' => 'test@example.com',
                    'lists' => [],
                    'metadata' => [],
                ];
            }

            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'accounts_linked' => $linkedAccounts,
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });

        it('handles various score ranges', function (): void {
            $scoreRanges = [0.0, 0.1, 0.25, 0.5, 0.75, 0.9, 1.0];
            $linkedAccounts = [];

            foreach ($scoreRanges as $index => $score) {
                $linkedAccounts[] = [
                    'account_id' => "score_test_account_{$index}",
                    'score' => $score,
                    'match_type' => ['email'],
                    'email' => "test{$index}@example.com",
                    'lists' => [],
                    'metadata' => [],
                ];
            }

            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'accounts_linked' => $linkedAccounts,
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });

        it('handles various match types', function (): void {
            $matchTypeCombinations = [
                ['email'],
                ['device'],
                ['network'],
                ['browser'],
                ['email', 'device'],
                ['device', 'network'],
                ['email', 'device', 'network'],
                ['email', 'device', 'network', 'browser'],
                ['browser', 'fingerprint'],
                ['location', 'timezone'],
            ];

            $linkedAccounts = [];
            foreach ($matchTypeCombinations as $index => $matchType) {
                $linkedAccounts[] = [
                    'account_id' => "match_type_test_{$index}",
                    'score' => 0.6,
                    'match_type' => $matchType,
                    'email' => "match{$index}@example.com",
                    'lists' => [],
                    'metadata' => [],
                ];
            }

            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'accounts_linked' => $linkedAccounts,
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });

        it('handles various email formats', function (): void {
            $emailFormats = [
                'simple@example.com',
                'user.name@example.com',
                'user+tag@example.com',
                'user123@example-domain.com',
                'very.long.email.address@very-long-domain-name.com',
                'user@subdomain.example.com',
                'user_name@example.org',
                'test-email@test.co.uk',
            ];

            $linkedAccounts = [];
            foreach ($emailFormats as $index => $email) {
                $linkedAccounts[] = [
                    'account_id' => "email_test_{$index}",
                    'score' => 0.7,
                    'match_type' => ['email'],
                    'email' => $email,
                    'lists' => [],
                    'metadata' => [],
                ];
            }

            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'accounts_linked' => $linkedAccounts,
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });

        it('handles various list configurations', function (): void {
            $listConfigurations = [
                [],
                ['single_list'],
                ['list_one', 'list_two'],
                ['premium_users', 'verified_users', 'beta_testers'],
                ['us_users', 'enterprise_clients', 'long_term_customers', 'high_value_accounts'],
            ];

            $linkedAccounts = [];
            foreach ($listConfigurations as $index => $lists) {
                $linkedAccounts[] = [
                    'account_id' => "list_test_{$index}",
                    'score' => 0.5,
                    'match_type' => ['device'],
                    'email' => "list{$index}@example.com",
                    'lists' => $lists,
                    'metadata' => [],
                ];
            }

            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'accounts_linked' => $linkedAccounts,
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });

        it('handles various metadata structures', function (): void {
            $metadataStructures = [
                [],
                ['simple' => 'value'],
                ['key1' => 'value1', 'key2' => 'value2'],
                ['nested' => ['inner_key' => 'inner_value']],
                ['array_value' => ['item1', 'item2', 'item3']],
                ['complex' => ['type' => 'premium', 'level' => 5, 'features' => ['feature1', 'feature2']]],
            ];

            $linkedAccounts = [];
            foreach ($metadataStructures as $index => $metadata) {
                $linkedAccounts[] = [
                    'account_id' => "metadata_test_{$index}",
                    'score' => 0.6,
                    'match_type' => ['email'],
                    'email' => "metadata{$index}@example.com",
                    'lists' => [],
                    'metadata' => $metadata,
                ];
            }

            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'accounts_linked' => $linkedAccounts,
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });
    });

    describe('request ID handling', function (): void {
        it('handles various request ID formats', function (): void {
            $requestIds = [
                'req_12345678901234567890',
                'request_abc123',
                'linked_accounts_req_789',
                '550e8400-e29b-41d4-a716-446655440000',
                'simple_request_id',
            ];

            foreach ($requestIds as $requestId) {
                $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                    'request_id' => $requestId,
                ]);
                $response = LinkedAccountsResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);

                $responseArray = $response->array();
                expect($responseArray['request_id'])->toBe($requestId);
            }
        });

        it('handles empty request ID', function (): void {
            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'request_id' => '',
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });

        it('handles very long request ID', function (): void {
            $longRequestId = str_repeat('req_', 100) . 'end';
            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'request_id' => $longRequestId,
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);

            $responseArray = $response->array();
            expect($responseArray['request_id'])->toBe($longRequestId);
        });
    });

    describe('data integrity and serialization', function (): void {
        it('maintains data integrity through serialization', function (): void {
            $originalData = MockFactory::createLinkedAccountsResponseFromFixture([
                'request_id' => 'integrity_test_request',
                'accounts_linked' => [
                    [
                        'account_id' => 'integrity_account_1',
                        'score' => 0.85,
                        'match_type' => ['email', 'device'],
                        'email' => 'integrity1@example.com',
                        'lists' => ['test_list'],
                        'metadata' => ['test' => 'value'],
                    ],
                ],
            ]);
            $response = LinkedAccountsResponse::from($originalData);
            $serializedData = $response->array();
            $recreatedResponse = LinkedAccountsResponse::from($serializedData);

            expect($recreatedResponse)->toBeInstanceOf(LinkedAccountsResponse::class);

            $originalArray = $response->array();
            $recreatedArray = $recreatedResponse->array();

            expect($recreatedArray['request_id'])->toBe($originalArray['request_id']);
        });

        it('handles complex nested data structures', function (): void {
            $complexLinkedAccounts = [
                [
                    'account_id' => 'complex_account_1',
                    'score' => 0.92,
                    'match_type' => ['email', 'device', 'network', 'browser'],
                    'email' => 'complex1@example.com',
                    'lists' => ['premium_users', 'verified_accounts', 'high_value_customers'],
                    'metadata' => [
                        'profile' => [
                            'tier' => 'platinum',
                            'joined_date' => '2023-01-15',
                            'preferences' => ['email_notifications', 'sms_alerts'],
                        ],
                        'activity' => [
                            'last_login' => '2024-01-15T10:30:00Z',
                            'session_count' => 150,
                            'countries' => ['US', 'CA', 'GB'],
                        ],
                    ],
                ],
            ];

            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'accounts_linked' => $complexLinkedAccounts,
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
            expect($response->array())->toBeArray();
        });

        it('serializes to correct structure', function (): void {
            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'request_id' => 'structure_test',
                'accounts_linked' => [
                    [
                        'account_id' => 'test_account',
                        'score' => 0.5,
                        'match_type' => ['email'],
                        'email' => 'test@example.com',
                        'lists' => [],
                        'metadata' => [],
                    ],
                ],
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);
            $serialized = $response->array();

            expect($serialized)->toBeArray()
                ->and($serialized)->toHaveCount(2)
                ->and($serialized)->toHaveKey('request_id')
                ->and($serialized)->toHaveKey('accounts_linked')
                ->and($serialized['request_id'])->toBe('structure_test')
                ->and($serialized['accounts_linked'])->toBeArray()
                ->and($serialized['accounts_linked'])->toHaveCount(1);
        });

        it('handles JSON serialization correctly', function (): void {
            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture();
            $response = LinkedAccountsResponse::from($fixtureData);

            $jsonString = json_encode($response->array());
            $decodedData = json_decode($jsonString, true);
            $recreatedResponse = LinkedAccountsResponse::from($decodedData);

            expect($recreatedResponse)->toBeInstanceOf(LinkedAccountsResponse::class);
            expect(json_last_error())->toBe(JSON_ERROR_NONE);
        });
    });

    describe('readonly class behavior', function (): void {
        it('creates immutable instances', function (): void {
            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture();
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);

            // Since it's a readonly class, we can't modify properties
            // This test verifies the object was created successfully
            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('request_id')
                ->and($responseArray)->toHaveKey('accounts_linked');
        });

        it('provides consistent data access', function (): void {
            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture();
            $response = LinkedAccountsResponse::from($fixtureData);

            $firstAccess = $response->array();
            $secondAccess = $response->array();
            $thirdAccess = $response->array();

            expect($firstAccess)->toBe($secondAccess)
                ->and($secondAccess)->toBe($thirdAccess);
        });
    });

    describe('real-world linked accounts scenarios', function (): void {
        it('handles family account linking scenario', function (): void {
            $familyAccounts = [
                [
                    'account_id' => 'family_parent_account',
                    'score' => 0.95,
                    'match_type' => ['email', 'device', 'network'],
                    'email' => 'parent@family.com',
                    'lists' => ['verified_users', 'family_accounts'],
                    'metadata' => ['role' => 'parent', 'family_id' => 'fam_123'],
                ],
                [
                    'account_id' => 'family_child_account',
                    'score' => 0.88,
                    'match_type' => ['device', 'network'],
                    'email' => 'child@family.com',
                    'lists' => ['family_accounts', 'minor_accounts'],
                    'metadata' => ['role' => 'child', 'family_id' => 'fam_123'],
                ],
            ];

            $familyData = MockFactory::createLinkedAccountsResponseFromFixture([
                'request_id' => 'family_linking_' . time(),
                'accounts_linked' => $familyAccounts,
            ]);
            $response = LinkedAccountsResponse::from($familyData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });

        it('handles business account linking scenario', function (): void {
            $businessAccounts = [
                [
                    'account_id' => 'business_admin_account',
                    'score' => 0.98,
                    'match_type' => ['email', 'device', 'network', 'browser'],
                    'email' => 'admin@business.com',
                    'lists' => ['enterprise_users', 'admin_accounts'],
                    'metadata' => ['role' => 'admin', 'company_id' => 'comp_456'],
                ],
                [
                    'account_id' => 'business_employee_account',
                    'score' => 0.75,
                    'match_type' => ['email', 'network'],
                    'email' => 'employee@business.com',
                    'lists' => ['enterprise_users', 'employee_accounts'],
                    'metadata' => ['role' => 'employee', 'company_id' => 'comp_456'],
                ],
            ];

            $businessData = MockFactory::createLinkedAccountsResponseFromFixture([
                'request_id' => 'business_linking_' . time(),
                'accounts_linked' => $businessAccounts,
            ]);
            $response = LinkedAccountsResponse::from($businessData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });

        it('handles suspicious account linking scenario', function (): void {
            $suspiciousAccounts = [
                [
                    'account_id' => 'suspicious_account_1',
                    'score' => 0.95,
                    'match_type' => ['device', 'browser', 'fingerprint'],
                    'email' => 'fake1@suspicious.com',
                    'lists' => ['high_risk_accounts'],
                    'metadata' => ['risk_level' => 'high', 'created_at' => date('c')],
                ],
                [
                    'account_id' => 'suspicious_account_2',
                    'score' => 0.92,
                    'match_type' => ['device', 'browser', 'fingerprint'],
                    'email' => 'fake2@suspicious.com',
                    'lists' => ['high_risk_accounts'],
                    'metadata' => ['risk_level' => 'high', 'created_at' => date('c')],
                ],
            ];

            $suspiciousData = MockFactory::createLinkedAccountsResponseFromFixture([
                'request_id' => 'suspicious_linking_' . time(),
                'accounts_linked' => $suspiciousAccounts,
            ]);
            $response = LinkedAccountsResponse::from($suspiciousData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });

        it('handles no linked accounts scenario', function (): void {
            $noLinkedData = MockFactory::createLinkedAccountsResponseFromFixture([
                'request_id' => 'no_links_' . time(),
                'accounts_linked' => [],
            ]);
            $response = LinkedAccountsResponse::from($noLinkedData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);

            $responseArray = $response->array();
            expect($responseArray['accounts_linked'])->toBeEmpty();
        });

        it('handles API response format', function (): void {
            // Simulate typical API response format
            $apiResponse = MockFactory::createLinkedAccountsResponseFromFixture();
            $response = LinkedAccountsResponse::from($apiResponse);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);

            // Verify it can be used in typical API response handling
            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('request_id')
                ->and($responseArray)->toHaveKey('accounts_linked');
        });
    });

    describe('edge cases and error handling', function (): void {
        it('handles extremely high match scores', function (): void {
            $highScoreAccount = [
                'account_id' => 'perfect_match_account',
                'score' => 1.0,
                'match_type' => ['email', 'device', 'network', 'browser', 'fingerprint'],
                'email' => 'perfect@match.com',
                'lists' => ['verified_users'],
                'metadata' => ['confidence' => 'perfect'],
            ];

            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'accounts_linked' => [$highScoreAccount],
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });

        it('handles extremely low match scores', function (): void {
            $lowScoreAccount = [
                'account_id' => 'weak_match_account',
                'score' => 0.01,
                'match_type' => ['partial_device'],
                'email' => 'weak@match.com',
                'lists' => [],
                'metadata' => ['confidence' => 'low'],
            ];

            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'accounts_linked' => [$lowScoreAccount],
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });

        it('handles accounts with many match types', function (): void {
            $manyMatchTypesAccount = [
                'account_id' => 'comprehensive_match_account',
                'score' => 0.85,
                'match_type' => [
                    'email', 'device', 'network', 'browser', 'fingerprint',
                    'location', 'timezone', 'language', 'screen_resolution',
                    'user_agent', 'ip_address', 'cookies',
                ],
                'email' => 'comprehensive@example.com',
                'lists' => ['power_users'],
                'metadata' => ['match_complexity' => 'high'],
            ];

            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'accounts_linked' => [$manyMatchTypesAccount],
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });

        it('handles large numbers of linked accounts', function (): void {
            $manyLinkedAccounts = [];
            for ($i = 1; $i <= 1000; $i++) {
                $manyLinkedAccounts[] = [
                    'account_id' => "massive_link_account_{$i}",
                    'score' => 0.5 + (0.5 * rand(0, 100) / 100),
                    'match_type' => ['device'],
                    'email' => "massive{$i}@example.com",
                    'lists' => [],
                    'metadata' => [],
                ];
            }

            $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                'accounts_linked' => $manyLinkedAccounts,
            ]);
            $response = LinkedAccountsResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);

            $responseArray = $response->array();
            expect($responseArray['accounts_linked'])->toHaveCount(1000);
        });
    });

    describe('performance and memory', function (): void {
        it('creates multiple instances efficiently', function (): void {
            $startTime = microtime(true);
            $responses = [];

            for ($i = 1; $i <= 50; $i++) {
                $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture([
                    'request_id' => "performance_test_{$i}",
                ]);
                $responses[] = LinkedAccountsResponse::from($fixtureData);
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            expect(count($responses))->toBe(50)
                ->and($executionTime)->toBeLessThan(1.0);

            foreach ($responses as $response) {
                expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
            }
        });

        it('maintains reasonable memory usage with complex linked accounts', function (): void {
            $initialMemory = memory_get_usage();

            $responses = [];
            for ($i = 1; $i <= 100; $i++) {
                $fixtureData = MockFactory::createLinkedAccountsResponseFromFixture();
                $responses[] = LinkedAccountsResponse::from($fixtureData);
            }

            $finalMemory = memory_get_usage();
            $memoryUsed = $finalMemory - $initialMemory;

            expect(count($responses))->toBe(100)
                ->and($memoryUsed)->toBeLessThan(3 * 1024 * 1024); // Less than 3MB due to complexity

            unset($responses);
        });
    });
});
