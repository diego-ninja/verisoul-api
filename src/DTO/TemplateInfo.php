<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class TemplateInfo extends GraniteDTO
{
    public function __construct(
        public string $documentCountryCode,
        public ?string $documentState,
        public string $templateType,
    ) {}
}
