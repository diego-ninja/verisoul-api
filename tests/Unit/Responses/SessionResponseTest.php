<?php

use Ninja\Verisoul\Responses\SessionResponse;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('SessionResponse', function (): void {
    describe('construction and basic functionality', function (): void {
        it('can be created from fixture data', function (): void {
            $fixtureData = MockFactory::createSessionResponseFromFixture();
            $response = SessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });

        it('can be created with custom session data', function (): void {
            $fixtureData = MockFactory::createSessionResponseFromFixture([
                'session_id' => 'custom_session_123',
                'account_ids' => ['acc_123', 'acc_456'],
                'true_country_code' => 'CA',
            ]);
            $response = SessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });
    });

    describe('session data access', function (): void {
        it('provides access to session identification data', function (): void {
            $fixtureData = MockFactory::createSessionResponseFromFixture([
                'session_id' => 'test_session_789',
                'request_id' => 'test_request_456',
                'project_id' => 'test_project_123',
            ]);
            $response = SessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(SessionResponse::class);

            $responseArray = $response->array();
            expect($responseArray)->toBeArray()
                ->and($responseArray)->toHaveKey('session_id')
                ->and($responseArray)->toHaveKey('request_id')
                ->and($responseArray)->toHaveKey('project_id');
        });

        it('provides access to account associations', function (): void {
            $accountIds = ['acc_123', 'acc_456', 'acc_789'];
            $fixtureData = MockFactory::createSessionResponseFromFixture([
                'account_ids' => $accountIds,
            ]);
            $response = SessionResponse::from($fixtureData);

            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('account_ids')
                ->and($responseArray['account_ids'])->toBe($accountIds);
        });
    });

    describe('network and location data', function (): void {
        it('handles network information correctly', function (): void {
            $networkData = [
                'ip_address' => '192.168.1.100',
                'service_provider' => 'Test ISP',
                'connection_type' => 'residential',
            ];
            $fixtureData = MockFactory::createSessionResponseFromFixture([
                'network' => $networkData,
            ]);
            $response = SessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });

        it('handles location information correctly', function (): void {
            $locationData = [
                'continent' => 'NA',
                'country_code' => 'US',
                'state' => 'California',
                'city' => 'San Francisco',
                'latitude' => 37.7749,
                'longitude' => -122.4194,
            ];
            $fixtureData = MockFactory::createSessionResponseFromFixture([
                'location' => $locationData,
            ]);
            $response = SessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });

        it('handles various country codes', function (): void {
            $countryCodes = ['US', 'CA', 'GB', 'DE', 'FR', 'JP', 'AU'];

            foreach ($countryCodes as $countryCode) {
                $fixtureData = MockFactory::createSessionResponseFromFixture([
                    'true_country_code' => $countryCode,
                    'location' => [
                        'country_code' => $countryCode,
                        'continent' => 'XX',
                    ],
                ]);
                $response = SessionResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(SessionResponse::class);
            }
        });
    });

    describe('device and browser data', function (): void {
        it('handles browser information correctly', function (): void {
            $browserData = [
                'type' => 'Chrome',
                'version' => '120.0.0.0',
                'language' => 'en-US',
                'user_agent' => 'Mozilla/5.0 (compatible test)',
                'timezone' => 'America/New_York',
            ];
            $fixtureData = MockFactory::createSessionResponseFromFixture([
                'browser' => $browserData,
            ]);
            $response = SessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });

        it('handles device information correctly', function (): void {
            $deviceData = [
                'category' => 'mobile',
                'type' => 'iPhone',
                'os' => 'iOS 17.0',
                'cpu_cores' => 6,
                'memory' => 8,
                'screen_height' => 2556,
                'screen_width' => 1179,
            ];
            $fixtureData = MockFactory::createSessionResponseFromFixture([
                'device' => $deviceData,
            ]);
            $response = SessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });

        it('handles various device categories', function (): void {
            $deviceCategories = ['desktop', 'mobile', 'tablet', 'smart_tv', 'wearable'];

            foreach ($deviceCategories as $category) {
                $fixtureData = MockFactory::createSessionResponseFromFixture([
                    'device' => [
                        'category' => $category,
                        'type' => 'Test Device',
                        'os' => 'Test OS 1.0',
                    ],
                ]);
                $response = SessionResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(SessionResponse::class);
            }
        });
    });

    describe('risk signals and bot detection', function (): void {
        it('handles risk signals correctly', function (): void {
            $riskSignalsData = [
                'device_risk' => true,
                'proxy' => false,
                'vpn' => true,
                'tor' => false,
                'datacenter' => false,
                'recent_fraud_ip' => false,
            ];
            $fixtureData = MockFactory::createSessionResponseFromFixture([
                'risk_signals' => $riskSignalsData,
            ]);
            $response = SessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });

        it('handles bot detection data correctly', function (): void {
            $botData = [
                'mouse_num_events' => 150,
                'click_num_events' => 25,
                'keyboard_num_events' => 80,
                'touch_num_events' => 0,
                'clipboard_num_events' => 2,
            ];
            $fixtureData = MockFactory::createSessionResponseFromFixture([
                'bot' => $botData,
            ]);
            $response = SessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });

        it('handles zero interaction bot scenario', function (): void {
            $botData = [
                'mouse_num_events' => 0,
                'click_num_events' => 0,
                'keyboard_num_events' => 0,
                'touch_num_events' => 0,
                'clipboard_num_events' => 0,
            ];
            $fixtureData = MockFactory::createSessionResponseFromFixture([
                'bot' => $botData,
            ]);
            $response = SessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });
    });

    describe('data integrity and serialization', function (): void {
        it('maintains data integrity through serialization', function (): void {
            $originalData = MockFactory::createSessionResponseFromFixture([
                'session_id' => 'integrity_test_session',
                'account_ids' => ['acc_test_1', 'acc_test_2'],
                'start_time' => '2024-01-15T10:30:00Z',
            ]);
            $response = SessionResponse::from($originalData);
            $serializedData = $response->array();
            $recreatedResponse = SessionResponse::from($serializedData);

            expect($recreatedResponse)->toBeInstanceOf(SessionResponse::class);
            expect($recreatedResponse->array())->toBeArray();
        });

        it('handles complex nested structures correctly', function (): void {
            $complexData = MockFactory::createSessionResponseFromFixture([
                'network' => [
                    'ip_address' => '203.0.113.1',
                    'service_provider' => 'Complex ISP Corp',
                    'connection_type' => 'fiber',
                    'additional_info' => [
                        'bandwidth' => '1000mbps',
                        'latency' => '5ms',
                    ],
                ],
                'device' => [
                    'category' => 'desktop',
                    'type' => 'Custom Build',
                    'os' => 'Linux Ubuntu 22.04',
                    'specifications' => [
                        'cpu' => 'AMD Ryzen 9 5900X',
                        'gpu' => 'NVIDIA RTX 4080',
                        'ram' => '32GB DDR4',
                    ],
                ],
            ]);
            $response = SessionResponse::from($complexData);

            expect($response)->toBeInstanceOf(SessionResponse::class);
            expect($response->array())->toBeArray();
        });
    });

    describe('timestamp handling', function (): void {
        it('handles various timestamp formats', function (): void {
            $timestampFormats = [
                '2024-01-15T10:30:00Z',
                '2024-01-15T10:30:00.123Z',
                '2024-01-15T10:30:00+00:00',
                '2024-01-15T10:30:00-08:00',
            ];

            foreach ($timestampFormats as $timestamp) {
                $fixtureData = MockFactory::createSessionResponseFromFixture([
                    'start_time' => $timestamp,
                ]);
                $response = SessionResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(SessionResponse::class);
            }
        });

        it('handles timezone-aware timestamps', function (): void {
            $timezones = [
                'America/New_York',
                'Europe/London',
                'Asia/Tokyo',
                'Australia/Sydney',
                'UTC',
            ];

            foreach ($timezones as $timezone) {
                $fixtureData = MockFactory::createSessionResponseFromFixture([
                    'browser' => [
                        'timezone' => $timezone,
                        'type' => 'Chrome',
                        'version' => '120.0.0.0',
                    ],
                ]);
                $response = SessionResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(SessionResponse::class);
            }
        });
    });

    describe('edge cases and error handling', function (): void {
        it('handles empty account IDs array', function (): void {
            $fixtureData = MockFactory::createSessionResponseFromFixture([
                'account_ids' => [],
            ]);
            $response = SessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });

        it('handles single account ID', function (): void {
            $fixtureData = MockFactory::createSessionResponseFromFixture([
                'account_ids' => ['single_account_123'],
            ]);
            $response = SessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });

        it('handles multiple account IDs', function (): void {
            $accountIds = array_map(fn($i) => "acc_multi_{$i}", range(1, 10));
            $fixtureData = MockFactory::createSessionResponseFromFixture([
                'account_ids' => $accountIds,
            ]);
            $response = SessionResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });

        it('handles missing optional fields gracefully', function (): void {
            $minimalData = MockFactory::createSessionResponseFromFixture([
                'session_id' => 'minimal_session',
                'request_id' => 'minimal_request',
                'project_id' => 'minimal_project',
                'account_ids' => ['minimal_account'],
            ]);
            $response = SessionResponse::from($minimalData);

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });
    });
});
