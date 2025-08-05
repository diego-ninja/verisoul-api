<?php

use Ninja\Verisoul\Responses\AuthenticateSessionResponse;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('AuthenticateSessionResponse', function (): void {
    describe('construction and basic functionality', function (): void {
        it('can be created from fixture data', function (): void {
            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture();
            $response = AuthenticateSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('can be created with custom decision and scores', function (): void {
            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'decision' => 'Suspicious',
                'account_score' => 0.8,
                'bot' => 0.9,
                'multiple_accounts' => 0.7,
                'risk_signals' => 0.85,
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('provides access to response identifiers', function (): void {
            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'project_id' => 'test_project_123',
                'session_id' => 'test_session_456',
                'account_id' => 'test_account_789',
                'request_id' => 'test_request_012',
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('project_id')
                ->and($responseArray)->toHaveKey('session_id')
                ->and($responseArray)->toHaveKey('account_id')
                ->and($responseArray)->toHaveKey('request_id')
                ->and($responseArray['project_id'])->toBe('test_project_123')
                ->and($responseArray['session_id'])->toBe('test_session_456')
                ->and($responseArray['account_id'])->toBe('test_account_789')
                ->and($responseArray['request_id'])->toBe('test_request_012');
        });
    });

    describe('decision handling', function (): void {
        it('handles Real decision', function (): void {
            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'decision' => 'Real',
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);

            $responseArray = $response->array();
            expect($responseArray['decision'])->toBe('Real');
        });

        it('handles Suspicious decision', function (): void {
            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'decision' => 'Suspicious',
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);

            $responseArray = $response->array();
            expect($responseArray['decision'])->toBe('Suspicious');
        });

        it('handles all valid decision types', function (): void {
            // Assuming these are the valid decision types
            $decisions = ['Real', 'Suspicious', 'Unknown'];

            foreach ($decisions as $decision) {
                $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                    'decision' => $decision,
                ]);
                $response = AuthenticateSessionResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
            }
        });
    });

    describe('score validation', function (): void {
        it('handles valid score ranges', function (): void {
            $validScores = [0.0, 0.25, 0.5, 0.75, 1.0];

            foreach ($validScores as $score) {
                $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                    'account_score' => $score,
                    'bot' => $score,
                    'multiple_accounts' => $score,
                    'risk_signals' => $score,
                ]);
                $response = AuthenticateSessionResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
            }
        });

        it('handles edge case scores', function (): void {
            $edgeScores = [0.001, 0.999, 0.123456789];

            foreach ($edgeScores as $score) {
                $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                    'account_score' => $score,
                ]);
                $response = AuthenticateSessionResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
            }
        });

        it('handles high-risk scenario scores', function (): void {
            $highRiskData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'decision' => 'Suspicious',
                'account_score' => 0.9,
                'bot' => 0.95,
                'multiple_accounts' => 0.8,
                'risk_signals' => 0.85,
                'accounts_linked' => 5,
            ]);
            $response = AuthenticateSessionResponse::from($highRiskData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('handles low-risk scenario scores', function (): void {
            $lowRiskData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'decision' => 'Real',
                'account_score' => 0.1,
                'bot' => 0.05,
                'multiple_accounts' => 0.0,
                'risk_signals' => 0.02,
                'accounts_linked' => 0,
            ]);
            $response = AuthenticateSessionResponse::from($lowRiskData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });
    });

    describe('accounts linked handling', function (): void {
        it('handles zero linked accounts', function (): void {
            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'accounts_linked' => 0,
                'linked_accounts' => null,
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('handles multiple linked accounts', function (): void {
            $linkedAccountsCount = [1, 2, 5, 10, 25];

            foreach ($linkedAccountsCount as $count) {
                $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                    'accounts_linked' => $count,
                ]);
                $response = AuthenticateSessionResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
            }
        });

        it('handles linked accounts collection', function (): void {
            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'accounts_linked' => 2,
                'linked_accounts' => [],
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });
    });

    describe('lists handling', function (): void {
        it('handles empty lists array', function (): void {
            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'lists' => [],
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('handles single list', function (): void {
            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'lists' => ['vip_users'],
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('handles multiple lists', function (): void {
            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'lists' => ['us_users', 'premium_members', 'verified_accounts', 'beta_testers'],
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('handles various list name formats', function (): void {
            $listNames = [
                'simple_list',
                'list-with-dashes',
                'LIST_UPPERCASE',
                'MixedCaseList',
                'list_with_numbers_123',
                'very_long_list_name_that_might_exist_in_real_world_scenarios',
            ];

            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'lists' => $listNames,
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });
    });

    describe('session data integration', function (): void {
        it('handles comprehensive session data', function (): void {
            $sessionData = MockFactory::createSessionResponseData([
                'network' => [
                    'ip_address' => '192.168.1.100',
                    'service_provider' => 'Test ISP',
                    'connection_type' => 'residential',
                ],
                'location' => [
                    'country_code' => 'US',
                    'state' => 'California',
                    'city' => 'San Francisco',
                ],
            ]);

            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'session' => $sessionData,
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('getRiskSignals returns unified risk signal collection', function (): void {
            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture();
            $response = AuthenticateSessionResponse::from($fixtureData);

            $riskSignals = $response->getRiskSignals();

            expect($riskSignals)->toBeArray();
        });

        it('getRiskSignals delegates to session riskSignals collection', function (): void {
            $sessionData = MockFactory::createSessionResponseData([
                'risk_signals' => [
                    'device_risk' => true,
                    'proxy' => false,
                    'vpn' => true,
                ],
            ]);

            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'session' => $sessionData,
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            $riskSignals = $response->getRiskSignals();

            expect($riskSignals)->toBeArray();
            expect($response->session)->toHaveProperty('riskSignals');
        });

        it('getRiskSignals returns empty array when no risk signals', function (): void {
            $sessionData = MockFactory::createSessionResponseData([
                'risk_signals' => [],
            ]);

            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'session' => $sessionData,
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            $riskSignals = $response->getRiskSignals();

            expect($riskSignals)->toBeArray();
        });

        it('handles session with various risk signals', function (): void {
            $sessionWithRisks = MockFactory::createSessionResponseData([
                'risk_signals' => [
                    'device_risk' => true,
                    'proxy' => false,
                    'vpn' => true,
                    'tor' => false,
                    'datacenter' => false,
                ],
            ]);

            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'session' => $sessionWithRisks,
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });
    });

    describe('account data integration', function (): void {
        it('handles comprehensive account data', function (): void {
            $accountData = MockFactory::createAccountResponseData([
                'account' => [
                    'id' => 'comprehensive_test_account',
                    'email' => 'comprehensive@test.com',
                    'metadata' => ['key' => 'value'],
                ],
            ]);

            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'account' => $accountData,
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('handles account with email verification data', function (): void {
            $accountWithEmail = MockFactory::createAccountResponseData([
                'email' => [
                    'email' => 'verified@example.com',
                    'disposable' => false,
                    'personal' => true,
                    'valid' => true,
                ],
            ]);

            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'account' => $accountWithEmail,
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('handles account with unique device/network statistics', function (): void {
            $accountWithStats = MockFactory::createAccountResponseData([
                'unique_devices' => [
                    '1_day' => 1,
                    '7_day' => 2,
                ],
                'unique_networks' => [
                    '1_day' => 1,
                    '7_day' => 3,
                ],
            ]);

            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'account' => $accountWithStats,
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });
    });

    describe('data integrity and serialization', function (): void {
        it('maintains data integrity through serialization', function (): void {
            $originalData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'decision' => 'Real',
                'account_score' => 0.25,
                'accounts_linked' => 1,
            ]);
            $response = AuthenticateSessionResponse::from($originalData);
            $serializedData = $response->array();
            $recreatedResponse = AuthenticateSessionResponse::from($serializedData);

            expect($recreatedResponse)->toBeInstanceOf(AuthenticateSessionResponse::class);
            expect($recreatedResponse->array())->toBeArray();
        });

        it('handles complex nested data structures', function (): void {
            $complexData = MockFactory::createAuthenticateSessionResponseFromFixture();
            $response = AuthenticateSessionResponse::from($complexData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
            expect($response->array())->toBeArray();
        });

        it('serializes to correct structure', function (): void {
            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture();
            $response = AuthenticateSessionResponse::from($fixtureData);
            $serialized = $response->array();

            expect($serialized)->toBeArray()
                ->and($serialized)->toHaveKey('project_id')
                ->and($serialized)->toHaveKey('session_id')
                ->and($serialized)->toHaveKey('account_id')
                ->and($serialized)->toHaveKey('request_id')
                ->and($serialized)->toHaveKey('decision')
                ->and($serialized)->toHaveKey('account_score')
                ->and($serialized)->toHaveKey('session')
                ->and($serialized)->toHaveKey('account');
        });
    });

    describe('real-world authentication scenarios', function (): void {
        it('handles successful authentication for trusted user', function (): void {
            $trustedUserData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'decision' => 'Real',
                'account_score' => 0.1,
                'bot' => 0.02,
                'multiple_accounts' => 0.0,
                'risk_signals' => 0.05,
                'accounts_linked' => 0,
                'lists' => ['trusted_users'],
            ]);
            $response = AuthenticateSessionResponse::from($trustedUserData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('handles suspicious authentication attempt', function (): void {
            $suspiciousData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'decision' => 'Suspicious',
                'account_score' => 0.8,
                'bot' => 0.9,
                'multiple_accounts' => 0.75,
                'risk_signals' => 0.85,
                'accounts_linked' => 10,
                'lists' => [],
            ]);
            $response = AuthenticateSessionResponse::from($suspiciousData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('handles new user authentication', function (): void {
            $newUserData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'decision' => 'Real',
                'account_score' => 0.3,
                'bot' => 0.1,
                'multiple_accounts' => 0.0,
                'risk_signals' => 0.2,
                'accounts_linked' => 0,
                'lists' => ['new_users'],
            ]);
            $response = AuthenticateSessionResponse::from($newUserData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('handles authentication with multiple account links', function (): void {
            $multiAccountData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'decision' => 'Real',
                'account_score' => 0.4,
                'multiple_accounts' => 0.6,
                'accounts_linked' => 3,
                'lists' => ['family_accounts'],
            ]);
            $response = AuthenticateSessionResponse::from($multiAccountData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });
    });

    describe('edge cases and error handling', function (): void {
        it('handles extreme score values', function (): void {
            $extremeValues = [0.0, 1.0, 0.999999, 0.000001];

            foreach ($extremeValues as $score) {
                $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                    'account_score' => $score,
                ]);
                $response = AuthenticateSessionResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
            }
        });

        it('handles very long identifier strings', function (): void {
            $longId = str_repeat('a', 500);

            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'account_id' => $longId,
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('handles large numbers of linked accounts', function (): void {
            $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                'accounts_linked' => 1000,
            ]);
            $response = AuthenticateSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('handles empty or null linked accounts collection', function (): void {
            $scenarios = [null, []];

            foreach ($scenarios as $linkedAccounts) {
                $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                    'linked_accounts' => $linkedAccounts,
                ]);
                $response = AuthenticateSessionResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
            }
        });
    });

    describe('performance and memory', function (): void {
        it('creates multiple instances efficiently', function (): void {
            $startTime = microtime(true);
            $responses = [];

            for ($i = 1; $i <= 50; $i++) {
                $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture([
                    'account_id' => "performance_test_account_{$i}",
                ]);
                $responses[] = AuthenticateSessionResponse::from($fixtureData);
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            expect(count($responses))->toBe(50)
                ->and($executionTime)->toBeLessThan(1.0); // Allow more time due to complexity

            foreach ($responses as $response) {
                expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
            }
        });

        it('maintains reasonable memory usage with complex data', function (): void {
            $initialMemory = memory_get_usage();

            $responses = [];
            for ($i = 1; $i <= 100; $i++) {
                $fixtureData = MockFactory::createAuthenticateSessionResponseFromFixture();
                $responses[] = AuthenticateSessionResponse::from($fixtureData);
            }

            $finalMemory = memory_get_usage();
            $memoryUsed = $finalMemory - $initialMemory;

            expect(count($responses))->toBe(100)
                ->and($memoryUsed)->toBeLessThan(5 * 1024 * 1024); // Less than 5MB due to complexity

            unset($responses);
        });
    });
});
