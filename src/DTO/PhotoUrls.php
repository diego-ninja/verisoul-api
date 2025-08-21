<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\Granite;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class PhotoUrls extends Granite
{
    public function __construct(
        public ?string $face = null,
        public ?string $idScanBack = null,
        public ?string $idScanFront = null,
    ) {}
}
