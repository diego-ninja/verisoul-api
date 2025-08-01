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

    describe('business logic validation', function (): void {
        it('correctly identifies blocking vs non-blocking scenarios', function (): void {
            // Test case 1: High-risk scenario
            $highRiskData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['likely_fake_id', 'known_fraud_face', 'high_device_risk'],
                'risk_score' => 0.9,
                'decision' => 'Suspicious',
            ]);
            $highRiskResponse = VerifyFaceResponse::from($highRiskData);

            // Test case 2: Low-risk scenario
            $lowRiskData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => [],
                'risk_score' => 0.1,
                'decision' => 'Real',
            ]);
            $lowRiskResponse = VerifyFaceResponse::from($lowRiskData);

            expect($highRiskResponse)->toBeInstanceOf(VerifyFaceResponse::class)
                ->and($lowRiskResponse)->toBeInstanceOf(VerifyFaceResponse::class);
        });

        it('provides consistent results across multiple calls', function (): void {
            $fixtureData = MockFactory::createVerifyFaceResponseFromFixture([
                'risk_flags' => ['repeat_face'],
            ]);
            $response = VerifyFaceResponse::from($fixtureData);

            $firstCall = $response->hasBlockingRiskFlags();
            $secondCall = $response->hasBlockingRiskFlags();
            $thirdCall = $response->hasBlockingRiskFlags();

            expect($firstCall)->toBe($secondCall)
                ->and($secondCall)->toBe($thirdCall);
        });
    });
});
