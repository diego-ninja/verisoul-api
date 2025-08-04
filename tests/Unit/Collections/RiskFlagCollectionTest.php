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
});
