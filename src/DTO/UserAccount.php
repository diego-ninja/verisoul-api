<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;

final readonly class UserAccount extends GraniteDTO
{
    public function __construct(
        public string $id,
        public ?string $email = null,
        public ?array $metadata = [],
        public ?string $group = null,
    ) {}
}
