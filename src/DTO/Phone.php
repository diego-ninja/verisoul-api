<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class Phone extends GraniteDTO
{
    public function __construct(
        public bool $valid,
        public string $phoneNumber,
        public string $callingCountryCode,
        public string $countryCode,
        public string $carrierName,
        public string $lineType,
    ) {}
}
