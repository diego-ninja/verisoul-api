<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;

final readonly class Document extends GraniteDTO
{
    public function __construct(
        public TemplateInfo $templateInfo,
        public UserData $userData,
    ) {}
}
