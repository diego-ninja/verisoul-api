<?php

namespace Ninja\Verisoul\DTO;

use Carbon\Carbon;
use Ninja\Granite\GraniteDTO;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;
use Ninja\Verisoul\Collections\RiskSignalCollection;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class Account extends GraniteDTO
{
    public function __construct(
        public UserAccount $account,
        public int $numSessions,
        public Carbon $firstSeen,
        public Carbon $lastSeen,
        public string $lastSession,
        public string $country,
        public array $countries,
        public UniqueValues $uniqueDevices,
        public UniqueValues $uniqueNetworks,
        public Email $email,
        public RiskSignalCollection $riskSignalAverage,
    ) {}
}
