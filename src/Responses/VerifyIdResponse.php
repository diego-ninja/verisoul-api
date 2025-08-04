<?php

namespace Ninja\Verisoul\Responses;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;
use Ninja\Verisoul\Collections\RiskFlagCollection;
use Ninja\Verisoul\Collections\RiskSignalCollection;
use Ninja\Verisoul\DTO\DeviceNetworkSignals;
use Ninja\Verisoul\DTO\Document;
use Ninja\Verisoul\DTO\DocumentSignals;
use Ninja\Verisoul\DTO\Matches;
use Ninja\Verisoul\DTO\Metadata;
use Ninja\Verisoul\DTO\PhotoUrls;
use Ninja\Verisoul\DTO\ReferringSessionSignals;
use Ninja\Verisoul\DTO\SessionData;
use Ninja\Verisoul\Enums\RiskFlag;
use Ninja\Verisoul\Enums\VerisoulDecision;
use Ninja\Verisoul\ValueObjects\Score;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class VerifyIdResponse extends ApiResponse
{
    public function __construct(
        public Metadata $metadata,
        public VerisoulDecision $decision,
        public Score $riskScore,
        public RiskFlagCollection $riskFlags,
        public DocumentSignals $documentSignals,
        public Document $documentData,
        public DeviceNetworkSignals $deviceNetworkSignals,
        public ReferringSessionSignals $referringSessionSignals,
        public PhotoUrls $photoUrls,
        public SessionData $sessionData,
        public Matches $matches,
    ) {}


    /**
     * Get risk flags by category
     */
    public function getRiskFlagsByCategory(): array
    {
        $categories = [];
        foreach ($this->riskFlags as $flag) {
            if ( ! $flag instanceof RiskFlag) {
                continue;
            }
            $flagCategories = $flag->getCategories();
            if ( ! is_iterable($flagCategories)) {
                continue;
            }
            foreach ($flagCategories as $category) {
                if ( ! is_object($category) || ! property_exists($category, 'value')) {
                    continue;
                }
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
        $this->riskFlags->each(function ($flag) use (&$levels): void {
            if ( ! $flag instanceof RiskFlag) {
                return;
            }
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
        return $this->riskFlags->map(function ($flag) {
            if ( ! $flag instanceof RiskFlag) {
                throw new InvalidArgumentException('Expected RiskFlag instance');
            }
            return $flag->value;
        })->toArray();
    }

    /**
     * Get all risk signals as a unified collection
     */
    public function getRiskSignals(): RiskSignalCollection
    {
        return RiskSignalCollection::fromVerisoulSignals(
            deviceNetworkSignals: $this->deviceNetworkSignals,
            documentSignals: $this->documentSignals,
            referringSessionSignals: $this->referringSessionSignals,
        );
    }
}
