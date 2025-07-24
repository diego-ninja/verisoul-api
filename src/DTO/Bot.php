<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;

final readonly class Bot extends GraniteDTO
{
    public function __construct(
        public int $mouseNumEvents,
        public int $clickNumEvents,
        public int $keyboardNumEvents,
        public int $touchNumEvents,
        public int $clipboardNumEvents,
    ) {}
}
