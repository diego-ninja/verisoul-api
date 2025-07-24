<?php

namespace Ninja\Verisoul\Responses;

use Carbon\Carbon;
use Ninja\Verisoul\Collections\RiskSignalCollection;
use Ninja\Verisoul\DTO\Email;
use Ninja\Verisoul\DTO\UniqueValues;
use Ninja\Verisoul\DTO\UserAccount;
use Ninja\Verisoul\Enums\VerisoulDecision;
use Ninja\Verisoul\ValueObjects\Score;

final readonly class AccountResponse extends ApiResponse
{
    public function __construct(
        public string $projectId,
        public string $requestId,
        public UserAccount $account,
        public int $numSessions,
        public Carbon $firstSeen,
        public Carbon $lastSeen,
        public string $lastSession,
        public VerisoulDecision $decision,
        public Score $accountScore,
        public float $bot,
        public float $multipleAccounts,
        public float $riskSignals,
        public int $accountsLinked,
        public string $country,
        public array $countries,
        public array $lists,
        public UniqueValues $uniqueDevices,
        public UniqueValues $uniqueNetworks,
        public Email $email,
        public RiskSignalCollection $riskSignalAverage,
    ) {}
}
