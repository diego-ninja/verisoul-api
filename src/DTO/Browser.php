<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
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
