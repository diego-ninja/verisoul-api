<?php

use Ninja\Verisoul\Enums\RiskCategory;
use Ninja\Verisoul\Enums\RiskFlag;

describe('RiskCategory Enum', function (): void {
    describe('enum cases', function (): void {
        it('has all expected cases', function (): void {
            $cases = RiskCategory::cases();
            $values = array_map(fn($case) => $case->value, $cases);

            expect($cases)->toHaveCount(8)
                ->and($values)->toContain('device')
                ->and($values)->toContain('network')
                ->and($values)->toContain('impersonation')
                ->and($values)->toContain('multi_accounting')
                ->and($values)->toContain('id_selling')
                ->and($values)->toContain('id_fraud')
                ->and($values)->toContain('fraud_farm')
                ->and($values)->toContain('coppa');
        });

        it('has correct enum values', function (): void {
            expect(RiskCategory::Device->value)->toBe('device')
                ->and(RiskCategory::Network->value)->toBe('network')
                ->and(RiskCategory::Impersonation->value)->toBe('impersonation')
                ->and(RiskCategory::MultiAccounting->value)->toBe('multi_accounting')
                ->and(RiskCategory::IDSelling->value)->toBe('id_selling')
                ->and(RiskCategory::IDFraud->value)->toBe('id_fraud')
                ->and(RiskCategory::FraudFarm->value)->toBe('fraud_farm')
                ->and(RiskCategory::COPPA->value)->toBe('coppa');
        });

        it('can be created from string values', function (): void {
            expect(RiskCategory::from('device'))->toBe(RiskCategory::Device)
                ->and(RiskCategory::from('network'))->toBe(RiskCategory::Network)
                ->and(RiskCategory::from('impersonation'))->toBe(RiskCategory::Impersonation)
                ->and(RiskCategory::from('multi_accounting'))->toBe(RiskCategory::MultiAccounting)
                ->and(RiskCategory::from('id_selling'))->toBe(RiskCategory::IDSelling)
                ->and(RiskCategory::from('id_fraud'))->toBe(RiskCategory::IDFraud)
                ->and(RiskCategory::from('fraud_farm'))->toBe(RiskCategory::FraudFarm)
                ->and(RiskCategory::from('coppa'))->toBe(RiskCategory::COPPA);
        });

        it('can try to create from string values', function (): void {
            expect(RiskCategory::tryFrom('device'))->toBe(RiskCategory::Device)
                ->and(RiskCategory::tryFrom('invalid_category'))->toBeNull();
        });
    });

    describe('getFlags method', function (): void {
        it('returns correct flags for Device category', function (): void {
            $flags = RiskCategory::Device->getFlags();

            expect($flags)->toBeArray()
                ->and(count($flags))->toBeGreaterThan(0);
        });

        it('returns correct flags for Network category', function (): void {
            $flags = RiskCategory::Network->getFlags();

            expect($flags)->toBeArray()
                ->and(count($flags))->toBeGreaterThan(0);
        });

        it('returns flags for all categories', function (): void {
            foreach (RiskCategory::cases() as $category) {
                $flags = $category->getFlags();

                expect($flags)->toBeArray();

                foreach ($flags as $flag) {
                    expect($flag)->toBeInstanceOf(RiskFlag::class);
                }
            }
        });

        it('returns arrays of RiskFlag instances', function (): void {
            foreach (RiskCategory::cases() as $category) {
                $flags = $category->getFlags();

                expect($flags)->toBeArray();

                foreach ($flags as $flag) {
                    expect($flag)->toBeInstanceOf(RiskFlag::class);
                }
            }
        });
    });

    describe('category relationships', function (): void {
        it('shows that some flags appear in multiple categories', function (): void {
            // ImpossibleTravelDetected appears in both Impersonation and IDSelling
            $impersonationFlags = RiskCategory::Impersonation->getFlags();
            $idSellingFlags = RiskCategory::IDSelling->getFlags();

            expect($impersonationFlags)->toContain(RiskFlag::ImpossibleTravelDetected)
                ->and($idSellingFlags)->toContain(RiskFlag::ImpossibleTravelDetected);
        });

        it('shows that LocationSpoofing appears in multiple categories', function (): void {
            $impersonationFlags = RiskCategory::Impersonation->getFlags();
            $idSellingFlags = RiskCategory::IDSelling->getFlags();

            expect($impersonationFlags)->toContain(RiskFlag::LocationSpoofing)
                ->and($idSellingFlags)->toContain(RiskFlag::LocationSpoofing);
        });

        it('shows that IdAgeBelow16 appears in multiple categories', function (): void {
            $impersonationFlags = RiskCategory::Impersonation->getFlags();
            $coppaFlags = RiskCategory::COPPA->getFlags();

            expect($impersonationFlags)->toContain(RiskFlag::IdAgeBelow16)
                ->and($coppaFlags)->toContain(RiskFlag::IdAgeBelow16);
        });

        it('shows that referring flags appear in both Impersonation and IDSelling', function (): void {
            $impersonationFlags = RiskCategory::Impersonation->getFlags();
            $idSellingFlags = RiskCategory::IDSelling->getFlags();

            $referringFlags = [
                RiskFlag::ReferringIpMismatch,
                RiskFlag::ReferringUserAgentMismatch,
                RiskFlag::ReferringDeviceTimezoneMismatch,
                RiskFlag::ReferringIpTimezoneMismatch,
            ];

            foreach ($referringFlags as $flag) {
                expect($impersonationFlags)->toContain($flag)
                    ->and($idSellingFlags)->toContain($flag);
            }
        });

        it('shows that some ID-related flags appear in both Impersonation and IDFraud', function (): void {
            $impersonationFlags = RiskCategory::Impersonation->getFlags();
            $idFraudFlags = RiskCategory::IDFraud->getFlags();

            $sharedFlags = [
                RiskFlag::CannotConfirmIdIsAuthentic,
                RiskFlag::LikelyFakeId,
                RiskFlag::IdExpired,
            ];

            foreach ($sharedFlags as $flag) {
                expect($impersonationFlags)->toContain($flag)
                    ->and($idFraudFlags)->toContain($flag);
            }
        });
    });

    describe('category completeness', function (): void {
        it('all categories return flags', function (): void {
            foreach (RiskCategory::cases() as $category) {
                $flags = $category->getFlags();
                expect($flags)->toBeArray();
            }
        });

        it('verifies flag assignments exist', function (): void {
            $totalFlags = 0;

            foreach (RiskCategory::cases() as $category) {
                $flags = $category->getFlags();
                $totalFlags += count($flags);
            }

            expect($totalFlags)->toBeGreaterThan(0);
        });
    });

    describe('enum behavior', function (): void {
        it('supports comparison operations', function (): void {
            expect(RiskCategory::Device === RiskCategory::Device)->toBeTrue()
                ->and(RiskCategory::Device === RiskCategory::Network)->toBeFalse()
                ->and(RiskCategory::Device !== RiskCategory::Network)->toBeTrue();
        });

        it('can be used in match expressions', function (): void {
            $category = RiskCategory::Network;

            $description = match ($category) {
                RiskCategory::Device => 'Device-related risks',
                RiskCategory::Network => 'Network-related risks',
                RiskCategory::Impersonation => 'Impersonation risks',
                RiskCategory::MultiAccounting => 'Multi-accounting risks',
                RiskCategory::IDSelling => 'ID selling risks',
                RiskCategory::IDFraud => 'ID fraud risks',
                RiskCategory::FraudFarm => 'Fraud farm risks',
                RiskCategory::COPPA => 'COPPA compliance risks',
            };

            expect($description)->toBe('Network-related risks');
        });

        it('can be used in arrays', function (): void {
            $highRiskCategories = [
                RiskCategory::FraudFarm,
                RiskCategory::IDFraud,
                RiskCategory::MultiAccounting,
            ];

            expect($highRiskCategories)->toHaveCount(3)
                ->and(in_array(RiskCategory::FraudFarm, $highRiskCategories))->toBeTrue()
                ->and(in_array(RiskCategory::Network, $highRiskCategories))->toBeFalse();
        });

        it('supports serialization', function (): void {
            $category = RiskCategory::Impersonation;
            $serialized = serialize($category);
            $unserialized = unserialize($serialized);

            expect($unserialized)->toBe(RiskCategory::Impersonation)
                ->and($unserialized->value)->toBe('impersonation');
        });
    });

    describe('validation and error handling', function (): void {
        it('throws exception for invalid string values', function (): void {
            expect(fn() => RiskCategory::from('invalid_category'))
                ->toThrow(ValueError::class);
        });

        it('handles case sensitivity correctly', function (): void {
            expect(RiskCategory::tryFrom('Device'))->toBeNull()
                ->and(RiskCategory::tryFrom('DEVICE'))->toBeNull()
                ->and(RiskCategory::tryFrom('device'))->toBe(RiskCategory::Device);
        });
    });

    describe('business logic integration', function (): void {
        it('supports risk categorization workflow', function (): void {
            $testCategories = [
                RiskCategory::Device,
                RiskCategory::Network,
                RiskCategory::FraudFarm,
            ];

            $assessments = [];
            foreach ($testCategories as $category) {
                $flags = $category->getFlags();
                $assessments[] = [
                    'category' => $category->value,
                    'flag_count' => count($flags),
                ];
            }

            expect($assessments)->toHaveCount(3);
        });

        it('enables category-based filtering and analysis', function (): void {
            $allFlags = RiskFlag::cases();

            expect($allFlags)->toBeArray()
                ->and(count($allFlags))->toBeGreaterThan(0);

            foreach (RiskCategory::cases() as $category) {
                $flags = $category->getFlags();
                expect($flags)->toBeArray();
            }
        });
    });

    describe('category semantics', function (): void {
        it('represents device-related risks correctly', function (): void {
            $category = RiskCategory::Device;
            $flags = $category->getFlags();

            expect($category->value)->toBe('device');
            expect($flags)->toContain(RiskFlag::HighDeviceRisk);
        });

        it('represents network-related risks correctly', function (): void {
            $category = RiskCategory::Network;
            $flags = $category->getFlags();

            expect($category->value)->toBe('network');
            expect($flags)->toContain(RiskFlag::ProxyDetected)
                ->and($flags)->toContain(RiskFlag::VpnDetected);
        });

        it('represents impersonation risks correctly', function (): void {
            $category = RiskCategory::Impersonation;
            $flags = $category->getFlags();

            expect($category->value)->toBe('impersonation');
            expect($flags)->toContain(RiskFlag::ImpossibleTravelDetected)
                ->and($flags)->toContain(RiskFlag::LocationSpoofing);
        });

        it('represents multi-accounting risks correctly', function (): void {
            $category = RiskCategory::MultiAccounting;
            $flags = $category->getFlags();

            expect($category->value)->toBe('multi_accounting');
            expect($flags)->toContain(RiskFlag::RepeatDevice)
                ->and($flags)->toContain(RiskFlag::RepeatFace)
                ->and($flags)->toContain(RiskFlag::RepeatId);
        });

        it('represents ID selling risks correctly', function (): void {
            $category = RiskCategory::IDSelling;
            $flags = $category->getFlags();

            expect($category->value)->toBe('id_selling');
            expect($flags)->toContain(RiskFlag::ImpossibleTravelDetected)
                ->and($flags)->toContain(RiskFlag::IpDocumentCountryMismatch);
        });

        it('represents ID fraud risks correctly', function (): void {
            $category = RiskCategory::IDFraud;
            $flags = $category->getFlags();

            expect($category->value)->toBe('id_fraud');
            expect($flags)->toContain(RiskFlag::LikelyFakeId)
                ->and($flags)->toContain(RiskFlag::CannotConfirmIdIsAuthentic);
        });

        it('represents fraud farm risks correctly', function (): void {
            $category = RiskCategory::FraudFarm;
            $flags = $category->getFlags();

            expect($category->value)->toBe('fraud_farm');
            expect($flags)->toContain(RiskFlag::KnownFraudFace)
                ->and($flags)->toContain(RiskFlag::KnownFraudId);
        });

        it('represents COPPA compliance risks correctly', function (): void {
            $category = RiskCategory::COPPA;
            $flags = $category->getFlags();

            expect($category->value)->toBe('coppa');
            expect($flags)->toContain(RiskFlag::IdAgeBelow16);
        });
    });
});
