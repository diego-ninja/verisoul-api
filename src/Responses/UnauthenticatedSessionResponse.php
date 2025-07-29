<?php

namespace Ninja\Verisoul\Responses;

use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;
use Ninja\Verisoul\DTO\Session;
use Ninja\Verisoul\Enums\VerisoulDecision;

#[SerializationConvention(SnakeCaseConvention::class)]
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
