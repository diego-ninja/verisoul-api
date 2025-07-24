<?php

namespace Ninja\Verisoul\Responses;

use Ninja\Verisoul\DTO\Session;
use Ninja\Verisoul\Enums\VerisoulDecision;

final readonly class UnauthenticatedSessionResponse extends ApiResponse
{
    public function __construct(
        public string $projectId,
        public string $sessionId,
        public string $requestId,
        public VerisoulDecision $decision,
        public float $accountScore,
        public float $bot,
        public float $multipleAccounts,
        public float $riskSignals,
        public int $accountsLinked,
        public array $lists,
        public Session $session,
    ) {}
}
