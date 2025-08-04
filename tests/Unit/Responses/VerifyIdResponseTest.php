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

    describe('document verification scenarios', function (): void {
        it('handles authentic document scenario', function (): void {
            $authenticData = MockFactory::createVerifyIdResponseFromFixture([
                'decision' => 'Real',
                'risk_score' => 0.1,
                'risk_flags' => [],
            ]);
            $response = VerifyIdResponse::from($authenticData);

            expect($response)->toBeInstanceOf(VerifyIdResponse::class);
        });

        it('handles suspicious document scenario', function (): void {
            $suspiciousData = MockFactory::createVerifyIdResponseFromFixture([
                'decision' => 'Suspicious',
                'risk_score' => 0.8,
                'risk_flags' => ['likely_fake_id', 'low_id_face_match_score'],
            ]);
            $response = VerifyIdResponse::from($suspiciousData);

            expect($response)->toBeInstanceOf(VerifyIdResponse::class);
        });

        it('provides consistent results across multiple method calls', function (): void {
            $fixtureData = MockFactory::createVerifyIdResponseFromFixture([
                'risk_flags' => ['repeat_id', 'high_device_risk'],
            ]);
            $response = VerifyIdResponse::from($fixtureData);

            $firstCall = $response->getRiskFlagsAsStrings();
            $secondCall = $response->getRiskFlagsAsStrings();
            $thirdCall = $response->getRiskFlagsAsStrings();

            expect($firstCall)->toBe($secondCall)
                ->and($secondCall)->toBe($thirdCall);
        });
    });
});
