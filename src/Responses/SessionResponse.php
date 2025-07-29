<?php

namespace Ninja\Verisoul\Responses;

use Carbon\Carbon;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;
use Ninja\Verisoul\Collections\RiskSignalCollection;
use Ninja\Verisoul\DTO\Bot;
use Ninja\Verisoul\DTO\Browser;
use Ninja\Verisoul\DTO\Device;
use Ninja\Verisoul\DTO\Location;
use Ninja\Verisoul\DTO\Network;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class SessionResponse extends ApiResponse
{
    public function __construct(
        public array $accountIds,
        public string $requestId,
        public string $projectId,
        public string $sessionId,
        public Carbon $startTime,
        public string $trueCountryCode,
        public Network $network,
        public Location $location,
        public Browser $browser,
        public Device $device,
        public RiskSignalCollection $riskSignals,
        public Bot $bot,
    ) {}
}
