<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\Granite;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class Device extends Granite
{
    public function __construct(
        public ?string $category,
        public ?string $type,
        public ?string $os,
        public ?int $cpuCores,
        public ?int $memory,
        public ?string $gpu,
        public ?float $screenHeight,
        public ?float $screenWidth,
    ) {}
}
