<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;

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
