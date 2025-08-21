<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\Granite;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class UserAccount extends Granite
{
    public function __construct(
        public string $id,
        public ?string $email = null,
        public ?array $metadata = [],
        public ?string $group = null,
    ) {}
}
