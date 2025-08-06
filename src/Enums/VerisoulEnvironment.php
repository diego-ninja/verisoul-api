<?php

namespace Ninja\Verisoul\Enums;

enum VerisoulEnvironment: string
{
    case Sandbox = 'sandbox';
    case Production = 'production';

    public function getBaseUrl(string $prefix = 'api'): string
    {
        return match ($this) {
            self::Sandbox => sprintf('https://%s.sandbox.verisoul.ai', $prefix),
            self::Production => sprintf('https://%s.prod.verisoul.ai', $prefix),
        };
    }
}
