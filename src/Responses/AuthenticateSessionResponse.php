<?php

namespace Ninja\Verisoul\Responses;

use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;
use Ninja\Verisoul\Collections\LinkedAccountCollection;
use Ninja\Verisoul\DTO\Account;
use Ninja\Verisoul\DTO\Session;
use Ninja\Verisoul\Enums\VerisoulDecision;
use Ninja\Verisoul\Support\EnumLogger;
use Ninja\Verisoul\ValueObjects\Score;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class AuthenticateSessionResponse extends ApiResponse
{
    public function __construct(
        public string $projectId,
        public string $sessionId,
        public string $accountId,
        public string $requestId,
        public Score $accountScore,
        public float $bot,
        public float $multipleAccounts,
        public float $riskSignals,
        public int $accountsLinked,
        public array $lists,
        public Session $session,
        public Account $account,
        public ?LinkedAccountCollection $linkedAccounts,
        public VerisoulDecision $decision = VerisoulDecision::Unknown,
    ) {}

    public function getRiskSignals(): array
    {
        return $this->session->riskSignals->toArray();
    }

    protected static function rules(): array
    {
        return [
            'decision' => [EnumLogger::logOnFail(VerisoulDecision::class, 'decision')],
        ];
    }
}
