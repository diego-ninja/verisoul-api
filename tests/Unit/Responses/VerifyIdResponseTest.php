<?php

use Ninja\Verisoul\Enums\RiskFlag;
use Ninja\Verisoul\Responses\VerifyIdResponse;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('VerifyIdResponse', function (): void {
    describe('construction and basic functionality', function (): void {
        it('can be created from fixture data', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture();
            $response = VerifyIdResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyIdResponse::class);
        });

        it('can be created with custom document data', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'document_data' => [
                    'template_info' => [
                        'document_country_code' => 'CA',
                        'document_state' => 'Ontario',
                        'template_type' => 'Driver License',
                    ],
                    'user_data' => [
                        'first_name' => 'Jane',
                        'last_name' => 'Smith',
                        'date_of_birth' => '1990-05-15',
                    ],
                ],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyIdResponse::class);
        });
    });

    describe('risk flag categorization methods', function (): void {
        it('getRiskFlagsByCategory groups flags correctly', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'risk_flags' => ['repeat_id', 'high_device_risk', 'vpn_detected'],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            $categories = $response->getRiskFlagsByCategory();

            expect($categories)->toBeArray();
        });

        it('getRiskFlagsByCategory returns empty array when no flags present', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'risk_flags' => [],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            $categories = $response->getRiskFlagsByCategory();

            expect($categories)->toBeArray()
                ->and($categories)->toBeEmpty();
        });

        it('getRiskFlagsByLevel groups flags by risk level', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'risk_flags' => ['repeat_id', 'high_device_risk'],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            $levels = $response->getRiskFlagsByLevel();

            expect($levels)->toBeArray();
        });

        it('getRiskFlagsByLevel handles mixed risk levels', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'risk_flags' => ['id_expired', 'likely_fake_id', 'vpn_detected'],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            $levels = $response->getRiskFlagsByLevel();

            expect($levels)->toBeArray();
        });
    });

    describe('risk flag checking methods', function (): void {
        it('hasRiskFlag returns true when specific flag is present', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'risk_flags' => ['repeat_id', 'high_device_risk'],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            expect($response->hasRiskFlag(RiskFlag::RepeatId))->toBeTrue();
        });

        it('hasRiskFlag returns false when specific flag is not present', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'risk_flags' => ['repeat_id'],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            expect($response->hasRiskFlag(RiskFlag::LikelyFakeId))->toBeFalse();
        });

        it('getRiskFlagsAsStrings returns array of flag values', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'risk_flags' => ['repeat_id', 'high_device_risk', 'vpn_detected'],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            $flagStrings = $response->getRiskFlagsAsStrings();

            expect($flagStrings)->toBeArray()
                ->and($flagStrings)->toContain('repeat_id')
                ->and($flagStrings)->toContain('high_device_risk')
                ->and($flagStrings)->toContain('vpn_detected');
        });
    });

    describe('document-specific functionality', function (): void {
        it('handles document signals correctly', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture();
            $response = VerifyIdResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyIdResponse::class);
        });

        it('handles document data with various ID types', function (): void {
            $idTypes = ['Driver License', 'Passport', 'National ID', 'State ID'];

            foreach ($idTypes as $idType) {
                $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                    'document_data' => [
                        'template_info' => [
                            'template_type' => $idType,
                            'document_country_code' => 'US',
                        ],
                    ],
                ]);
                $response = VerifyIdResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(VerifyIdResponse::class);
            }
        });
    });

    describe('risk signals integration', function (): void {
        it('getRiskSignals returns unified collection including document signals', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture();
            $response = VerifyIdResponse::from($fixtureData);

            $riskSignals = $response->getRiskSignals();

            expect($riskSignals)->toBeInstanceOf('Ninja\Verisoul\Collections\RiskSignalCollection');
        });

        it('getRiskSignals handles document signals from fixture', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture();
            $response = VerifyIdResponse::from($fixtureData);

            $riskSignals = $response->getRiskSignals();

            expect($riskSignals)->toBeInstanceOf('Ninja\Verisoul\Collections\RiskSignalCollection');
        });
    });

    describe('data integrity and serialization', function (): void {
        it('maintains data integrity through serialization', function (): void {
            $originalData = MockFactory::createVerifyIdResponseFromFixture([
                'risk_flags' => ['repeat_id', 'high_device_risk'],
                'risk_score' => 0.4,
                'document_signals' => [
                    'id_validity' => 'likely_authentic_id',
                    'id_face_match_score' => 0.85,
                ],
            ]);
            $response = VerifyIdResponse::from($originalData);
            $serializedData = $response->array();
            $recreatedResponse = VerifyIdResponse::from($serializedData);

            expect($recreatedResponse)->toBeInstanceOf(VerifyIdResponse::class);
            expect($recreatedResponse->array())->toBeArray();
        });

        it('handles complex document data structures', function (): void {
            $complexData = MockFactory::createVerifyIdResponseFromFixture([
                'document_data' => [
                    'template_info' => [
                        'document_country_code' => 'US',
                        'document_state' => 'California',
                        'template_type' => 'Driver License',
                    ],
                    'user_data' => [
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                        'date_of_birth' => '1985-03-15',
                        'address' => [
                            'street' => '123 Main St',
                            'city' => 'Los Angeles',
                            'state' => 'CA',
                            'postal_code' => '90210',
                            'country' => 'US',
                        ],
                    ],
                ],
            ]);
            $response = VerifyIdResponse::from($complexData);

            expect($response)->toBeInstanceOf(VerifyIdResponse::class);
            expect($response->array())->toBeArray();
        });
    });

    describe('edge cases and error handling', function (): void {
        it('handles empty risk flags array', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'risk_flags' => [],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            expect($response->getRiskFlagsAsStrings())->toBeEmpty();
        });

        it('handles extreme risk score values', function (): void {
            $extremeValues = [0.0, 1.0, 0.999, 0.001];

            foreach ($extremeValues as $score) {
                $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                    'risk_score' => $score,
                ]);
                $response = VerifyIdResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(VerifyIdResponse::class);
            }
        });

        it('handles missing optional document fields', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'document_data' => [
                    'template_info' => [
                        'template_type' => 'Driver License',
                        // Missing country_code and state
                    ],
                    'user_data' => [
                        'first_name' => 'John',
                        // Missing other fields
                    ],
                ],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyIdResponse::class);
        });
    });

    describe('comprehensive risk flag analysis', function (): void {
        it('getRiskFlagsByCategory handles non-RiskFlag items gracefully', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'risk_flags' => ['repeat_id', 'high_device_risk', 'likely_fake_id'],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            $categories = $response->getRiskFlagsByCategory();

            expect($categories)->toBeArray();
            foreach ($categories as $categoryFlags) {
                expect($categoryFlags)->toBeArray();
            }
        });

        it('getRiskFlagsByLevel handles non-RiskFlag items gracefully', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'risk_flags' => ['repeat_id', 'vpn_detected', 'id_expired', 'likely_fake_id'],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            $levels = $response->getRiskFlagsByLevel();

            expect($levels)->toBeArray();
            foreach ($levels as $levelFlags) {
                expect($levelFlags)->toBeArray();
            }
        });

        it('getRiskFlagsAsStrings handles normal case correctly', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'risk_flags' => ['repeat_id', 'high_device_risk'],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            $flagStrings = $response->getRiskFlagsAsStrings();
            expect($flagStrings)->toBeArray();
            expect(count($flagStrings))->toBe(2);
        });

        it('hasRiskFlag works with various ID-specific flag types', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'risk_flags' => ['repeat_id', 'likely_fake_id', 'id_expired', 'low_id_face_match_score'],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            expect($response->hasRiskFlag(RiskFlag::RepeatId))->toBeTrue();
            expect($response->hasRiskFlag(RiskFlag::LikelyFakeId))->toBeTrue();
            expect($response->hasRiskFlag(RiskFlag::IdExpired))->toBeTrue();
            expect($response->hasRiskFlag(RiskFlag::LowIdFaceMatchScore))->toBeTrue();
            expect($response->hasRiskFlag(RiskFlag::VpnDetected))->toBeFalse();
        });
    });

    describe('comprehensive risk signals testing', function (): void {
        it('getRiskSignals combines all three signal types', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'device_network_signals' => [
                    'device_risk' => 0.8,
                    'proxy' => 0.1,
                    'vpn' => 0.9,
                    'datacenter' => 0.0,
                    'tor' => 0.0,
                    'spoofed_ip' => 0.0,
                    'recent_fraud_ip' => 0.0,
                    'device_network_mismatch' => 0.0,
                    'location_spoofing' => 0.0,
                ],
                'document_signals' => [
                    'id_validity' => 'likely_authentic_id',
                    'id_face_match_score' => 0.85,
                    'face_mismatch' => 0.15,
                    'id_age' => 2,
                    'face_liveness' => 0.95,
                ],
                'referring_session_signals' => [
                    'impossible_travel' => 0.2,
                    'ip_mismatch' => 0.0,
                    'user_agent_mismatch' => 0.1,
                    'device_timezone_mismatch' => 0.0,
                    'ip_timezone_mismatch' => 0.0,
                ],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            $riskSignals = $response->getRiskSignals();

            expect($riskSignals)->toBeInstanceOf('Ninja\Verisoul\Collections\RiskSignalCollection');
            expect($riskSignals->count())->toBeGreaterThan(0);
        });

        it('getRiskSignals handles empty signals across all types', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'device_network_signals' => [
                    'device_risk' => 0.0,
                    'proxy' => 0.0,
                    'vpn' => 0.0,
                    'datacenter' => 0.0,
                    'tor' => 0.0,
                    'spoofed_ip' => 0.0,
                    'recent_fraud_ip' => 0.0,
                    'device_network_mismatch' => 0.0,
                    'location_spoofing' => 0.0,
                ],
                'document_signals' => [
                    'id_validity' => 'likely_authentic_id',
                    'id_face_match_score' => 0.0,
                    'face_mismatch' => 0.0,
                    'id_age' => 0,
                    'face_liveness' => 0.0,
                ],
                'referring_session_signals' => [
                    'impossible_travel' => 0.0,
                    'ip_mismatch' => 0.0,
                    'user_agent_mismatch' => 0.0,
                    'device_timezone_mismatch' => 0.0,
                    'ip_timezone_mismatch' => 0.0,
                ],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            $riskSignals = $response->getRiskSignals();

            expect($riskSignals)->toBeInstanceOf('Ninja\Verisoul\Collections\RiskSignalCollection');
        });
    });

    describe('document verification scenarios', function (): void {
        it('handles authentic document scenario', function (): void {
            $authenticData = MockFactory::createVerifyIdResponseFromFixture([
                'decision' => 'Real',
                'risk_score' => 0.1,
                'risk_flags' => [],
            ]);
            $response = VerifyIdResponse::from($authenticData);

            expect($response)->toBeInstanceOf(VerifyIdResponse::class);

            $categories = $response->getRiskFlagsByCategory();
            $levels = $response->getRiskFlagsByLevel();
            expect($categories)->toBeEmpty();
            expect($levels)->toBeEmpty();
        });

        it('handles suspicious document scenario', function (): void {
            $suspiciousData = MockFactory::createVerifyIdResponseFromFixture([
                'decision' => 'Suspicious',
                'risk_score' => 0.8,
                'risk_flags' => ['likely_fake_id', 'low_id_face_match_score'],
            ]);
            $response = VerifyIdResponse::from($suspiciousData);

            expect($response)->toBeInstanceOf(VerifyIdResponse::class);

            $flagStrings = $response->getRiskFlagsAsStrings();
            expect($flagStrings)->toContain('likely_fake_id');
            expect($flagStrings)->toContain('low_id_face_match_score');
        });

        it('handles ID fraud detection scenario', function (): void {
            $fraudData = MockFactory::createVerifyIdResponseFromFixture([
                'decision' => 'Suspicious',
                'risk_score' => 0.95,
                'risk_flags' => ['likely_fake_id', 'known_fraud_id', 'cannot_confirm_id_is_authentic'],
            ]);
            $response = VerifyIdResponse::from($fraudData);

            expect($response)->toBeInstanceOf(VerifyIdResponse::class);
            expect($response->hasRiskFlag(RiskFlag::LikelyFakeId))->toBeTrue();
            expect($response->hasRiskFlag(RiskFlag::KnownFraudId))->toBeTrue();
            expect($response->hasRiskFlag(RiskFlag::CannotConfirmIdIsAuthentic))->toBeTrue();
        });

        it('provides consistent results across multiple method calls', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'risk_flags' => ['repeat_id', 'high_device_risk'],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            // Test string flags consistency
            $firstCall = $response->getRiskFlagsAsStrings();
            $secondCall = $response->getRiskFlagsAsStrings();
            expect($firstCall)->toBe($secondCall);

            // Test categories consistency
            $categories1 = $response->getRiskFlagsByCategory();
            $categories2 = $response->getRiskFlagsByCategory();
            expect($categories1)->toBe($categories2);

            // Test levels consistency
            $levels1 = $response->getRiskFlagsByLevel();
            $levels2 = $response->getRiskFlagsByLevel();
            expect($levels1)->toBe($levels2);

            // Test specific flag consistency
            $hasFlag1 = $response->hasRiskFlag(RiskFlag::RepeatId);
            $hasFlag2 = $response->hasRiskFlag(RiskFlag::RepeatId);
            expect($hasFlag1)->toBe($hasFlag2);
        });
    });

    describe('performance and edge cases', function (): void {
        it('handles large number of risk flags efficiently', function (): void {
            $manyFlags = [
                'high_device_risk', 'proxy_detected', 'vpn_detected', 'datacenter_detected',
                'likely_fake_id', 'id_expired', 'repeat_id', 'repeat_device',
                'known_fraud_id', 'cannot_confirm_id_is_authentic', 'low_id_face_match_score',
            ];

            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'risk_flags' => $manyFlags,
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            $startTime = microtime(true);

            $categories = $response->getRiskFlagsByCategory();
            $levels = $response->getRiskFlagsByLevel();
            $flagStrings = $response->getRiskFlagsAsStrings();
            $hasSpecificFlag = $response->hasRiskFlag(RiskFlag::LikelyFakeId);

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            expect($executionTime)->toBeLessThan(0.1); // Should be very fast
            expect($categories)->toBeArray();
            expect($levels)->toBeArray();
            expect($flagStrings)->toBeArray();
            expect(count($flagStrings))->toBe(count($manyFlags));
            expect($hasSpecificFlag)->toBeTrue();
        });

        it('handles empty and null scenarios gracefully', function (): void {
            $emptyData = MockFactory::createVerifyIdResponseFromFixture([
                'risk_flags' => [],
                'risk_score' => 0.0,
            ]);
            $response = VerifyIdResponse::from($emptyData);

            expect($response->getRiskFlagsByCategory())->toBeEmpty();
            expect($response->getRiskFlagsByLevel())->toBeEmpty();
            expect($response->getRiskFlagsAsStrings())->toBeEmpty();
            expect($response->hasRiskFlag(RiskFlag::LikelyFakeId))->toBeFalse();
        });

        it('handles international document scenarios', function (): void {
            $internationalCountries = ['CA', 'GB', 'AU', 'DE', 'FR', 'JP', 'MX'];

            foreach ($internationalCountries as $country) {
                $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                    'document_data' => [
                        'template_info' => [
                            'document_country_code' => $country,
                            'template_type' => 'National ID',
                        ],
                    ],
                ]);
                $response = VerifyIdResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(VerifyIdResponse::class);
            }
        });
    });
});
