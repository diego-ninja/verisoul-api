<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\Granite;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class AccountMatch extends Granite
{
    public function __construct(
        public string $accountId,
        public array $matchTypes,
    ) {}

    /**
     * Check if has face match
     */
    public function hasFaceMatch(): bool
    {
        return in_array('face', $this->matchTypes);
    }

    /**
     * Check if has ID match
     */
    public function hasIdMatch(): bool
    {
        return in_array('id', $this->matchTypes) || in_array('document', $this->matchTypes);
    }

    /**
     * Check if has device match
     */
    public function hasDeviceMatch(): bool
    {
        return in_array('device', $this->matchTypes);
    }

    /**
     * Check if has email match
     */
    public function hasEmailMatch(): bool
    {
        return in_array('email', $this->matchTypes);
    }

    /**
     * Check if has phone match
     */
    public function hasPhoneMatch(): bool
    {
        return in_array('phone', $this->matchTypes);
    }

    /**
     * Check if is exact match (multiple match types)
     */
    public function isExactMatch(): bool
    {
        return count($this->matchTypes) >= 2;
    }


    /**
     * Get match type priorities (for sorting)
     */
    public function getMatchPriority(): int
    {
        $priorities = [
            'face' => 100,
            'id' => 90,
            'document' => 90,
            'device' => 70,
            'email' => 50,
            'phone' => 40,
        ];

        $maxPriority = 0;
        foreach ($this->matchTypes as $type) {
            $maxPriority = max($maxPriority, $priorities[$type] ?? 0);
        }

        return $maxPriority;
    }

    /**
     * Get match summary
     */
    public function getMatchSummary(): array
    {
        return [
            'account_id' => $this->accountId,
            'match_types' => $this->matchTypes,
            'match_count' => count($this->matchTypes),
            'is_exact_match' => $this->isExactMatch(),
            'has_biometric_match' => $this->hasFaceMatch(),
            'has_document_match' => $this->hasIdMatch(),
            'priority' => $this->getMatchPriority(),
        ];
    }
}
