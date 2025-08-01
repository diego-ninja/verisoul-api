<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class AccountList extends GraniteDTO
{
    public function __construct(
        public ?string $requestId,
        public ?string $name,
        public ?string $description,
        public array $accounts,
    ) {}
}
