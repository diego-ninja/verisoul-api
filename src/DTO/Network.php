<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\Granite;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class Network extends Granite
{
    public function __construct(
        public ?string $ipAddress,
        public ?string $serviceProvider,
        public ?string $connectionType,
    ) {}
}
