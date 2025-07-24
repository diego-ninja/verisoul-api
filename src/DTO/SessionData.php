<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;

final readonly class SessionData extends GraniteDTO
{
    public function __construct(
        public ?string $trueCountryCode,
        public Network $network,
        public Location $location,
        public Browser $browser,
        public Device $device,
    ) {}
}
