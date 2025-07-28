<?php

namespace Ninja\Verisoul\Enums;

enum RiskLevel: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';
    case Unknown = 'unknown';

    public static function values(): array
    {
        return [
            self::Low->value,
            self::Medium->value,
            self::High->value,
            self::Critical->value,
            self::Unknown->value,
        ];
    }

    public static function withScore(float $score): RiskLevel
    {
        if ($score >= 0.9) {
            return self::Critical;
        } elseif ($score >= 0.7) {
            return self::High;
        } elseif ($score >= 0.4) {
            return self::Medium;
        } elseif ($score > 0) {
            return self::Low;
        }

        return self::Unknown;
    }
}
