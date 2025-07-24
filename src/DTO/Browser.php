<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;

final readonly class Browser extends GraniteDTO
{
    public function __construct(
        public ?string $type,
        public ?string $version,
        public ?string $language,
        public ?string $userAgent,
        public ?string $timezone,
    ) {}
}
