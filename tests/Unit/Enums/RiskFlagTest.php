<?php

use Ninja\Verisoul\Enums\RiskCategory;
use Ninja\Verisoul\Enums\RiskFlag;
use Ninja\Verisoul\Enums\RiskLevel;

describe('RiskFlag Enum', function (): void {
    describe('enum cases', function (): void {
        it('has all expected cases', function (): void {
            $cases = RiskFlag::cases();
            $values = array_map(fn($case) => $case->value, $cases);

            expect($cases)->toHaveCount(26)
                ->and($values)->toContain('high_device_risk')
                ->and($values)->toContain('proxy_detected')
                ->and($values)->toContain('vpn_detected')
                ->and($values)->toContain('datacenter_detected')
                ->and($values)->toContain('recent_fraud_ip')
                ->and($values)->toContain('impossible_travel_detected')
                ->and($values)->toContain('ip_document_country_mismatch')
                ->and($values)->toContain('cannot_confirm_id_is_authentic')
                ->and($values)->toContain('likely_fake_id')
                ->and($values)->toContain('id_expired')
                ->and($values)->toContain('id_age_below_16')
                ->and($values)->toContain('low_id_face_match_score')
                ->and($values)->toContain('moderate_id_face_match_score')
                ->and($values)->toContain('repeat_face')
                ->and($values)->toContain('repeat_id')
                ->and($values)->toContain('repeat_device')
                ->and($values)->toContain('known_fraud_face')
                ->and($values)->toContain('known_fraud_id')
                ->and($values)->toContain('location_spoofing')
                ->and($values)->toContain('different_device_type_same_category')
                ->and($values)->toContain('same_device_type_different_ip')
                ->and($values)->toContain('potential_link_sharing')
                ->and($values)->toContain('referring_ip_mismatch')
                ->and($values)->toContain('referring_user_agent_mismatch')
                ->and($values)->toContain('referring_device_timezone_mismatch')
                ->and($values)->toContain('referring_ip_timezone_mismatch');
        });

        it('has correct enum values', function (): void {
            expect(RiskFlag::HighDeviceRisk->value)->toBe('high_device_risk')
                ->and(RiskFlag::ProxyDetected->value)->toBe('proxy_detected')
                ->and(RiskFlag::VpnDetected->value)->toBe('vpn_detected')
                ->and(RiskFlag::DatacenterDetected->value)->toBe('datacenter_detected')
                ->and(RiskFlag::RecentFraudIp->value)->toBe('recent_fraud_ip')
                ->and(RiskFlag::ImpossibleTravelDetected->value)->toBe('impossible_travel_detected')
                ->and(RiskFlag::CannotConfirmIdIsAuthentic->value)->toBe('cannot_confirm_id_is_authentic')
                ->and(RiskFlag::LikelyFakeId->value)->toBe('likely_fake_id')
                ->and(RiskFlag::IdExpired->value)->toBe('id_expired')
                ->and(RiskFlag::IdAgeBelow16->value)->toBe('id_age_below_16')
                ->and(RiskFlag::LowIdFaceMatchScore->value)->toBe('low_id_face_match_score')
                ->and(RiskFlag::ModerateIdFaceMatchScore->value)->toBe('moderate_id_face_match_score')
                ->and(RiskFlag::RepeatFace->value)->toBe('repeat_face')
                ->and(RiskFlag::RepeatId->value)->toBe('repeat_id')
                ->and(RiskFlag::RepeatDevice->value)->toBe('repeat_device')
                ->and(RiskFlag::KnownFraudFace->value)->toBe('known_fraud_face')
                ->and(RiskFlag::KnownFraudId->value)->toBe('known_fraud_id')
                ->and(RiskFlag::LocationSpoofing->value)->toBe('location_spoofing')
                ->and(RiskFlag::DifferentDeviceTypeSameCategory->value)->toBe('different_device_type_same_category')
                ->and(RiskFlag::SameDeviceTypeDifferentIp->value)->toBe('same_device_type_different_ip')
                ->and(RiskFlag::PotentialLinkSharing->value)->toBe('potential_link_sharing')
                ->and(RiskFlag::ReferringIpMismatch->value)->toBe('referring_ip_mismatch')
                ->and(RiskFlag::ReferringUserAgentMismatch->value)->toBe('referring_user_agent_mismatch')
                ->and(RiskFlag::ReferringDeviceTimezoneMismatch->value)->toBe('referring_device_timezone_mismatch')
                ->and(RiskFlag::ReferringIpTimezoneMismatch->value)->toBe('referring_ip_timezone_mismatch');
        });

        it('can be created from string values', function (): void {
            expect(RiskFlag::from('high_device_risk'))->toBe(RiskFlag::HighDeviceRisk)
                ->and(RiskFlag::from('proxy_detected'))->toBe(RiskFlag::ProxyDetected)
                ->and(RiskFlag::from('repeat_face'))->toBe(RiskFlag::RepeatFace)
                ->and(RiskFlag::from('likely_fake_id'))->toBe(RiskFlag::LikelyFakeId);
        });

        it('can try to create from string values', function (): void {
            expect(RiskFlag::tryFrom('high_device_risk'))->toBe(RiskFlag::HighDeviceRisk)
                ->and(RiskFlag::tryFrom('invalid_flag'))->toBeNull();
        });
    });

    describe('values method', function (): void {
        it('returns all enum values as array', function (): void {
            $values = RiskFlag::values();

            expect($values)->toBeArray()
                ->and($values)->toHaveCount(26)
                ->and($values)->toContain('high_device_risk')
                ->and($values)->toContain('proxy_detected')
                ->and($values)->toContain('repeat_face')
                ->and($values)->toContain('likely_fake_id');
        });
    });

    describe('fromString method', function (): void {
        it('creates flag from string value', function (): void {
            expect(RiskFlag::fromString('high_device_risk'))->toBe(RiskFlag::HighDeviceRisk)
                ->and(RiskFlag::fromString('invalid_flag'))->toBeNull();
        });
    });


    describe('getByCategory method', function (): void {
        it('returns flags for device category', function (): void {
            $deviceFlags = RiskFlag::getByCategory('device');

            expect($deviceFlags)->toBeArray();
        });

        it('returns flags for network category', function (): void {
            $networkFlags = RiskFlag::getByCategory('network');

            expect($networkFlags)->toBeArray();
        });

        it('returns flags for impersonation category', function (): void {
            $impersonationFlags = RiskFlag::getByCategory('impersonation');

            expect($impersonationFlags)->toBeArray();
        });

        it('returns flags for multi_accounting category', function (): void {
            $multiAccountingFlags = RiskFlag::getByCategory('multi_accounting');

            expect($multiAccountingFlags)->toBeArray();
        });

        it('returns flags for id_selling category', function (): void {
            $idSellingFlags = RiskFlag::getByCategory('id_selling');

            expect($idSellingFlags)->toBeArray();
        });

        it('returns flags for id_fraud category', function (): void {
            $idFraudFlags = RiskFlag::getByCategory('id_fraud');

            expect($idFraudFlags)->toBeArray();
        });

        it('returns flags for fraud_farm category', function (): void {
            $fraudFarmFlags = RiskFlag::getByCategory('fraud_farm');

            expect($fraudFarmFlags)->toBeArray();
        });

        it('returns flags for coppa category', function (): void {
            $coppaFlags = RiskFlag::getByCategory('coppa');

            expect($coppaFlags)->toBeArray();
        });

        it('returns empty array for non-existent category', function (): void {
            $flags = RiskFlag::getByCategory('non_existent');

            expect($flags)->toBeArray()
                ->and($flags)->toBeEmpty();
        });
    });

    describe('getByRiskLevel method', function (): void {
        it('returns high risk flags', function (): void {
            $highRiskFlags = RiskFlag::getByRiskLevel(RiskLevel::High);

            expect($highRiskFlags)->toBeArray()
                ->and(count($highRiskFlags))->toBeGreaterThan(0);
        });

        it('returns moderate risk flags', function (): void {
            $moderateRiskFlags = RiskFlag::getByRiskLevel(RiskLevel::Moderate);

            expect($moderateRiskFlags)->toBeArray()
                ->and(count($moderateRiskFlags))->toBeGreaterThan(0);
        });

        it('returns low risk flags', function (): void {
            $lowRiskFlags = RiskFlag::getByRiskLevel(RiskLevel::Low);

            expect($lowRiskFlags)->toBeArray()
                ->and(count($lowRiskFlags))->toBeGreaterThan(0);
        });
    });

    describe('getBlockingFlags method', function (): void {
        it('returns flags that should block verification', function (): void {
            $blockingFlags = RiskFlag::getBlockingFlags();

            expect($blockingFlags)->toBeArray()
                ->and(count($blockingFlags))->toBeGreaterThan(0);
        });

        it('all returned flags should block', function (): void {
            $blockingFlags = RiskFlag::getBlockingFlags();

            foreach ($blockingFlags as $flag) {
                expect($flag->shouldBlock())->toBeTrue();
            }
        });
    });

    describe('getCategories method', function (): void {
        it('returns categories for flags', function (): void {
            $categories = RiskFlag::HighDeviceRisk->getCategories();

            expect($categories)->toBeArray();
        });

        it('returns array of RiskCategory instances', function (): void {
            $categories = RiskFlag::ProxyDetected->getCategories();

            expect($categories)->toBeArray();

            foreach ($categories as $category) {
                expect($category)->toBeInstanceOf(RiskCategory::class);
            }
        });

        it('some flags belong to multiple categories', function (): void {
            $categories = RiskFlag::ImpossibleTravelDetected->getCategories();

            expect($categories)->toBeArray()
                ->and(count($categories))->toBeGreaterThanOrEqual(1);
        });
    });

    describe('getDisplayName method', function (): void {
        it('returns correct display names', function (): void {
            expect(RiskFlag::HighDeviceRisk->getDisplayName())->toBe('High Device Risk')
                ->and(RiskFlag::ProxyDetected->getDisplayName())->toBe('Proxy Detected')
                ->and(RiskFlag::VpnDetected->getDisplayName())->toBe('VPN Detected')
                ->and(RiskFlag::RepeatFace->getDisplayName())->toBe('Repeat Face')
                ->and(RiskFlag::LikelyFakeId->getDisplayName())->toBe('Likely Fake ID')
                ->and(RiskFlag::IdAgeBelow16->getDisplayName())->toBe('ID Age Below 16')
                ->and(RiskFlag::ImpossibleTravelDetected->getDisplayName())->toBe('Impossible Travel Detected');
        });
    });

    describe('getDescription method', function (): void {
        it('returns correct descriptions', function (): void {
            expect(RiskFlag::HighDeviceRisk->getDescription())->toBe('Device likely emulator, VM')
                ->and(RiskFlag::ProxyDetected->getDescription())->toBe('ID Check on a proxy IP')
                ->and(RiskFlag::VpnDetected->getDescription())->toBe('ID Check on a VPN')
                ->and(RiskFlag::RepeatFace->getDescription())->toBe('Face has been seen in your application under a different account')
                ->and(RiskFlag::LikelyFakeId->getDescription())->toBe('ID very likely fake')
                ->and(RiskFlag::IdAgeBelow16->getDescription())->toBe('DOB on ID indicates user is below 16 years old');
        });
    });

    describe('getRiskLevel method', function (): void {
        it('returns correct risk levels', function (): void {
            expect(RiskFlag::HighDeviceRisk->getRiskLevel())->toBe(RiskLevel::High)
                ->and(RiskFlag::ProxyDetected->getRiskLevel())->toBe(RiskLevel::Moderate)
                ->and(RiskFlag::IdExpired->getRiskLevel())->toBe(RiskLevel::Low)
                ->and(RiskFlag::LikelyFakeId->getRiskLevel())->toBe(RiskLevel::High)
                ->and(RiskFlag::KnownFraudFace->getRiskLevel())->toBe(RiskLevel::Moderate);
        });
    });

    describe('shouldBlock method', function (): void {
        it('returns true for blocking flags', function (): void {
            expect(RiskFlag::LikelyFakeId->shouldBlock())->toBeTrue()
                ->and(RiskFlag::KnownFraudFace->shouldBlock())->toBeTrue()
                ->and(RiskFlag::KnownFraudId->shouldBlock())->toBeTrue()
                ->and(RiskFlag::LowIdFaceMatchScore->shouldBlock())->toBeTrue();
        });

        it('returns false for non-blocking flags', function (): void {
            expect(RiskFlag::ProxyDetected->shouldBlock())->toBeFalse()
                ->and(RiskFlag::VpnDetected->shouldBlock())->toBeFalse()
                ->and(RiskFlag::IdExpired->shouldBlock())->toBeFalse()
                ->and(RiskFlag::RepeatFace->shouldBlock())->toBeFalse();
        });
    });

    describe('enum behavior', function (): void {
        it('supports comparison operations', function (): void {
            expect(RiskFlag::HighDeviceRisk === RiskFlag::HighDeviceRisk)->toBeTrue()
                ->and(RiskFlag::HighDeviceRisk === RiskFlag::ProxyDetected)->toBeFalse()
                ->and(RiskFlag::HighDeviceRisk !== RiskFlag::ProxyDetected)->toBeTrue();
        });

        it('can be used in match expressions', function (): void {
            $flag = RiskFlag::HighDeviceRisk;

            $severity = match ($flag) {
                RiskFlag::LikelyFakeId => 'critical',
                RiskFlag::HighDeviceRisk => 'high',
                RiskFlag::ProxyDetected => 'medium',
                RiskFlag::IdExpired => 'low',
                default => 'unknown',
            };

            expect($severity)->toBe('high');
        });

        it('can be used in arrays', function (): void {
            $criticalFlags = [
                RiskFlag::LikelyFakeId,
                RiskFlag::KnownFraudFace,
                RiskFlag::KnownFraudId,
            ];

            expect($criticalFlags)->toHaveCount(3)
                ->and(in_array(RiskFlag::LikelyFakeId, $criticalFlags))->toBeTrue()
                ->and(in_array(RiskFlag::ProxyDetected, $criticalFlags))->toBeFalse();
        });

        it('supports serialization', function (): void {
            $flag = RiskFlag::HighDeviceRisk;
            $serialized = serialize($flag);
            $unserialized = unserialize($serialized);

            expect($unserialized)->toBe(RiskFlag::HighDeviceRisk)
                ->and($unserialized->value)->toBe('high_device_risk');
        });
    });

    describe('validation and error handling', function (): void {
        it('throws exception for invalid string values', function (): void {
            expect(fn() => RiskFlag::from('invalid_flag'))
                ->toThrow(ValueError::class);
        });

        it('handles case sensitivity correctly', function (): void {
            expect(RiskFlag::tryFrom('High_Device_Risk'))->toBeNull()
                ->and(RiskFlag::tryFrom('HIGH_DEVICE_RISK'))->toBeNull()
                ->and(RiskFlag::tryFrom('high_device_risk'))->toBe(RiskFlag::HighDeviceRisk);
        });
    });

    describe('business logic integration', function (): void {
        it('supports risk assessment workflow', function (): void {
            $testFlags = [
                RiskFlag::LikelyFakeId,
                RiskFlag::ProxyDetected,
                RiskFlag::IdExpired,
            ];

            $assessments = [];
            foreach ($testFlags as $flag) {
                $assessments[] = [
                    'flag' => $flag->value,
                    'level' => $flag->getRiskLevel()->value,
                    'blocking' => $flag->shouldBlock(),
                    'display' => $flag->getDisplayName(),
                ];
            }

            expect($assessments)->toHaveCount(3);
        });

        it('supports category-based filtering', function (): void {
            $deviceFlags = RiskFlag::getByCategory('device');
            $networkFlags = RiskFlag::getByCategory('network');

            expect($deviceFlags)->toBeArray()
                ->and($networkFlags)->toBeArray();
        });
    });
});
