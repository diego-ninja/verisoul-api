<?php

namespace Ninja\Verisoul\Enums;

enum VerificationType: string
{
    case Email = 'email';
    case Phone = 'phone';
    case Face = 'face';
    case Identity = 'identity';

    public static function values(): array
    {
        return [
            self::Email->value,
            self::Phone->value,
            self::Face->value,
            self::Identity->value,
        ];
    }
}
