<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;
final readonly class PhotoUrls extends GraniteDTO
{
    public function __construct(
        public ?string $face = null,
        public ?string $idScanBack = null,
        public ?string $idScanFront = null,
    ) {}
}
