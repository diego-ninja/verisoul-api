<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\Granite;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class Matches extends Granite
{
    public function __construct(
        public int $numAccountsLinked,
        public array $accountsLinked,
    ) {}
}
