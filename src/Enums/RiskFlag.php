<?php

namespace Ninja\Verisoul\Enums;

enum RiskFlag: string
{
    case HighDeviceRisk = 'high_device_risk';
    case ProxyDetected = 'proxy_detected';
    case VpnDetected = 'vpn_detected';
    case DatacenterDetected = 'datacenter_detected';
    case RecentFraudIp = 'recent_fraud_ip';
    case ImpossibleTravelDetected = 'impossible_travel_detected';
    case IpDocumentCountryMismatch = 'ip_document_country_mismatch';
    case CannotConfirmIdIsAuthentic = 'cannot_confirm_id_is_authentic';
    case LikelyFakeId = 'likely_fake_id';
    case IdExpired = 'id_expired';
    case IdAgeBelow16 = 'id_age_below_16';
    case LowIdFaceMatchScore = 'low_id_face_match_score';
    case ModerateIdFaceMatchScore = 'moderate_id_face_match_score';
    case RepeatFace = 'repeat_face';
    case RepeatId = 'repeat_id';
    case RepeatDevice = 'repeat_device';
    case KnownFraudFace = 'known_fraud_face';
    case KnownFraudId = 'known_fraud_id';
    case LocationSpoofing = 'location_spoofing';
    case DifferentDeviceTypeSameCategory = 'different_device_type_same_category';
    case SameDeviceTypeDifferentIp = 'same_device_type_different_ip';
    case PotentialLinkSharing = 'potential_link_sharing';
    case ReferringIpMismatch = 'referring_ip_mismatch';
    case ReferringUserAgentMismatch = 'referring_user_agent_mismatch';
    case ReferringDeviceTimezoneMismatch = 'referring_device_timezone_mismatch';
    case ReferringIpTimezoneMismatch = 'referring_ip_timezone_mismatch';

    /**
     * Get all possible risk flag values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }


    /**
     * Create from string value with validation
     */
    public static function fromString(string $value): ?self
    {
        return self::tryFrom($value);
    }

    /**
     * Get flags by category
     */
    public static function getByCategory(string $category): array
    {
        $flags = [];

        foreach (self::cases() as $flag) {
            $categories = $flag->getCategories();
            foreach ($categories as $riskCategory) {
                if ($riskCategory->value === $category) {
                    $flags[] = $flag;
                    break;
                }
            }
        }

        return array_values(array_unique($flags, SORT_REGULAR));
    }

    /**
     * Get flags by risk level
     */
    public static function getByRiskLevel(RiskLevel $level): array
    {
        return array_filter(self::cases(), fn(RiskFlag $flag) => $flag->getRiskLevel() === $level);
    }

    /**
     * Get blocking flags
     */
    public static function getBlockingFlags(): array
    {
        return array_filter(self::cases(), fn(RiskFlag $flag) => $flag->shouldBlock());
    }

    public function getCategories(): array
    {
        $categories = [];

        foreach (RiskCategory::cases() as $category) {
            if (in_array($this, $category->getFlags(), true)) {
                $categories[] = $category;
            }
        }

        return $categories;
    }


    /**
     * Get display name for risk flag
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::HighDeviceRisk => 'High Device Risk',
            self::RepeatDevice => 'Repeat Device',
            self::ProxyDetected => 'Proxy Detected',
            self::VpnDetected => 'VPN Detected',
            self::DatacenterDetected => 'Datacenter Detected',
            self::RecentFraudIp => 'Recent Fraud IP',
            self::CannotConfirmIdIsAuthentic => 'Cannot Confirm ID is Authentic',
            self::LikelyFakeId => 'Likely Fake ID',
            self::IdExpired => 'ID Expired',
            self::IdAgeBelow16 => 'ID Age Below 16',
            self::LowIdFaceMatchScore => 'Low ID Face Match Score',
            self::ModerateIdFaceMatchScore => 'Moderate ID Face Match Score',
            self::RepeatFace => 'Repeat Face',
            self::RepeatId => 'Repeat ID',
            self::KnownFraudFace => 'Known Fraud Face',
            self::KnownFraudId => 'Known Fraud ID',
            self::ImpossibleTravelDetected => 'Impossible Travel Detected',
            self::IpDocumentCountryMismatch => 'IP Document Country Mismatch',
            self::LocationSpoofing => 'Location Spoofing',
            self::DifferentDeviceTypeSameCategory => 'Different Device Type Same Category',
            self::SameDeviceTypeDifferentIp => 'Same Device Type Different IP',
            self::PotentialLinkSharing => 'Potential Link Sharing',
            self::ReferringIpMismatch => 'Referring IP Mismatch',
            self::ReferringUserAgentMismatch => 'Referring User Agent Mismatch',
            self::ReferringDeviceTimezoneMismatch => 'Referring Device Timezone Mismatch',
            self::ReferringIpTimezoneMismatch => 'Referring IP Timezone Mismatch',
        };
    }

    /**
     * Get description for risk flag
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::HighDeviceRisk => 'Device likely emulator, VM',
            self::RepeatDevice => 'Device has been used for multiple ID Checks',
            self::ProxyDetected => 'ID Check on a proxy IP',
            self::VpnDetected => 'ID Check on a VPN',
            self::DatacenterDetected => 'ID Check on a datacenter IP',
            self::RecentFraudIp => 'IP recently reported as fraud',
            self::CannotConfirmIdIsAuthentic => 'ID may be spoofed or digital media',
            self::LikelyFakeId => 'ID very likely fake',
            self::IdExpired => 'ID Expiration date is past',
            self::IdAgeBelow16 => 'DOB on ID indicates user is below 16 years old',
            self::LowIdFaceMatchScore => 'Face does not match ID Photo',
            self::ModerateIdFaceMatchScore => 'Face may not match ID photo',
            self::RepeatFace => 'Face has been seen in your application under a different account',
            self::RepeatId => 'ID has been seen in your application under a different account',
            self::KnownFraudFace => 'Face is associated with fraud',
            self::KnownFraudId => 'ID is associated with fraud',
            self::ImpossibleTravelDetected => 'Referring session geolocation is far from ID Check geolocation',
            self::IpDocumentCountryMismatch => 'Current IP geolocation country does not match document geolocation',
            self::LocationSpoofing => 'User is actively trying to obfuscate their current location',
            self::DifferentDeviceTypeSameCategory => 'Referring session and ID Check session are same device category (e.g., mobile), but are different types (iPhone, Android)',
            self::SameDeviceTypeDifferentIp => 'Referring session and ID Check session are same device type (e.g., iPhone), but are different IPs',
            self::PotentialLinkSharing => 'Referring session is likely sharing the verification link',
            self::ReferringIpMismatch => 'Referring session IP different than ID Check session IP',
            self::ReferringUserAgentMismatch => 'Referring session user agent different than ID Check user agent',
            self::ReferringDeviceTimezoneMismatch => 'Referring session device timezone is a meaningfully different region than verification timezone',
            self::ReferringIpTimezoneMismatch => 'Referring session ip timezone is a meaningfully different region than verification timezone',
        };
    }

    /**
     * Get risk level for this flag
     */
    public function getRiskLevel(): RiskLevel
    {
        return match ($this) {
            // High risk flags
            self::HighDeviceRisk,
            self::ImpossibleTravelDetected,
            self::RepeatFace,
            self::RepeatDevice,
            self::RepeatId,
            self::LocationSpoofing,
            self::IdAgeBelow16,
            self::LowIdFaceMatchScore,
            self::LikelyFakeId => RiskLevel::High,

            // Medium risk flags
            self::ProxyDetected,
            self::VpnDetected,
            self::DatacenterDetected,
            self::DifferentDeviceTypeSameCategory,
            self::SameDeviceTypeDifferentIp,
            self::ReferringDeviceTimezoneMismatch,
            self::ReferringIpTimezoneMismatch,
            self::CannotConfirmIdIsAuthentic,
            self::IpDocumentCountryMismatch,
            self::KnownFraudFace,
            self::KnownFraudId,
            self::ModerateIdFaceMatchScore => RiskLevel::Moderate,

            // Low risk flags
            self::IdExpired,
            self::RecentFraudIp,
            self::PotentialLinkSharing,
            self::ReferringIpMismatch,
            self::ReferringUserAgentMismatch => RiskLevel::Low,
        };
    }

    /**
     * Check if this flag should block verification
     */
    public function shouldBlock(): bool
    {
        return match ($this) {
            self::LikelyFakeId,
            self::KnownFraudFace,
            self::KnownFraudId,
            self::LowIdFaceMatchScore => true,
            default => false,
        };
    }
}
