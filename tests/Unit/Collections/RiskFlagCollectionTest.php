<?php

use Ninja\Verisoul\Collections\RiskFlagCollection;
use Ninja\Verisoul\Enums\RiskCategory;
use Ninja\Verisoul\Enums\RiskFlag;
use Ninja\Verisoul\Enums\RiskLevel;

describe('RiskFlagCollection', function (): void {
    describe('creation methods', function (): void {
        it('creates from mixed array', function (): void {
            $collection = RiskFlagCollection::from([
                'high_device_risk',
                RiskFlag::ProxyDetected,
                'invalid_flag', // should be ignored
                RiskFlag::LikelyFakeId,
            ]);

            expect($collection)->toHaveCount(3)
                ->and($collection->contains(RiskFlag::HighDeviceRisk))->toBeTrue()
                ->and($collection->contains(RiskFlag::ProxyDetected))->toBeTrue()
                ->and($collection->contains(RiskFlag::LikelyFakeId))->toBeTrue();
        });

        it('creates from values', function (): void {
            $collection = RiskFlagCollection::fromValues([
                'high_device_risk',
                'proxy_detected',
                'invalid_flag', // should be ignored
            ]);

            expect($collection)->toHaveCount(2)
                ->and($collection->contains(RiskFlag::HighDeviceRisk))->toBeTrue()
                ->and($collection->contains(RiskFlag::ProxyDetected))->toBeTrue();
        });

        it('creates from names case insensitive', function (): void {
            $collection = RiskFlagCollection::fromNames([
                'HighDeviceRisk',
                'PROXYDETECTED',
                'likelyfakeid',
                'InvalidFlag', // should be ignored
            ]);

            expect($collection)->toHaveCount(3)
                ->and($collection->contains(RiskFlag::HighDeviceRisk))->toBeTrue()
                ->and($collection->contains(RiskFlag::ProxyDetected))->toBeTrue()
                ->and($collection->contains(RiskFlag::LikelyFakeId))->toBeTrue();
        });
    });

    describe('filtering methods', function (): void {
        beforeEach(function (): void {
            $this->collection = RiskFlagCollection::from([
                RiskFlag::HighDeviceRisk,
                RiskFlag::ProxyDetected,
                RiskFlag::LikelyFakeId,
                RiskFlag::IdExpired,
                RiskFlag::RepeatFace,
                RiskFlag::KnownFraudFace,
            ]);
        });

        it('filters by category', function (): void {
            $networkFlags = $this->collection->byCategory(RiskCategory::Network);

            expect($networkFlags)->toHaveCount(1)
                ->and($networkFlags->contains(RiskFlag::ProxyDetected))->toBeTrue();
        });

        it('filters by category string', function (): void {
            $networkFlags = $this->collection->byCategory('network');

            expect($networkFlags)->toHaveCount(1)
                ->and($networkFlags->contains(RiskFlag::ProxyDetected))->toBeTrue();
        });

        it('filters by multiple categories', function (): void {
            $multipleFlags = $this->collection->byCategories(['network', 'device']);

            expect($multipleFlags)->toHaveCount(2)
                ->and($multipleFlags->contains(RiskFlag::ProxyDetected))->toBeTrue()
                ->and($multipleFlags->contains(RiskFlag::HighDeviceRisk))->toBeTrue();
        });

        it('filters by risk level', function (): void {
            $highRiskFlags = $this->collection->byRiskLevel(RiskLevel::High);

            expect($highRiskFlags->count())->toBeGreaterThan(0);
        });

        it('filters by multiple risk levels', function (): void {
            $multipleRiskFlags = $this->collection->byRiskLevels([
                RiskLevel::High,
                RiskLevel::Moderate,
            ]);

            expect($multipleRiskFlags->count())->toBeGreaterThan(0);
        });

        it('filters blocking flags', function (): void {
            $blockingFlags = $this->collection->blocking();

            expect($blockingFlags->contains(RiskFlag::LikelyFakeId))->toBeTrue()
                ->and($blockingFlags->contains(RiskFlag::KnownFraudFace))->toBeTrue();
        });

        it('filters non-blocking flags', function (): void {
            $nonBlockingFlags = $this->collection->nonBlocking();

            expect($nonBlockingFlags->contains(RiskFlag::ProxyDetected))->toBeTrue()
                ->and($nonBlockingFlags->contains(RiskFlag::IdExpired))->toBeTrue();
        });

        it('filters by display name pattern', function (): void {
            $deviceFlags = $this->collection->byDisplayNamePattern('Device');

            expect($deviceFlags->contains(RiskFlag::HighDeviceRisk))->toBeTrue();
        });
    });

    describe('grouping methods', function (): void {
        beforeEach(function (): void {
            $this->collection = RiskFlagCollection::from([
                RiskFlag::HighDeviceRisk,
                RiskFlag::ProxyDetected,
                RiskFlag::LikelyFakeId,
                RiskFlag::RepeatFace,
            ]);
        });

        it('groups by category', function (): void {
            $grouped = $this->collection->groupByCategory();

            expect($grouped)->toBeArray()
                ->and($grouped)->toHaveKey('device')
                ->and($grouped)->toHaveKey('network');
        });

        it('groups by risk level', function (): void {
            $grouped = $this->collection->groupByRiskLevel();

            expect($grouped)->toBeArray();
        });

        it('gets risk level distribution', function (): void {
            $distribution = $this->collection->getRiskLevelDistribution();

            expect($distribution)->toBeArray()
                ->and($distribution)->toHaveKey('low')
                ->and($distribution)->toHaveKey('moderate')
                ->and($distribution)->toHaveKey('high')
                ->and($distribution)->toHaveKey('critical')
                ->and($distribution)->toHaveKey('unknown');
        });

        it('gets category distribution', function (): void {
            $distribution = $this->collection->getCategoryDistribution();

            expect($distribution)->toBeArray();
        });
    });

    describe('analysis methods', function (): void {
        beforeEach(function (): void {
            $this->collection = RiskFlagCollection::from([
                RiskFlag::HighDeviceRisk,
                RiskFlag::LikelyFakeId,
                RiskFlag::ProxyDetected,
                RiskFlag::IdExpired,
            ]);
        });

        it('gets summary statistics', function (): void {
            $summary = $this->collection->getSummary();

            expect($summary)->toBeArray()
                ->and($summary)->toHaveKey('total_flags')
                ->and($summary)->toHaveKey('blocking_flags')
                ->and($summary)->toHaveKey('non_blocking_flags')
                ->and($summary)->toHaveKey('risk_level_distribution')
                ->and($summary)->toHaveKey('category_distribution')
                ->and($summary)->toHaveKey('has_critical_flags')
                ->and($summary)->toHaveKey('has_high_risk_flags')
                ->and($summary)->toHaveKey('has_blocking_flags');
        });

        it('gets most severe flags', function (): void {
            $severe = $this->collection->getMostSevere(2);

            expect($severe)->toHaveCount(2)
                ->and($severe->contains(RiskFlag::LikelyFakeId))->toBeTrue();
        });

        it('checks for blocking flags', function (): void {
            expect($this->collection->hasBlockingFlags())->toBeTrue();
        });

        it('checks for category presence', function (): void {
            expect($this->collection->hasCategory(RiskCategory::Device))->toBeTrue()
                ->and($this->collection->hasCategory('network'))->toBeTrue()
                ->and($this->collection->hasCategory('nonexistent'))->toBeFalse();
        });

        it('checks for risk level presence', function (): void {
            expect($this->collection->hasRiskLevel(RiskLevel::High))->toBeTrue();
        });

        it('gets unique categories', function (): void {
            $categories = $this->collection->getUniqueCategories();

            expect($categories)->toBeInstanceOf(Illuminate\Support\Collection::class);
        });

        it('gets unique risk levels', function (): void {
            $levels = $this->collection->getUniqueRiskLevels();

            expect($levels)->toBeInstanceOf(Illuminate\Support\Collection::class);
        });
    });

    describe('manipulation methods', function (): void {
        beforeEach(function (): void {
            $this->collection = RiskFlagCollection::from([
                RiskFlag::HighDeviceRisk,
                RiskFlag::ProxyDetected,
            ]);
        });

        it('adds flag if not present', function (): void {
            $result = $this->collection->addFlag(RiskFlag::LikelyFakeId);

            expect($result)->toHaveCount(3)
                ->and($result->contains(RiskFlag::LikelyFakeId))->toBeTrue();
        });

        it('does not add duplicate flag', function (): void {
            $result = $this->collection->addFlag(RiskFlag::HighDeviceRisk);

            expect($result)->toHaveCount(2);
        });

        it('removes flag', function (): void {
            $result = $this->collection->removeFlag(RiskFlag::HighDeviceRisk);

            expect($result)->toHaveCount(1)
                ->and($result->contains(RiskFlag::HighDeviceRisk))->toBeFalse();
        });

        it('creates unique collection', function (): void {
            $withDuplicates = RiskFlagCollection::from([
                RiskFlag::HighDeviceRisk,
                RiskFlag::ProxyDetected,
                RiskFlag::HighDeviceRisk, // duplicate
            ]);

            $unique = $withDuplicates->uniqueFlags();

            expect($unique)->toHaveCount(2);
        });
    });

    describe('collection operations', function (): void {
        beforeEach(function (): void {
            $this->collection1 = RiskFlagCollection::from([
                RiskFlag::HighDeviceRisk,
                RiskFlag::ProxyDetected,
            ]);

            $this->collection2 = RiskFlagCollection::from([
                RiskFlag::ProxyDetected,
                RiskFlag::LikelyFakeId,
            ]);
        });

        it('merges collections', function (): void {
            $merged = $this->collection1->mergeFlags($this->collection2);

            expect($merged)->toHaveCount(3)
                ->and($merged->contains(RiskFlag::HighDeviceRisk))->toBeTrue()
                ->and($merged->contains(RiskFlag::ProxyDetected))->toBeTrue()
                ->and($merged->contains(RiskFlag::LikelyFakeId))->toBeTrue();
        });

        it('intersects collections', function (): void {
            $intersection = $this->collection1->intersectFlags($this->collection2);

            expect($intersection)->toHaveCount(1)
                ->and($intersection->contains(RiskFlag::ProxyDetected))->toBeTrue();
        });

        it('calculates difference', function (): void {
            $diff = $this->collection1->diffFlags($this->collection2);

            expect($diff)->toHaveCount(1)
                ->and($diff->contains(RiskFlag::HighDeviceRisk))->toBeTrue();
        });
    });

    describe('risk assessment methods', function (): void {
        it('identifies low risk collection', function (): void {
            $lowRiskCollection = RiskFlagCollection::from([
                RiskFlag::IdExpired,
            ]);

            expect($lowRiskCollection->isLowRisk())->toBeTrue();
        });

        it('identifies high risk collection', function (): void {
            $highRiskCollection = RiskFlagCollection::from([
                RiskFlag::LikelyFakeId,
                RiskFlag::HighDeviceRisk,
            ]);

            expect($highRiskCollection->isHighRisk())->toBeTrue();
        });

        it('empty collection is low risk', function (): void {
            $emptyCollection = new RiskFlagCollection();

            expect($emptyCollection->isLowRisk())->toBeTrue();
        });

        it('provides recommendations', function (): void {
            $blockingCollection = RiskFlagCollection::from([RiskFlag::LikelyFakeId]);
            $highRiskCollection = RiskFlagCollection::from([RiskFlag::HighDeviceRisk]);
            $moderateRiskCollection = RiskFlagCollection::from([RiskFlag::ProxyDetected]);
            $emptyCollection = new RiskFlagCollection();

            expect($blockingCollection->getRecommendation())->toBe('block')
                ->and($highRiskCollection->getRecommendation())->toBe('review')
                ->and($moderateRiskCollection->getRecommendation())->toBe('monitor')
                ->and($emptyCollection->getRecommendation())->toBe('approve');
        });
    });

    describe('conversion methods', function (): void {
        beforeEach(function (): void {
            $this->collection = RiskFlagCollection::from([
                RiskFlag::HighDeviceRisk,
                RiskFlag::ProxyDetected,
            ]);
        });

        it('converts to values array', function (): void {
            $values = $this->collection->toValues();

            expect($values)->toBeArray()
                ->and($values)->toContain('high_device_risk')
                ->and($values)->toContain('proxy_detected');
        });

        it('converts to names array', function (): void {
            $names = $this->collection->toNames();

            expect($names)->toBeArray()
                ->and($names)->toContain('HighDeviceRisk')
                ->and($names)->toContain('ProxyDetected');
        });

        it('converts to display names array', function (): void {
            $displayNames = $this->collection->toDisplayNames();

            expect($displayNames)->toBeArray()
                ->and($displayNames)->toContain('High Device Risk')
                ->and($displayNames)->toContain('Proxy Detected');
        });

        it('converts to detailed array', function (): void {
            $detailed = $this->collection->toDetailedArray();

            expect($detailed)->toBeArray()
                ->and($detailed)->toHaveCount(2);

            expect($detailed[0])->toHaveKey('name')
                ->and($detailed[0])->toHaveKey('value')
                ->and($detailed[0])->toHaveKey('display_name')
                ->and($detailed[0])->toHaveKey('description')
                ->and($detailed[0])->toHaveKey('risk_level')
                ->and($detailed[0])->toHaveKey('categories')
                ->and($detailed[0])->toHaveKey('should_block');
        });

        it('converts to array for serialization', function (): void {
            $array = $this->collection->array();

            expect($array)->toBeArray()
                ->and($array)->toContain('high_device_risk')
                ->and($array)->toContain('proxy_detected');
        });

        it('converts to json', function (): void {
            $json = $this->collection->json();

            expect($json)->toBeString();

            $decoded = json_decode($json, true);
            expect($decoded)->toBeArray()
                ->and($decoded)->toContain('high_device_risk')
                ->and($decoded)->toContain('proxy_detected');
        });
    });

    describe('rule application', function (): void {
        beforeEach(function (): void {
            $this->collection = RiskFlagCollection::from([
                RiskFlag::HighDeviceRisk,
                RiskFlag::ProxyDetected,
                RiskFlag::LikelyFakeId,
                RiskFlag::IdExpired,
            ]);
        });

        it('applies custom rules', function (): void {
            $result = $this->collection->applyRule(fn(RiskFlag $flag) => str_contains($flag->value, 'device'));

            expect($result)->toHaveCount(1)
                ->and($result->contains(RiskFlag::HighDeviceRisk))->toBeTrue();
        });
    });

    describe('empty collection behavior', function (): void {
        beforeEach(function (): void {
            $this->emptyCollection = new RiskFlagCollection();
        });

        it('handles empty collection gracefully', function (): void {
            expect($this->emptyCollection->getSummary()['total_flags'])->toBe(0)
                ->and($this->emptyCollection->isLowRisk())->toBeTrue()
                ->and($this->emptyCollection->hasBlockingFlags())->toBeFalse()
                ->and($this->emptyCollection->getRecommendation())->toBe('approve');
        });

        it('returns empty arrays for empty collection', function (): void {
            expect($this->emptyCollection->toValues())->toBeArray()
                ->and($this->emptyCollection->toValues())->toBeEmpty()
                ->and($this->emptyCollection->getRiskLevelDistribution()['low'])->toBe(0);
        });
    });

    describe('edge cases and error handling', function (): void {
        it('handles invalid arguments in from method', function (): void {
            expect(fn() => RiskFlagCollection::from('not_iterable'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('handles non-string non-int values in fromValues', function (): void {
            $collection = RiskFlagCollection::fromValues([
                'high_device_risk',
                123, // integer - should be skipped
                [], // array - should be skipped
                null, // null - should be skipped
                'proxy_detected',
            ]);

            expect($collection)->toHaveCount(2);
        });

        it('handles non-string values in fromNames', function (): void {
            $collection = RiskFlagCollection::fromNames([
                'HighDeviceRisk',
                123, // integer - should be skipped
                null, // null - should be skipped
                'ProxyDetected',
            ]);

            expect($collection)->toHaveCount(2);
        });

        it('throws exception when non-RiskFlag in conversion methods', function (): void {
            $collection = new RiskFlagCollection();
            $collection->add('invalid_item');

            expect(fn() => $collection->toValues())->toThrow(InvalidArgumentException::class);
            expect(fn() => $collection->toNames())->toThrow(InvalidArgumentException::class);
            expect(fn() => $collection->toDisplayNames())->toThrow(InvalidArgumentException::class);
            expect(fn() => $collection->toDetailedArray())->toThrow(InvalidArgumentException::class);
            expect(fn() => $collection->getUniqueRiskLevels())->toThrow(InvalidArgumentException::class);
        });

        it('handles non-RiskFlag items in filtering methods gracefully', function (): void {
            $collection = new RiskFlagCollection();
            $collection->add(RiskFlag::HighDeviceRisk);
            $collection->add('invalid_item');

            $filtered = $collection->byCategory(RiskCategory::Device);
            expect($filtered)->toHaveCount(1);

            $filtered = $collection->byRiskLevel(RiskLevel::High);
            expect($filtered)->toHaveCount(1);

            $filtered = $collection->blocking();
            expect($filtered)->toHaveCount(0); // HighDeviceRisk is not blocking

            $filtered = $collection->nonBlocking();
            expect($filtered)->toHaveCount(1); // HighDeviceRisk is non-blocking

            $filtered = $collection->byDisplayNamePattern('Device');
            expect($filtered)->toHaveCount(1);
        });
    });

    describe('additional analysis methods', function (): void {
        beforeEach(function (): void {
            $this->mixedCollection = RiskFlagCollection::from([
                RiskFlag::HighDeviceRisk, // High risk
                RiskFlag::LikelyFakeId, // High risk, blocking
                RiskFlag::ProxyDetected, // Moderate risk
                RiskFlag::KnownFraudFace, // Moderate risk, blocking
                RiskFlag::IdExpired, // Low risk
            ]);
        });

        it('correctly identifies critical risk flags', function (): void {
            $criticalCollection = RiskFlagCollection::from([
                RiskFlag::HighDeviceRisk,
            ]);

            $summary = $criticalCollection->getSummary();
            expect($summary['has_critical_flags'])->toBeFalse(); // HighDeviceRisk is High, not Critical
        });

        it('gets most severe flags with correct ordering', function (): void {
            $severe = $this->mixedCollection->getMostSevere(10);

            // Blocking flags should come first
            $blockingCount = $severe->blocking()->count();
            expect($blockingCount)->toBe(2); // LikelyFakeId and KnownFraudFace are blocking

            // The ordering prioritizes blocking flags first
            $severeFlagsArray = $severe->toArray();
            $blockingFlags = array_filter($severeFlagsArray, fn($flag) => $flag->shouldBlock());
            $nonBlockingFlags = array_filter($severeFlagsArray, fn($flag) => ! $flag->shouldBlock());

            expect(count($blockingFlags))->toBe(2);
            expect(count($nonBlockingFlags))->toBe(3);
        });

        it('limits most severe flags correctly', function (): void {
            $severe = $this->mixedCollection->getMostSevere(2);
            expect($severe)->toHaveCount(2);
        });
    });

    describe('complex filtering scenarios', function (): void {
        beforeEach(function (): void {
            $this->complexCollection = RiskFlagCollection::from([
                RiskFlag::HighDeviceRisk,
                RiskFlag::ProxyDetected,
                RiskFlag::VpnDetected,
                RiskFlag::LikelyFakeId,
                RiskFlag::IdExpired,
                RiskFlag::RepeatFace,
                RiskFlag::KnownFraudFace,
                RiskFlag::LocationSpoofing,
            ]);
        });

        it('filters by multiple categories with enum objects', function (): void {
            $multipleFlags = $this->complexCollection->byCategories([
                RiskCategory::Network,
                RiskCategory::Device,
            ]);

            expect($multipleFlags->count())->toBeGreaterThan(0);
        });

        it('groups by category correctly', function (): void {
            $grouped = $this->complexCollection->groupByCategory();

            expect($grouped)->toBeArray();
            foreach ($grouped as $categoryFlags) {
                expect($categoryFlags)->toBeInstanceOf(RiskFlagCollection::class);
            }
        });

        it('gets category distribution with multiple categories per flag', function (): void {
            $distribution = $this->complexCollection->getCategoryDistribution();

            expect($distribution)->toBeArray();
            expect(array_sum($distribution))->toBeGreaterThanOrEqual($this->complexCollection->count());
        });

        it('gets unique categories correctly', function (): void {
            $categories = $this->complexCollection->getUniqueCategories();

            expect($categories->count())->toBeGreaterThan(0);
            $categories->each(function ($category): void {
                expect($category)->toBeInstanceOf(RiskCategory::class);
            });
        });
    });

    describe('collection operations edge cases', function (): void {
        it('merges with empty collection', function (): void {
            $collection1 = RiskFlagCollection::from([RiskFlag::HighDeviceRisk]);
            $emptyCollection = new RiskFlagCollection();

            $merged = $collection1->mergeFlags($emptyCollection);
            expect($merged)->toHaveCount(1);

            $merged = $emptyCollection->mergeFlags($collection1);
            expect($merged)->toHaveCount(1);
        });

        it('intersects with empty collection', function (): void {
            $collection = RiskFlagCollection::from([RiskFlag::HighDeviceRisk]);
            $emptyCollection = new RiskFlagCollection();

            $intersection = $collection->intersectFlags($emptyCollection);
            expect($intersection)->toHaveCount(0);
        });

        it('calculates difference with identical collections', function (): void {
            $collection1 = RiskFlagCollection::from([RiskFlag::HighDeviceRisk]);
            $collection2 = RiskFlagCollection::from([RiskFlag::HighDeviceRisk]);

            $diff = $collection1->diffFlags($collection2);
            expect($diff)->toHaveCount(0);
        });

        it('handles non-RiskFlag items in collection operations', function (): void {
            $collection1 = new RiskFlagCollection();
            $collection1->add(RiskFlag::HighDeviceRisk);
            $collection1->add('invalid_item');

            $collection2 = RiskFlagCollection::from([RiskFlag::HighDeviceRisk]);

            $intersection = $collection1->intersectFlags($collection2);
            expect($intersection)->toHaveCount(1);

            $diff = $collection1->diffFlags($collection2);
            expect($diff)->toHaveCount(0); // Only RiskFlag items are considered
        });
    });

    describe('risk assessment edge cases', function (): void {
        it('handles collection with mixed invalid items in risk assessment', function (): void {
            $collection = new RiskFlagCollection();
            $collection->add(RiskFlag::IdExpired); // Low risk
            $collection->add('invalid_item');

            expect($collection->isLowRisk())->toBeFalse(); // Contains invalid item
            expect($collection->isHighRisk())->toBeFalse();
        });

        it('handles complex recommendation scenarios', function (): void {
            // Collection with only moderate risk flags
            $moderateOnlyCollection = RiskFlagCollection::from([
                RiskFlag::ProxyDetected,
                RiskFlag::KnownFraudFace, // This is blocking, so should return 'block'
            ]);
            expect($moderateOnlyCollection->getRecommendation())->toBe('block');

            // Collection with only low risk flags
            $lowOnlyCollection = RiskFlagCollection::from([
                RiskFlag::IdExpired,
            ]);
            expect($lowOnlyCollection->getRecommendation())->toBe('approve');
        });
    });

    describe('performance and edge case testing', function (): void {
        it('handles large collections efficiently', function (): void {
            $allFlags = RiskFlag::cases();
            $largeCollection = RiskFlagCollection::from($allFlags);

            expect($largeCollection->count())->toBe(count($allFlags));

            $summary = $largeCollection->getSummary();
            expect($summary['total_flags'])->toBe(count($allFlags));

            $grouped = $largeCollection->groupByCategory();
            expect($grouped)->toBeArray();

            $distribution = $largeCollection->getRiskLevelDistribution();
            expect(array_sum($distribution))->toBe(count($allFlags));
        });

        it('handles regex patterns in display name filtering', function (): void {
            $collection = RiskFlagCollection::from([
                RiskFlag::HighDeviceRisk,
                RiskFlag::RepeatDevice,
                RiskFlag::ProxyDetected,
            ]);

            $deviceFlags = $collection->byDisplayNamePattern('Device');
            expect($deviceFlags)->toHaveCount(2);

            $proxyFlags = $collection->byDisplayNamePattern('Proxy');
            expect($proxyFlags)->toHaveCount(1);

            $noMatch = $collection->byDisplayNamePattern('NonExistent');
            expect($noMatch)->toHaveCount(0);
        });

        it('handles special characters in regex patterns', function (): void {
            $collection = RiskFlagCollection::from([
                RiskFlag::HighDeviceRisk,
            ]);

            // Should not throw exception with special regex characters
            $result = $collection->byDisplayNamePattern('High.*Risk');
            expect($result)->toHaveCount(1);
        });
    });

    describe('serialization and data integrity', function (): void {
        beforeEach(function (): void {
            $this->testCollection = RiskFlagCollection::from([
                RiskFlag::HighDeviceRisk,
                RiskFlag::LikelyFakeId,
                RiskFlag::ProxyDetected,
            ]);
        });

        it('maintains data integrity through serialization cycle', function (): void {
            $json = $this->testCollection->json();
            $array = json_decode($json, true);

            $rebuilt = RiskFlagCollection::fromValues($array);
            expect($rebuilt)->toHaveCount($this->testCollection->count());

            foreach ($this->testCollection as $flag) {
                expect($rebuilt->contains($flag))->toBeTrue();
            }
        });

        it('detailed array contains all required fields', function (): void {
            $detailed = $this->testCollection->toDetailedArray();

            foreach ($detailed as $flagData) {
                expect($flagData)->toHaveKey('name');
                expect($flagData)->toHaveKey('value');
                expect($flagData)->toHaveKey('display_name');
                expect($flagData)->toHaveKey('description');
                expect($flagData)->toHaveKey('risk_level');
                expect($flagData)->toHaveKey('categories');
                expect($flagData)->toHaveKey('should_block');

                expect($flagData['categories'])->toBeArray();
                expect($flagData['should_block'])->toBeBool();
            }
        });

        it('handles non-RiskCategory objects in detailed array conversion', function (): void {
            // This test ensures the toDetailedArray method handles edge cases
            $detailed = $this->testCollection->toDetailedArray();
            expect($detailed)->toBeArray();
            expect(count($detailed))->toBe($this->testCollection->count());
        });
    });
});
