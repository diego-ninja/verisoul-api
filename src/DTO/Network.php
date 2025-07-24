<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;

final readonly class Network extends GraniteDTO
{
    public function __construct(
        public ?string $ipAddress,
        public ?string $serviceProvider,
        public ?string $connectionType,
    ) {}
}
