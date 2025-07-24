<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;

final readonly class Email extends GraniteDTO
{
    public function __construct(
        public string $email,
        public bool $personal,
        public bool $disposable,
        public bool $valid,
    ) {}
}
