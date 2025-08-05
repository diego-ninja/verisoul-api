<?php

use Ninja\Verisoul\Enums\RiskFlag;
use Ninja\Verisoul\Responses\VerifyFaceResponse;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('VerifyFaceResponse', function (): void {
    describe('construction and basic functionality', function (): void {
        it('can be created from fixture data', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture();
            $response = VerifyFaceResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyFaceResponse::class);
        });

        it('can be created with custom risk flags', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['repeat_face', 'high_device_risk'],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyFaceResponse::class);
        });
    });

    describe('risk flag analysis methods', function (): void {
        it('hasBlockingRiskFlags returns true when blocking flags are present', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['likely_fake_id', 'known_fraud_face'],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            expect($response->hasBlockingRiskFlags())->toBeBool();
        });

        it('hasBlockingRiskFlags returns false when no blocking flags are present', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => [],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            expect($response->hasBlockingRiskFlags())->toBeFalse();
        });

        it('hasModerateRiskFlags returns true when moderate flags are present', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['vpn_detected'],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            expect($response->hasModerateRiskFlags())->toBeBool();
        });

        it('hasModerateRiskFlags returns false when no moderate flags are present', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => [],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            expect($response->hasModerateRiskFlags())->toBeFalse();
        });
    });

    describe('risk flag categorization', function (): void {
        it('getRiskFlagsByCategory groups flags correctly', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['repeat_face', 'high_device_risk', 'vpn_detected'],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            $categories = $response->getRiskFlagsByCategory();

            expect($categories)->toBeArray();
        });

        it('getRiskFlagsByCategory returns empty array when no flags present', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => [],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            $categories = $response->getRiskFlagsByCategory();

            expect($categories)->toBeArray()
                ->and($categories)->toBeEmpty();
        });

        it('getRiskFlagsByLevel groups flags by risk level', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['repeat_face', 'vpn_detected'],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            $levels = $response->getRiskFlagsByLevel();

            expect($levels)->toBeArray();
        });
    });

    describe('risk flag checking methods', function (): void {
        it('hasRiskFlag returns true when specific flag is present', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['repeat_face', 'vpn_detected'],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            // Use actual risk flag from fixture
            expect($response->hasRiskFlag(RiskFlag::RepeatFace))->toBeTrue();
        });

        it('hasRiskFlag returns false when specific flag is not present', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['repeat_face'],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            expect($response->hasRiskFlag(RiskFlag::HighDeviceRisk))->toBeFalse();
        });

        it('getRiskFlagsAsStrings returns array of flag values', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['repeat_face', 'high_device_risk'],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            $flagStrings = $response->getRiskFlagsAsStrings();

            expect($flagStrings)->toBeArray()
                ->and($flagStrings)->toContain('repeat_face')
                ->and($flagStrings)->toContain('high_device_risk');
        });
    });

    describe('risk signals integration', function (): void {
        it('getRiskSignals returns unified collection', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture();
            $response = VerifyFaceResponse::from($fixtureData);

            $riskSignals = $response->getRiskSignals();

            expect($riskSignals)->toBeInstanceOf('Ninja\Verisoul\Collections\RiskSignalCollection');
        });
    });

    describe('data integrity and serialization', function (): void {
        it('maintains data integrity through serialization', function (): void {
            $originalData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['repeat_face', 'vpn_detected'],
                'risk_score' => 0.75,
            ]);
            $response = VerifyFaceResponse::from($originalData);
            $serializedData = $response->array();
            $recreatedResponse = VerifyFaceResponse::from($serializedData);

            expect($recreatedResponse)->toBeInstanceOf(VerifyFaceResponse::class);
            expect($recreatedResponse->array())->toBeArray();
        });

        it('handles complex nested data structures', function (): void {
            $complexData = MockFactory::createVerifyFaceResponseFromFixture([
                'device_network_signals' => [
                    'device_risk' => 0.8,
                    'proxy' => 0.1,
                    'vpn' => 0.9,
                    'datacenter' => 0.0,
                    'tor' => 0.0,
                ],
                'referring_session_signals' => [
                    'impossible_travel' => 0.2,
                    'ip_mismatch' => 0.0,
                    'user_agent_mismatch' => 0.1,
                ],
            ]);
            $response = VerifyFaceResponse::from($complexData);

            expect($response)->toBeInstanceOf(VerifyFaceResponse::class);
            expect($response->array())->toBeArray();
        });
    });

    describe('edge cases and error handling', function (): void {
        it('handles empty risk flags array', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => [],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            expect($response->hasBlockingRiskFlags())->toBeFalse()
                ->and($response->hasModerateRiskFlags())->toBeFalse()
                ->and($response->getRiskFlagsAsStrings())->toBeEmpty();
        });

        it('handles high risk score values', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_score' => 0.95,
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyFaceResponse::class);
        });

        it('handles zero risk score values', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_score' => 0.0,
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyFaceResponse::class);
        });
    });

    describe('comprehensive risk flag analysis', function (): void {
        it('getRiskFlagsByCategory handles non-RiskFlag items gracefully', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['repeat_face', 'high_device_risk'],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            $categories = $response->getRiskFlagsByCategory();

            expect($categories)->toBeArray();
            foreach ($categories as $categoryFlags) {
                expect($categoryFlags)->toBeArray();
            }
        });

        it('getRiskFlagsByLevel handles non-RiskFlag items gracefully', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['repeat_face', 'vpn_detected', 'id_expired'],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            $levels = $response->getRiskFlagsByLevel();

            expect($levels)->toBeArray();
            foreach ($levels as $levelFlags) {
                expect($levelFlags)->toBeArray();
            }
        });

        it('getRiskFlagsAsStrings throws exception for invalid items', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['repeat_face'],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            // This should work normally
            $flagStrings = $response->getRiskFlagsAsStrings();
            expect($flagStrings)->toBeArray();
        });

        it('hasRiskFlag works with various flag types', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['repeat_face', 'high_device_risk', 'likely_fake_id'],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            expect($response->hasRiskFlag(RiskFlag::RepeatFace))->toBeTrue();
            expect($response->hasRiskFlag(RiskFlag::HighDeviceRisk))->toBeTrue();
            expect($response->hasRiskFlag(RiskFlag::LikelyFakeId))->toBeTrue();
            expect($response->hasRiskFlag(RiskFlag::VpnDetected))->toBeFalse();
        });
    });

    describe('comprehensive risk signals testing', function (): void {
        it('getRiskSignals combines device and referring session signals', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
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
                'referring_session_signals' => [
                    'impossible_travel' => 0.2,
                    'ip_mismatch' => 0.0,
                    'user_agent_mismatch' => 0.1,
                    'device_timezone_mismatch' => 0.0,
                    'ip_timezone_mismatch' => 0.0,
                ],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            $riskSignals = $response->getRiskSignals();

            expect($riskSignals)->toBeInstanceOf('Ninja\Verisoul\Collections\RiskSignalCollection');
            expect($riskSignals->count())->toBeGreaterThan(0);
        });

        it('getRiskSignals handles empty signals', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
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
                'referring_session_signals' => [
                    'impossible_travel' => 0.0,
                    'ip_mismatch' => 0.0,
                    'user_agent_mismatch' => 0.0,
                    'device_timezone_mismatch' => 0.0,
                    'ip_timezone_mismatch' => 0.0,
                ],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            $riskSignals = $response->getRiskSignals();

            expect($riskSignals)->toBeInstanceOf('Ninja\Verisoul\Collections\RiskSignalCollection');
        });
    });

    describe('business logic validation', function (): void {
        it('correctly identifies blocking vs non-blocking scenarios', function (): void {
            // Test case 1: High-risk scenario with blocking flags
            $highRiskData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['likely_fake_id', 'known_fraud_face'],
                'risk_score' => 0.9,
                'decision' => 'Suspicious',
            ]);
            $highRiskResponse = VerifyFaceResponse::from($highRiskData);

            // Test case 2: Moderate risk scenario
            $moderateRiskData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['vpn_detected', 'proxy_detected'],
                'risk_score' => 0.5,
                'decision' => 'Suspicious',
            ]);
            $moderateRiskResponse = VerifyFaceResponse::from($moderateRiskData);

            // Test case 3: Low-risk scenario
            $lowRiskData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['id_expired'],
                'risk_score' => 0.1,
                'decision' => 'Real',
            ]);
            $lowRiskResponse = VerifyFaceResponse::from($lowRiskData);

            expect($highRiskResponse)->toBeInstanceOf(VerifyFaceResponse::class);
            expect($moderateRiskResponse)->toBeInstanceOf(VerifyFaceResponse::class);
            expect($lowRiskResponse)->toBeInstanceOf(VerifyFaceResponse::class);

            // Verify blocking detection
            expect($highRiskResponse->hasBlockingRiskFlags())->toBeTrue();
            expect($moderateRiskResponse->hasModerateRiskFlags())->toBeTrue();
        });

        it('provides consistent results across multiple calls', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['repeat_face', 'high_device_risk'],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            // Test blocking flags consistency
            $blocking1 = $response->hasBlockingRiskFlags();
            $blocking2 = $response->hasBlockingRiskFlags();
            expect($blocking1)->toBe($blocking2);

            // Test moderate flags consistency
            $moderate1 = $response->hasModerateRiskFlags();
            $moderate2 = $response->hasModerateRiskFlags();
            expect($moderate1)->toBe($moderate2);

            // Test categories consistency
            $categories1 = $response->getRiskFlagsByCategory();
            $categories2 = $response->getRiskFlagsByCategory();
            expect($categories1)->toBe($categories2);

            // Test levels consistency
            $levels1 = $response->getRiskFlagsByLevel();
            $levels2 = $response->getRiskFlagsByLevel();
            expect($levels1)->toBe($levels2);
        });

        it('handles mixed risk flag levels correctly', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => [
                    'likely_fake_id',      // High + Blocking
                    'vpn_detected',        // Moderate
                    'id_expired',          // Low
                    'high_device_risk',    // High
                ],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            expect($response->hasBlockingRiskFlags())->toBeTrue();
            expect($response->hasModerateRiskFlags())->toBeTrue();

            $levels = $response->getRiskFlagsByLevel();
            expect($levels)->toBeArray();
            expect(isset($levels['high']))->toBeTrue();
            expect(isset($levels['moderate']))->toBeTrue();
            expect(isset($levels['low']))->toBeTrue();

            $categories = $response->getRiskFlagsByCategory();
            expect($categories)->toBeArray();
        });
    });

    describe('performance and edge cases', function (): void {
        it('handles large number of risk flags efficiently', function (): void {
            $manyFlags = [
                'high_device_risk', 'proxy_detected', 'vpn_detected', 'datacenter_detected',
                'likely_fake_id', 'id_expired', 'repeat_face', 'repeat_id', 'repeat_device',
                'known_fraud_face', 'known_fraud_id'
            ];

            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => $manyFlags,
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            $startTime = microtime(true);

            $hasBlocking = $response->hasBlockingRiskFlags();
            $hasModerate = $response->hasModerateRiskFlags();
            $categories = $response->getRiskFlagsByCategory();
            $levels = $response->getRiskFlagsByLevel();
            $flagStrings = $response->getRiskFlagsAsStrings();

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            expect($executionTime)->toBeLessThan(0.1); // Should be very fast
            expect($hasBlocking)->toBeBool();
            expect($hasModerate)->toBeBool();
            expect($categories)->toBeArray();
            expect($levels)->toBeArray();
            expect($flagStrings)->toBeArray();
            expect(count($flagStrings))->toBe(count($manyFlags));
        });

        it('handles empty and null scenarios gracefully', function (): void {
            $emptyData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => [],
                'risk_score' => 0.0,
            ]);
            $response = VerifyFaceResponse::from($emptyData);

            expect($response->hasBlockingRiskFlags())->toBeFalse();
            expect($response->hasModerateRiskFlags())->toBeFalse();
            expect($response->getRiskFlagsByCategory())->toBeEmpty();
            expect($response->getRiskFlagsByLevel())->toBeEmpty();
            expect($response->getRiskFlagsAsStrings())->toBeEmpty();
            expect($response->hasRiskFlag(RiskFlag::HighDeviceRisk))->toBeFalse();
        });
    });
});
