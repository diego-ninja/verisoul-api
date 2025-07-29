<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
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
