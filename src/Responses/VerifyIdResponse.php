<?php

namespace Ninja\Verisoul\Responses;

use Illuminate\Support\Collection;
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

final readonly class VerifyIdResponse extends ApiResponse
{
    /**
     * @param  Collection<RiskFlag>  $riskFlags
     */
    public function __construct(
        public Metadata $metadata,
        public VerisoulDecision $decision,
        public Score $riskScore,
        public Collection $riskFlags,
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
            $category = $flag->getCategory();
            if (! isset($categories[$category])) {
                $categories[$category] = [];
            }
            $categories[$category][] = $flag;
        }

        return $categories;
    }

    /**
     * Get risk flags by level
     */
    public function getRiskFlagsByLevel(): array
    {
        $levels = [];
        $this->riskFlags->each(function (RiskFlag $flag) use (&$levels) {
            $level = $flag->getRiskLevel();
            if (! isset($levels[$level->value])) {
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
        return $this->riskFlags->contains(fn (RiskFlag $riskFlag) => $riskFlag === $flag);
    }

    /**
     * Get risk flags as string array
     */
    public function getRiskFlagsAsStrings(): array
    {
        return $this->riskFlags->map(fn (RiskFlag $flag) => $flag->value)->toArray();
    }

    /**
     * Get all risk signals as a unified collection
     */
    public function getRiskSignals(): RiskSignalCollection
    {
        return RiskSignalCollection::fromVerisoulSignals(
            deviceNetworkSignals: $this->deviceNetworkSignals,
            documentSignals: $this->documentSignals,
            referringSessionSignals: $this->referringSessionSignals
        );
    }
}
