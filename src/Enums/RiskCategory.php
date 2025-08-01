<?php

namespace Ninja\Verisoul\Enums;

enum RiskCategory: string
{
    case Device = 'device';
    case Network = 'network';
    case Impersonation = 'impersonation';
    case MultiAccounting = 'multi_accounting';
    case IDSelling = 'id_selling';
    case IDFraud = 'id_fraud';
    case FraudFarm = 'fraud_farm';
    case COPPA = 'coppa';

    /**
     * @return array<int, RiskFlag>
     */
    public function getFlags(): array
    {
        return match ($this) {
            self::Device => [
                RiskFlag::HighDeviceRisk,
            ],
            self::Network => [
                RiskFlag::ProxyDetected,
                RiskFlag::VpnDetected,
                RiskFlag::DatacenterDetected,
                RiskFlag::RecentFraudIp,
            ],
            self::Impersonation => [
                RiskFlag::ImpossibleTravelDetected,
                RiskFlag::CannotConfirmIdIsAuthentic,
                RiskFlag::DifferentDeviceTypeSameCategory,
                RiskFlag::SameDeviceTypeDifferentIp,
                RiskFlag::PotentialLinkSharing,
                RiskFlag::LocationSpoofing,
                RiskFlag::ReferringIpMismatch,
                RiskFlag::ReferringUserAgentMismatch,
                RiskFlag::ReferringDeviceTimezoneMismatch,
                RiskFlag::ReferringIpTimezoneMismatch,
                RiskFlag::LikelyFakeId,
                RiskFlag::IdExpired,
                RiskFlag::IdAgeBelow16,
            ],
            self::MultiAccounting => [
                RiskFlag::RepeatDevice,
                RiskFlag::RepeatFace,
                RiskFlag::RepeatId,
            ],
            self::IDSelling => [
                RiskFlag::ImpossibleTravelDetected,
                RiskFlag::IpDocumentCountryMismatch,
                RiskFlag::LocationSpoofing,
                RiskFlag::PotentialLinkSharing,
                RiskFlag::ReferringIpMismatch,
                RiskFlag::ReferringUserAgentMismatch,
                RiskFlag::ReferringDeviceTimezoneMismatch,
                RiskFlag::ReferringIpTimezoneMismatch,
            ],
            self::IDFraud => [
                RiskFlag::CannotConfirmIdIsAuthentic,
                RiskFlag::LowIdFaceMatchScore,
                RiskFlag::ModerateIdFaceMatchScore,
                RiskFlag::LikelyFakeId,
                RiskFlag::IdExpired,
            ],
            self::FraudFarm => [
                RiskFlag::KnownFraudFace,
                RiskFlag::KnownFraudId,
            ],
            self::COPPA => [
                RiskFlag::IdAgeBelow16,
            ],
        };
    }
}
