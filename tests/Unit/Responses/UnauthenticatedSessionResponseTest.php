<?php

use Ninja\Verisoul\Responses\UnauthenticatedSessionResponse;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('UnauthenticatedSessionResponse', function (): void {
    describe('construction and basic functionality', function (): void {
        it('can be created from fixture data', function (): void {
            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture();
            $response = UnauthenticatedSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
        });

        it('can be created with custom decision and scores', function (): void {
            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'decision' => 'Suspicious',
                'account_score' => 0.85,
                'bot' => 0.9,
                'multiple_accounts' => 0.7,
                'risk_signals' => 0.8,
            ]);
            $response = UnauthenticatedSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
        });

        it('provides access to response identifiers', function (): void {
            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'project_id' => 'test_project_123',
                'session_id' => 'test_session_456',
                'request_id' => 'test_request_789',
            ]);
            $response = UnauthenticatedSessionResponse::from($fixtureData);

            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('project_id')
                ->and($responseArray)->toHaveKey('session_id')
                ->and($responseArray)->toHaveKey('request_id')
                ->and($responseArray['project_id'])->toBe('test_project_123')
                ->and($responseArray['session_id'])->toBe('test_session_456')
                ->and($responseArray['request_id'])->toBe('test_request_789');
        });
    });

    describe('decision handling', function (): void {
        it('handles Real decision', function (): void {
            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'decision' => 'Real',
            ]);
            $response = UnauthenticatedSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);

            $responseArray = $response->array();
            expect($responseArray['decision'])->toBe('Real');
        });

        it('handles Suspicious decision', function (): void {
            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'decision' => 'Suspicious',
            ]);
            $response = UnauthenticatedSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);

            $responseArray = $response->array();
            expect($responseArray['decision'])->toBe('Suspicious');
        });

        it('handles all valid decision types', function (): void {
            $decisions = ['Real', 'Suspicious', 'Unknown'];

            foreach ($decisions as $decision) {
                $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                    'decision' => $decision,
                ]);
                $response = UnauthenticatedSessionResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
            }
        });
    });

    describe('score validation', function (): void {
        it('handles valid score ranges', function (): void {
            $validScores = [0.0, 0.25, 0.5, 0.75, 1.0];

            foreach ($validScores as $score) {
                $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                    'account_score' => $score,
                    'bot' => $score,
                    'multiple_accounts' => $score,
                    'risk_signals' => $score,
                ]);
                $response = UnauthenticatedSessionResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
            }
        });

        it('handles edge case scores', function (): void {
            $edgeScores = [0.001, 0.999, 0.123456789];

            foreach ($edgeScores as $score) {
                $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                    'account_score' => $score,
                ]);
                $response = UnauthenticatedSessionResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
            }
        });

        it('handles high-risk scenario scores', function (): void {
            $highRiskData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'decision' => 'Suspicious',
                'account_score' => 0.9,
                'bot' => 0.95,
                'multiple_accounts' => 0.8,
                'risk_signals' => 0.85,
                'accounts_linked' => 8,
            ]);
            $response = UnauthenticatedSessionResponse::from($highRiskData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
        });

        it('handles low-risk scenario scores', function (): void {
            $lowRiskData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'decision' => 'Real',
                'account_score' => 0.05,
                'bot' => 0.01,
                'multiple_accounts' => 0.0,
                'risk_signals' => 0.03,
                'accounts_linked' => 0,
            ]);
            $response = UnauthenticatedSessionResponse::from($lowRiskData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
        });
    });

    describe('accounts linked handling', function (): void {
        it('handles zero linked accounts', function (): void {
            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'accounts_linked' => 0,
            ]);
            $response = UnauthenticatedSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);

            $responseArray = $response->array();
            expect($responseArray['accounts_linked'])->toBe(0);
        });

        it('handles multiple linked accounts', function (): void {
            $linkedAccountsCount = [1, 2, 5, 10, 25];

            foreach ($linkedAccountsCount as $count) {
                $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                    'accounts_linked' => $count,
                ]);
                $response = UnauthenticatedSessionResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);

                $responseArray = $response->array();
                expect($responseArray['accounts_linked'])->toBe($count);
            }
        });

        it('handles high numbers of linked accounts', function (): void {
            $highCounts = [50, 100, 500];

            foreach ($highCounts as $count) {
                $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                    'accounts_linked' => $count,
                ]);
                $response = UnauthenticatedSessionResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
            }
        });
    });

    describe('lists handling', function (): void {
        it('handles empty lists array', function (): void {
            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'lists' => [],
            ]);
            $response = UnauthenticatedSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);

            $responseArray = $response->array();
            expect($responseArray['lists'])->toBeEmpty();
        });

        it('handles single list', function (): void {
            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'lists' => ['anonymous_users'],
            ]);
            $response = UnauthenticatedSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);

            $responseArray = $response->array();
            expect($responseArray['lists'])->toContain('anonymous_users');
        });

        it('handles multiple lists', function (): void {
            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'lists' => ['visitors', 'trial_users', 'anonymous_sessions', 'unverified'],
            ]);
            $response = UnauthenticatedSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);

            $responseArray = $response->array();
            expect($responseArray['lists'])->toHaveCount(4);
        });

        it('handles various list name formats', function (): void {
            $listNames = [
                'simple_list',
                'list-with-dashes',
                'LIST_UPPERCASE',
                'MixedCaseList',
                'list_with_numbers_123',
                'anonymous_session_visitors',
            ];

            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'lists' => $listNames,
            ]);
            $response = UnauthenticatedSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
        });
    });

    describe('session data integration', function (): void {
        it('handles comprehensive session data', function (): void {
            $sessionData = MockFactory::createSessionResponseData([
                'network' => [
                    'ip_address' => '203.0.113.1',
                    'service_provider' => 'Unknown ISP',
                    'connection_type' => 'mobile',
                ],
                'location' => [
                    'country_code' => 'US',
                    'state' => 'Unknown',
                    'city' => 'Unknown',
                ],
            ]);

            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'session' => $sessionData,
            ]);
            $response = UnauthenticatedSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
        });

        it('handles session with various risk signals', function (): void {
            $sessionWithRisks = MockFactory::createSessionResponseData([
                'risk_signals' => [
                    'device_risk' => true,
                    'proxy' => true,
                    'vpn' => false,
                    'tor' => true,
                    'datacenter' => false,
                ],
            ]);

            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'session' => $sessionWithRisks,
            ]);
            $response = UnauthenticatedSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
        });

        it('handles session with bot detection data', function (): void {
            $sessionWithBot = MockFactory::createSessionResponseData([
                'bot' => [
                    'mouse_num_events' => 0,
                    'click_num_events' => 0,
                    'keyboard_num_events' => 0,
                    'touch_num_events' => 50,
                    'clipboard_num_events' => 0,
                ],
            ]);

            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'session' => $sessionWithBot,
            ]);
            $response = UnauthenticatedSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
        });

        it('handles session from various devices', function (): void {
            $deviceTypes = ['mobile', 'desktop', 'tablet', 'smart_tv'];

            foreach ($deviceTypes as $deviceType) {
                $sessionData = MockFactory::createSessionResponseData([
                    'device' => [
                        'category' => $deviceType,
                        'type' => 'Unknown Device',
                        'os' => 'Unknown OS',
                    ],
                ]);

                $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                    'session' => $sessionData,
                ]);
                $response = UnauthenticatedSessionResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
            }
        });
    });

    describe('data integrity and serialization', function (): void {
        it('maintains data integrity through serialization', function (): void {
            $originalData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'decision' => 'Real',
                'account_score' => 0.3,
                'accounts_linked' => 2,
            ]);
            $response = UnauthenticatedSessionResponse::from($originalData);
            $serializedData = $response->array();
            $recreatedResponse = UnauthenticatedSessionResponse::from($serializedData);

            expect($recreatedResponse)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
            expect($recreatedResponse->array())->toBeArray();
        });

        it('handles complex nested data structures', function (): void {
            $complexData = MockFactory::createUnauthenticatedSessionResponseFromFixture();
            $response = UnauthenticatedSessionResponse::from($complexData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
            expect($response->array())->toBeArray();
        });

        it('serializes to correct structure', function (): void {
            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture();
            $response = UnauthenticatedSessionResponse::from($fixtureData);
            $serialized = $response->array();

            expect($serialized)->toBeArray()
                ->and($serialized)->toHaveKey('project_id')
                ->and($serialized)->toHaveKey('session_id')
                ->and($serialized)->toHaveKey('request_id')
                ->and($serialized)->toHaveKey('decision')
                ->and($serialized)->toHaveKey('account_score')
                ->and($serialized)->toHaveKey('bot')
                ->and($serialized)->toHaveKey('multiple_accounts')
                ->and($serialized)->toHaveKey('risk_signals')
                ->and($serialized)->toHaveKey('accounts_linked')
                ->and($serialized)->toHaveKey('lists')
                ->and($serialized)->toHaveKey('session');
        });
    });

    describe('real-world unauthenticated scenarios', function (): void {
        it('handles anonymous visitor with low risk', function (): void {
            $anonymousVisitorData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'decision' => 'Real',
                'account_score' => 0.2,
                'bot' => 0.05,
                'multiple_accounts' => 0.1,
                'risk_signals' => 0.15,
                'accounts_linked' => 0,
                'lists' => ['anonymous_visitors'],
            ]);
            $response = UnauthenticatedSessionResponse::from($anonymousVisitorData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
        });

        it('handles suspicious anonymous session', function (): void {
            $suspiciousData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'decision' => 'Suspicious',
                'account_score' => 0.85,
                'bot' => 0.9,
                'multiple_accounts' => 0.7,
                'risk_signals' => 0.8,
                'accounts_linked' => 15,
                'lists' => ['high_risk_sessions'],
            ]);
            $response = UnauthenticatedSessionResponse::from($suspiciousData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
        });

        it('handles bot-detected unauthenticated session', function (): void {
            $botDetectedData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'decision' => 'Suspicious',
                'account_score' => 0.95,
                'bot' => 0.98,
                'multiple_accounts' => 0.0,
                'risk_signals' => 0.9,
                'accounts_linked' => 0,
                'lists' => [],
            ]);
            $response = UnauthenticatedSessionResponse::from($botDetectedData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
        });

        it('handles new visitor from clean IP', function (): void {
            $newVisitorData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'decision' => 'Real',
                'account_score' => 0.1,
                'bot' => 0.02,
                'multiple_accounts' => 0.0,
                'risk_signals' => 0.05,
                'accounts_linked' => 0,
                'lists' => ['first_time_visitors'],
            ]);
            $response = UnauthenticatedSessionResponse::from($newVisitorData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
        });

        it('handles session from VPN/proxy', function (): void {
            $vpnSessionData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'decision' => 'Suspicious',
                'account_score' => 0.6,
                'bot' => 0.1,
                'multiple_accounts' => 0.8,
                'risk_signals' => 0.9,
                'accounts_linked' => 5,
                'lists' => ['vpn_users'],
            ]);
            $response = UnauthenticatedSessionResponse::from($vpnSessionData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
        });
    });

    describe('edge cases and error handling', function (): void {
        it('handles extreme score values', function (): void {
            $extremeValues = [0.0, 1.0, 0.999999, 0.000001];

            foreach ($extremeValues as $score) {
                $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                    'account_score' => $score,
                ]);
                $response = UnauthenticatedSessionResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
            }
        });

        it('handles very long identifier strings', function (): void {
            $longId = str_repeat('s', 500);

            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'session_id' => $longId,
            ]);
            $response = UnauthenticatedSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
        });

        it('handles large numbers of linked accounts', function (): void {
            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'accounts_linked' => 10000,
            ]);
            $response = UnauthenticatedSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
        });

        it('handles missing optional session data', function (): void {
            $minimalData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                'session' => [
                    'session_id' => 'minimal_session',
                    'request_id' => 'minimal_request',
                    'project_id' => 'minimal_project',
                ],
            ]);
            $response = UnauthenticatedSessionResponse::from($minimalData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
        });
    });

    describe('readonly class behavior', function (): void {
        it('creates immutable instances', function (): void {
            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture();
            $response = UnauthenticatedSessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);

            // Since it's a readonly class, we can't modify properties
            // This test verifies the object was created successfully
            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('session_id');
        });

        it('provides consistent data access', function (): void {
            $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture();
            $response = UnauthenticatedSessionResponse::from($fixtureData);

            $firstAccess = $response->array();
            $secondAccess = $response->array();
            $thirdAccess = $response->array();

            expect($firstAccess)->toBe($secondAccess)
                ->and($secondAccess)->toBe($thirdAccess);
        });
    });

    describe('performance and memory', function (): void {
        it('creates multiple instances efficiently', function (): void {
            $startTime = microtime(true);
            $responses = [];

            for ($i = 1; $i <= 50; $i++) {
                $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture([
                    'session_id' => "performance_test_session_{$i}",
                ]);
                $responses[] = UnauthenticatedSessionResponse::from($fixtureData);
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            expect(count($responses))->toBe(50)
                ->and($executionTime)->toBeLessThan(1.0); // Allow more time due to complexity

            foreach ($responses as $response) {
                expect($response)->toBeInstanceOf(UnauthenticatedSessionResponse::class);
            }
        });

        it('maintains reasonable memory usage with complex data', function (): void {
            $initialMemory = memory_get_usage();

            $responses = [];
            for ($i = 1; $i <= 100; $i++) {
                $fixtureData = MockFactory::createUnauthenticatedSessionResponseFromFixture();
                $responses[] = UnauthenticatedSessionResponse::from($fixtureData);
            }

            $finalMemory = memory_get_usage();
            $memoryUsed = $finalMemory - $initialMemory;

            expect(count($responses))->toBe(100)
                ->and($memoryUsed)->toBeLessThan(5 * 1024 * 1024); // Less than 5MB due to complexity

            unset($responses);
        });
    });
});
