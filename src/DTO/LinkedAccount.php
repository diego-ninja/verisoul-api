<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;

final readonly class LinkedAccount extends GraniteDTO
{
    public function __construct(
        public string $accountId,
        public float $score,
        public string $email,
        public array $matchType,
        public array $lists,
        public array $metadata,
    ) {}
}
