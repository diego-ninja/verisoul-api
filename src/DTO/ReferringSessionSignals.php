<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class ReferringSessionSignals extends GraniteDTO
{
    public function __construct(
        public float $impossibleTravel,
        public float $ipMismatch,
        public float $userAgentMismatch,
        public float $deviceTimezoneMismatch,
        public float $ipTimezoneMismatch,
    ) {}
}
