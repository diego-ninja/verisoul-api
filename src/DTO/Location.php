<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class Location extends GraniteDTO
{
    public function __construct(
        public ?string $continent,
        public ?string $countryCode,
        public ?string $state,
        public ?string $city,
        public ?string $zipCode,
        public ?string $timezone,
        public ?float $latitude,
        public ?float $longitude,
    ) {}
}
