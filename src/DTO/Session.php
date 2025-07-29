<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;
use Carbon\Carbon;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;
use Ninja\Verisoul\Collections\RiskSignalCollection;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class Session extends GraniteDTO
{
    public function __construct(
        public ?Carbon $startTime,
        public ?string $trueCountryCode,
        public Network $network,
        public Location $location,
        public Browser $browser,
        public Device $device,
        public Bot $bot,
        public RiskSignalCollection $riskSignals,
    ) {}
}
