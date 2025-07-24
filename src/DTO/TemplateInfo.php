<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;

final readonly class TemplateInfo extends GraniteDTO
{
    public function __construct(
        public string $documentCountryCode,
        public ?string $documentState,
        public string $templateType,
    ) {}
}
