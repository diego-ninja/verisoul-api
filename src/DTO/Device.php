<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;

final readonly class Device extends GraniteDTO
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
