<?php

namespace Ninja\Verisoul\Responses;

use Carbon\Carbon;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;
use Ninja\Verisoul\Collections\RiskSignalCollection;
use Ninja\Verisoul\DTO\Email;
use Ninja\Verisoul\DTO\UniqueValues;
use Ninja\Verisoul\DTO\UserAccount;
use Ninja\Verisoul\Enums\VerisoulDecision;
use Ninja\Verisoul\Support\EnumLogger;
use Ninja\Verisoul\ValueObjects\Score;

#[SerializationConvention(SnakeCaseConvention::class)]
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
        public VerisoulDecision $decision = VerisoulDecision::Unknown,
    ) {}

    protected static function rules(): array
    {
        return [
            'decision' => [EnumLogger::logOnFail(VerisoulDecision::class, 'decision')],
        ];
    }
}
