<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class Email extends GraniteDTO
{
    public function __construct(
        public string $email,
        public bool $personal,
        public bool $disposable,
        public bool $valid,
    ) {}
}
