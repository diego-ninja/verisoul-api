<?php

namespace Ninja\Verisoul\Responses;

use Illuminate\Support\Collection;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;
use Ninja\Verisoul\Collections\RiskFlagCollection;
use Ninja\Verisoul\Collections\RiskSignalCollection;
use Ninja\Verisoul\DTO\DeviceNetworkSignals;
use Ninja\Verisoul\DTO\Matches;
use Ninja\Verisoul\DTO\Metadata;
use Ninja\Verisoul\DTO\PhotoUrls;
use Ninja\Verisoul\DTO\ReferringSessionSignals;
use Ninja\Verisoul\DTO\SessionData;
use Ninja\Verisoul\Enums\RiskFlag;
use Ninja\Verisoul\Enums\RiskLevel;
use Ninja\Verisoul\Enums\VerisoulDecision;
use Ninja\Verisoul\ValueObjects\Score;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class VerifyFaceResponse extends ApiResponse
{
    public function __construct(
        public Metadata $metadata,
        public VerisoulDecision $decision,
        public Score $riskScore,
        public RiskFlagCollection $riskFlags,
        public DeviceNetworkSignals $deviceNetworkSignals,
        public ReferringSessionSignals $referringSessionSignals,
        public PhotoUrls $photoUrls,
        public SessionData $sessionData,
        public Matches $matches,
    ) {}

    /**
     * Check if has blocking risk flags
     */
    public function hasBlockingRiskFlags(): bool
    {
        return $this->riskFlags->some(fn(RiskFlag $flag) => $flag->shouldBlock());
    }

    /**
     * Check if has moderate risk flags that require review
     */
    public function hasModerateRiskFlags(): bool
    {
        return $this->riskFlags->some(fn(RiskFlag $flag) => RiskLevel::Moderate === $flag->getRiskLevel());
    }

    /**
     * Get risk flags by category
     */
    public function getRiskFlagsByCategory(): array
    {
        $categories = [];
        foreach ($this->riskFlags as $flag) {
            $flagCategories = $flag->getCategories();
            foreach ($flagCategories as $category) {
                $categoryValue = $category->value;
                if ( ! isset($categories[$categoryValue])) {
                    $categories[$categoryValue] = [];
                }
                $categories[$categoryValue][] = $flag;
            }
        }

        return $categories;
    }

    /**
     * Get risk flags by level
     */
    public function getRiskFlagsByLevel(): array
    {
        $levels = [];
        $this->riskFlags->each(function (RiskFlag $flag) use (&$levels): void {
            $level = $flag->getRiskLevel();
            if ( ! isset($levels[$level->value])) {
                $levels[$level->value] = [];
            }
            $levels[$level->value][] = $flag;
        });

        return $levels;
    }

    /**
     * Check if specific risk flag is present
     */
    public function hasRiskFlag(RiskFlag $flag): bool
    {
        return $this->riskFlags->contains(fn(RiskFlag $riskFlag) => $riskFlag === $flag);
    }

    /**
     * Get risk flags as string array
     */
    public function getRiskFlagsAsStrings(): array
    {
        return $this->riskFlags->map(fn(RiskFlag $flag) => $flag->value)->toArray();
    }

    /**
     * Get all risk signals as a unified collection
     */
    public function getRiskSignals(): RiskSignalCollection
    {
        return RiskSignalCollection::fromVerisoulSignals(
            deviceNetworkSignals: $this->deviceNetworkSignals,
            referringSessionSignals: $this->referringSessionSignals,
        );
    }
}
