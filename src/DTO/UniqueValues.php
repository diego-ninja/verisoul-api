<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;

final readonly class UniqueValues extends GraniteDTO
{
    public function __construct(
        public int $lastDay,
        public int $lastWeek,
    ) {}
}
