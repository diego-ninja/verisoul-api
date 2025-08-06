<?php

namespace Ninja\Verisoul\Enums;

enum VerisoulEnvironment: string
{
    case Sandbox = 'sandbox';
    case Production = 'production';

    public function getBaseUrl(): string
    {
        return match ($this) {
            self::Sandbox => 'https://api.sandbox.verisoul.ai',
            self::Production => 'https://api.prod.verisoul.ai',
        };
    }
}
