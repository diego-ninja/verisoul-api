<?php

namespace Ninja\Verisoul\Collections;

use Illuminate\Support\Collection;
use JsonException;
use Ninja\Granite\Contracts\GraniteObject;
use Ninja\Verisoul\Enums\RiskCategory;
use Ninja\Verisoul\Enums\RiskFlag;
use Ninja\Verisoul\Enums\RiskLevel;

/**
 * Collection of RiskFlag enums
 *
 * This collection provides specialized methods for managing and analyzing
 * collections of risk flags, including filtering by category, risk level,
 * and blocking status.
 */
final class RiskFlagCollection extends Collection implements GraniteObject
{
    /**
     * Create collection from array of flag data
     */
    public static function from(mixed ...$args): static
    {
        $collection = new self();
        $flags = $args[0] ?? [];

        foreach ($flags as $flag) {
            if (is_string($flag)) {
                $riskFlag = RiskFlag::tryFrom($flag);
                if ($riskFlag) {
                    $collection->add($riskFlag);
                }
            } elseif ($flag instanceof RiskFlag) {
                $collection->add($flag);
            }
        }

        return $collection;
    }

    /**
     * Create collection from flag values
     */
    public static function fromValues(array $values): self
    {
        $collection = new self();

        foreach ($values as $value) {
            $flag = RiskFlag::tryFrom($value);
            if ($flag) {
                $collection->add($flag);
            }
        }

        return $collection;
    }

    /**
     * Create collection from flag names (case insensitive)
     */
    public static function fromNames(array $names): self
    {
        $collection = new self();

        foreach ($names as $name) {
            foreach (RiskFlag::cases() as $flag) {
                if (0 === strcasecmp($flag->name, $name)) {
                    $collection->add($flag);
                    break;
                }
            }
        }

        return $collection;
    }

    /**
     * Filter flags by category
     */
    public function byCategory(RiskCategory|string $category): self
    {
        $categoryValue = $category instanceof RiskCategory ? $category->value : $category;

        return $this->filter(function (RiskFlag $flag) use ($categoryValue) {
            $categories = $flag->getCategories();
            foreach ($categories as $flagCategory) {
                if ($flagCategory->value === $categoryValue) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Filter flags by multiple categories
     */
    public function byCategories(array $categories): self
    {
        return $this->filter(function (RiskFlag $flag) use ($categories) {
            $flagCategories = $flag->getCategories();
            foreach ($flagCategories as $flagCategory) {
                if (in_array($flagCategory->value, $categories) || in_array($flagCategory, $categories)) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Filter flags by risk level
     */
    public function byRiskLevel(RiskLevel $level): self
    {
        return $this->filter(fn(RiskFlag $flag) => $flag->getRiskLevel() === $level);
    }

    /**
     * Filter flags by multiple risk levels
     */
    public function byRiskLevels(array $levels): self
    {
        return $this->filter(fn(RiskFlag $flag) => in_array($flag->getRiskLevel(), $levels));
    }

    /**
     * Get only blocking flags
     */
    public function blocking(): self
    {
        return $this->filter(fn(RiskFlag $flag) => $flag->shouldBlock());
    }

    /**
     * Get only non-blocking flags
     */
    public function nonBlocking(): self
    {
        return $this->filter(fn(RiskFlag $flag) => ! $flag->shouldBlock());
    }

    /**
     * Get flags by display name pattern
     */
    public function byDisplayNamePattern(string $pattern): self
    {
        return $this->filter(fn(RiskFlag $flag) => preg_match("/{$pattern}/i", $flag->getDisplayName()));
    }

    /**
     * Group flags by category
     */
    public function groupByCategory(): array
    {
        $grouped = [];

        foreach ($this as $flag) {
            $categories = $flag->getCategories();
            foreach ($categories as $category) {
                if ( ! isset($grouped[$category->value])) {
                    $grouped[$category->value] = new self();
                }
                $grouped[$category->value]->add($flag);
            }
        }

        return $grouped;
    }

    /**
     * Group flags by risk level
     */
    public function groupByRiskLevel(): array
    {
        return $this->groupBy(fn(RiskFlag $flag) => $flag->getRiskLevel()->value)->toArray();
    }

    /**
     * Get risk level distribution
     */
    public function getRiskLevelDistribution(): array
    {
        $distribution = [
            'low' => 0,
            'moderate' => 0,
            'high' => 0,
            'critical' => 0,
            'unknown' => 0,
        ];

        foreach ($this as $flag) {
            $level = $flag->getRiskLevel()->value;
            $distribution[$level] = ($distribution[$level] ?? 0) + 1;
        }

        return $distribution;
    }

    /**
     * Get category distribution
     */
    public function getCategoryDistribution(): array
    {
        $distribution = [];

        foreach ($this as $flag) {
            $categories = $flag->getCategories();
            foreach ($categories as $category) {
                $categoryValue = $category->value;
                $distribution[$categoryValue] = ($distribution[$categoryValue] ?? 0) + 1;
            }
        }

        return $distribution;
    }

    /**
     * Get summary statistics
     */
    public function getSummary(): array
    {
        return [
            'total_flags' => $this->count(),
            'blocking_flags' => $this->blocking()->count(),
            'non_blocking_flags' => $this->nonBlocking()->count(),
            'risk_level_distribution' => $this->getRiskLevelDistribution(),
            'category_distribution' => $this->getCategoryDistribution(),
            'has_critical_flags' => $this->byRiskLevel(RiskLevel::Critical)->isNotEmpty(),
            'has_high_risk_flags' => $this->byRiskLevel(RiskLevel::High)->isNotEmpty(),
            'has_blocking_flags' => $this->blocking()->isNotEmpty(),
        ];
    }

    /**
     * Get the most severe flags (blocking first, then by risk level)
     */
    public function getMostSevere(int $limit = 10): self
    {
        return $this->sortBy([
            fn(RiskFlag $flag) => $flag->shouldBlock() ? 0 : 1, // Blocking flags first
            fn(RiskFlag $flag) => match ($flag->getRiskLevel()) {
                RiskLevel::Critical => 0,
                RiskLevel::High => 1,
                RiskLevel::Moderate => 2,
                RiskLevel::Low => 3,
                RiskLevel::Unknown => 4,
            },
        ])->take($limit);
    }

    /**
     * Check if collection contains any blocking flags
     */
    public function hasBlockingFlags(): bool
    {
        return $this->blocking()->isNotEmpty();
    }

    /**
     * Check if collection contains flags from specific category
     */
    public function hasCategory(RiskCategory|string $category): bool
    {
        return $this->byCategory($category)->isNotEmpty();
    }

    /**
     * Check if collection contains flags of specific risk level
     */
    public function hasRiskLevel(RiskLevel $level): bool
    {
        return $this->byRiskLevel($level)->isNotEmpty();
    }

    /**
     * Get unique categories represented in this collection
     */
    public function getUniqueCategories(): Collection
    {
        $categories = collect();

        foreach ($this as $flag) {
            $flagCategories = $flag->getCategories();
            foreach ($flagCategories as $category) {
                if ( ! $categories->contains($category)) {
                    $categories->add($category);
                }
            }
        }

        return $categories;
    }

    /**
     * Get unique risk levels represented in this collection
     */
    public function getUniqueRiskLevels(): Collection
    {
        return $this->map(fn(RiskFlag $flag) => $flag->getRiskLevel())->unique();
    }

    /**
     * Add flag to collection if not already present
     */
    public function addFlag(RiskFlag $flag): self
    {
        if ( ! $this->contains($flag)) {
            $this->add($flag);
        }

        return $this;
    }

    /**
     * Remove flag from collection
     */
    public function removeFlag(RiskFlag $flag): self
    {
        return $this->reject(fn(RiskFlag $item) => $item === $flag);
    }

    /**
     * Convert to array of flag values
     */
    public function toValues(): array
    {
        return $this->map(fn(RiskFlag $flag) => $flag->value)->toArray();
    }

    /**
     * Convert to array of flag names
     */
    public function toNames(): array
    {
        return $this->map(fn(RiskFlag $flag) => $flag->name)->toArray();
    }

    /**
     * Convert to array of display names
     */
    public function toDisplayNames(): array
    {
        return $this->map(fn(RiskFlag $flag) => $flag->getDisplayName())->toArray();
    }

    /**
     * Convert to detailed array representation
     */
    public function toDetailedArray(): array
    {
        return $this->map(fn(RiskFlag $flag) => [
            'name' => $flag->name,
            'value' => $flag->value,
            'display_name' => $flag->getDisplayName(),
            'description' => $flag->getDescription(),
            'risk_level' => $flag->getRiskLevel()->value,
            'categories' => array_map(fn(RiskCategory $cat) => $cat->value, $flag->getCategories()),
            'should_block' => $flag->shouldBlock(),
        ])->toArray();
    }

    /**
     * Convert to array for JSON serialization
     */
    public function array(): array
    {
        return $this->toValues();
    }

    /**
     * Convert to JSON string
     *
     * @throws JsonException
     */
    public function json(): string
    {
        return json_encode($this->array(), JSON_THROW_ON_ERROR);
    }

    /**
     * Create collection with only unique flags (removes duplicates)
     */
    public function uniqueFlags(): self
    {
        return new self($this->uniqueStrict()->values());
    }

    /**
     * Merge with another RiskFlagCollection
     */
    public function mergeFlags(RiskFlagCollection $other): self
    {
        return new self($this->merge($other)->uniqueFlags());
    }

    /**
     * Get intersection with another RiskFlagCollection
     */
    public function intersectFlags(RiskFlagCollection $other): self
    {
        return $this->filter(fn(RiskFlag $flag) => $other->contains($flag));
    }

    /**
     * Get difference with another RiskFlagCollection
     */
    public function diffFlags(RiskFlagCollection $other): self
    {
        return $this->filter(fn(RiskFlag $flag) => ! $other->contains($flag));
    }

    /**
     * Apply a decision rule and return matching flags
     */
    public function applyRule(callable $rule): self
    {
        return $this->filter($rule);
    }

    /**
     * Check if collection is empty or contains only low-risk flags
     */
    public function isLowRisk(): bool
    {
        if ($this->isEmpty()) {
            return true;
        }

        return $this->every(
            fn(RiskFlag $flag) =>
            RiskLevel::Low === $flag->getRiskLevel() ||
            RiskLevel::Unknown === $flag->getRiskLevel(),
        );
    }

    /**
     * Check if collection contains high-risk or critical flags
     */
    public function isHighRisk(): bool
    {
        return $this->contains(
            fn(RiskFlag $flag) =>
            RiskLevel::High === $flag->getRiskLevel() ||
            RiskLevel::Critical === $flag->getRiskLevel(),
        );
    }

    /**
     * Get recommendation based on flags in collection
     */
    public function getRecommendation(): string
    {
        if ($this->isEmpty()) {
            return 'approve';
        }

        if ($this->hasBlockingFlags()) {
            return 'block';
        }

        if ($this->isHighRisk()) {
            return 'review';
        }

        if ($this->byRiskLevel(RiskLevel::Moderate)->isNotEmpty()) {
            return 'monitor';
        }

        return 'approve';
    }
}
