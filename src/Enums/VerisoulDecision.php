<?php

namespace Ninja\Verisoul\Enums;

enum VerisoulDecision: string
{
    case Fake = 'Fake';
    case Suspicious = 'Suspicious';
    case Real = 'Real';
    case Unknown = 'Unknown';

    public static function values(): array
    {
        return [
            self::Fake->value,
            self::Suspicious->value,
            self::Real->value,
            self::Unknown->value,
        ];
    }
}
