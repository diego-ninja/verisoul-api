<?php

namespace Ninja\Verisoul\Enums;

use Ninja\Verisoul\ValueObjects\Score;

enum RiskLevel: string
{
    case Low = 'low';
    case Moderate = 'moderate';
    case High = 'high';
    case Critical = 'critical';
    case Unknown = 'unknown';

    public static function values(): array
    {
        return [
            self::Low->value,
            self::Moderate->value,
            self::High->value,
            self::Critical->value,
            self::Unknown->value,
        ];
    }

    public static function withScore(float|Score $score): RiskLevel
    {
        if ($score instanceof Score) {
            $score = $score->value();
        }

        if ($score >= 0.9) {
            return self::Critical;
        }
        if ($score >= 0.7) {
            return self::High;
        }
        if ($score >= 0.4) {
            return self::Moderate;
        }
        if ($score > 0) {
            return self::Low;
        }

        return self::Unknown;
    }
}
