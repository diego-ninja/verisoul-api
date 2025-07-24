<?php

use Ninja\Verisoul\Enums\VerisoulDecision;

describe('VerisoulDecision Enum', function () {
    describe('enum cases', function () {
        it('has all expected cases', function () {
            $cases = VerisoulDecision::cases();
            $values = array_map(fn($case) => $case->value, $cases);

            expect($cases)->toHaveCount(4)
                ->and($values)->toContain('Fake')
                ->and($values)->toContain('Suspicious')
                ->and($values)->toContain('Real')
                ->and($values)->toContain('Unknown');
        });

        it('has correct enum values', function () {
            expect(VerisoulDecision::Fake->value)->toBe('Fake')
                ->and(VerisoulDecision::Suspicious->value)->toBe('Suspicious')
                ->and(VerisoulDecision::Real->value)->toBe('Real')
                ->and(VerisoulDecision::Unknown->value)->toBe('Unknown');
        });

        it('uses proper capitalization', function () {
            // Values should be capitalized as they come from API
            expect(VerisoulDecision::Fake->value)->toBe('Fake')
                ->and(VerisoulDecision::Suspicious->value)->toBe('Suspicious')
                ->and(VerisoulDecision::Real->value)->toBe('Real')
                ->and(VerisoulDecision::Unknown->value)->toBe('Unknown');
        });

        it('can be created from string values', function () {
            expect(VerisoulDecision::from('Fake'))->toBe(VerisoulDecision::Fake)
                ->and(VerisoulDecision::from('Suspicious'))->toBe(VerisoulDecision::Suspicious)
                ->and(VerisoulDecision::from('Real'))->toBe(VerisoulDecision::Real)
                ->and(VerisoulDecision::from('Unknown'))->toBe(VerisoulDecision::Unknown);
        });

        it('can try to create from string values', function () {
            expect(VerisoulDecision::tryFrom('Real'))->toBe(VerisoulDecision::Real)
                ->and(VerisoulDecision::tryFrom('Fake'))->toBe(VerisoulDecision::Fake)
                ->and(VerisoulDecision::tryFrom('invalid'))->toBeNull();
        });
    });

    describe('values method', function () {
        it('returns all enum values as array', function () {
            $values = VerisoulDecision::values();

            expect($values)->toBeArray()
                ->and($values)->toHaveCount(4)
                ->and($values)->toContain('Fake')
                ->and($values)->toContain('Suspicious')
                ->and($values)->toContain('Real')
                ->and($values)->toContain('Unknown');
        });

        it('maintains correct order', function () {
            $values = VerisoulDecision::values();

            expect($values[0])->toBe('Fake')
                ->and($values[1])->toBe('Suspicious')
                ->and($values[2])->toBe('Real')
                ->and($values[3])->toBe('Unknown');
        });

        it('provides values suitable for API communication', function () {
            $values = VerisoulDecision::values();

            foreach ($values as $value) {
                // All values should be properly capitalized strings
                expect($value)->toBeString()
                    ->and(ucfirst($value))->toBe($value);
            }
        });
    });

    describe('decision semantics', function () {
        it('represents clear positive decision', function () {
            $real = VerisoulDecision::Real;

            expect($real->value)->toBe('Real')
                ->and($real)->toBe(VerisoulDecision::Real);
        });

        it('represents clear negative decision', function () {
            $fake = VerisoulDecision::Fake;

            expect($fake->value)->toBe('Fake')
                ->and($fake)->toBe(VerisoulDecision::Fake);
        });

        it('represents uncertain negative decision', function () {
            $suspicious = VerisoulDecision::Suspicious;

            expect($suspicious->value)->toBe('Suspicious')
                ->and($suspicious)->toBe(VerisoulDecision::Suspicious);
        });

        it('represents inconclusive decision', function () {
            $unknown = VerisoulDecision::Unknown;

            expect($unknown->value)->toBe('Unknown')
                ->and($unknown)->toBe(VerisoulDecision::Unknown);
        });
    });

    describe('decision ordering and priority', function () {
        it('supports risk-based ordering', function () {
            // From highest risk to lowest risk
            $riskOrdering = [
                VerisoulDecision::Fake,      // Definitely fraudulent
                VerisoulDecision::Suspicious, // Potentially fraudulent
                VerisoulDecision::Unknown,    // Inconclusive
                VerisoulDecision::Real,       // Legitimate
            ];

            expect($riskOrdering)->toHaveCount(4);
            
            // Verify all decisions are included
            foreach (VerisoulDecision::cases() as $decision) {
                expect(in_array($decision, $riskOrdering, true))->toBeTrue();
            }
        });

        it('can be used for decision comparison logic', function () {
            $testDecisions = [
                VerisoulDecision::Real,
                VerisoulDecision::Suspicious,
                VerisoulDecision::Fake,
                VerisoulDecision::Unknown,
            ];

            foreach ($testDecisions as $decision) {
                expect($decision)->toBeInstanceOf(VerisoulDecision::class);
            }
        });
    });

    describe('business logic applications', function () {
        it('supports allow/deny decision making', function () {
            $allowDecisions = [VerisoulDecision::Real];
            $denyDecisions = [VerisoulDecision::Fake, VerisoulDecision::Suspicious];
            $reviewDecisions = [VerisoulDecision::Unknown];

            // Test allow logic
            expect(in_array(VerisoulDecision::Real, $allowDecisions))->toBeTrue()
                ->and(in_array(VerisoulDecision::Fake, $allowDecisions))->toBeFalse();

            // Test deny logic
            expect(in_array(VerisoulDecision::Fake, $denyDecisions))->toBeTrue()
                ->and(in_array(VerisoulDecision::Suspicious, $denyDecisions))->toBeTrue()
                ->and(in_array(VerisoulDecision::Real, $denyDecisions))->toBeFalse();

            // Test review logic
            expect(in_array(VerisoulDecision::Unknown, $reviewDecisions))->toBeTrue()
                ->and(in_array(VerisoulDecision::Real, $reviewDecisions))->toBeFalse();
        });

        it('supports confidence level assessment', function () {
            $highConfidenceDecisions = [VerisoulDecision::Real, VerisoulDecision::Fake];
            $lowConfidenceDecisions = [VerisoulDecision::Suspicious, VerisoulDecision::Unknown];

            // High confidence decisions
            expect(in_array(VerisoulDecision::Real, $highConfidenceDecisions))->toBeTrue()
                ->and(in_array(VerisoulDecision::Fake, $highConfidenceDecisions))->toBeTrue();

            // Low confidence decisions
            expect(in_array(VerisoulDecision::Suspicious, $lowConfidenceDecisions))->toBeTrue()
                ->and(in_array(VerisoulDecision::Unknown, $lowConfidenceDecisions))->toBeTrue();
        });

        it('enables risk-based routing', function () {
            $decision = VerisoulDecision::Suspicious;
            
            $action = match ($decision) {
                VerisoulDecision::Real => 'approve',
                VerisoulDecision::Fake => 'deny',
                VerisoulDecision::Suspicious => 'review',
                VerisoulDecision::Unknown => 'manual_check',
            };

            expect($action)->toBe('review');
        });
    });

    describe('enum behavior', function () {
        it('supports comparison operations', function () {
            expect(VerisoulDecision::Real === VerisoulDecision::Real)->toBeTrue()
                ->and(VerisoulDecision::Real === VerisoulDecision::Fake)->toBeFalse()
                ->and(VerisoulDecision::Real !== VerisoulDecision::Fake)->toBeTrue();
        });

        it('can be used in match expressions', function () {
            $decision = VerisoulDecision::Suspicious;
            
            $riskLevel = match ($decision) {
                VerisoulDecision::Real => 'low',
                VerisoulDecision::Unknown => 'medium',
                VerisoulDecision::Suspicious => 'high',
                VerisoulDecision::Fake => 'critical',
            };

            expect($riskLevel)->toBe('high');
        });

        it('can be used in conditional logic', function () {
            $decision = VerisoulDecision::Fake;
            
            $isTrusted = match ($decision) {
                VerisoulDecision::Real => true,
                default => false,
            };

            $isRisky = in_array($decision, [
                VerisoulDecision::Fake, 
                VerisoulDecision::Suspicious
            ]);

            expect($isTrusted)->toBeFalse()
                ->and($isRisky)->toBeTrue();
        });

        it('can be used in arrays', function () {
            $negativeDecisions = [
                VerisoulDecision::Fake,
                VerisoulDecision::Suspicious,
            ];

            expect($negativeDecisions)->toHaveCount(2)
                ->and(in_array(VerisoulDecision::Fake, $negativeDecisions))->toBeTrue()
                ->and(in_array(VerisoulDecision::Real, $negativeDecisions))->toBeFalse();
        });

        it('supports serialization', function () {
            $decision = VerisoulDecision::Suspicious;
            $serialized = serialize($decision);
            $unserialized = unserialize($serialized);

            expect($unserialized)->toBe(VerisoulDecision::Suspicious)
                ->and($unserialized->value)->toBe('Suspicious');
        });
    });

    describe('API integration', function () {
        it('handles API response values correctly', function () {
            // Simulate typical API responses
            $apiResponses = [
                'Real' => VerisoulDecision::Real,
                'Fake' => VerisoulDecision::Fake,
                'Suspicious' => VerisoulDecision::Suspicious,
                'Unknown' => VerisoulDecision::Unknown,
            ];

            foreach ($apiResponses as $apiValue => $expectedDecision) {
                $decision = VerisoulDecision::from($apiValue);
                expect($decision)->toBe($expectedDecision);
            }
        });

        it('supports API value validation', function () {
            $validApiValues = VerisoulDecision::values();
            
            foreach ($validApiValues as $value) {
                expect(VerisoulDecision::tryFrom($value))->not->toBeNull();
            }

            // Invalid values should return null
            expect(VerisoulDecision::tryFrom('invalid'))->toBeNull()
                ->and(VerisoulDecision::tryFrom('real'))->toBeNull() // lowercase
                ->and(VerisoulDecision::tryFrom('FAKE'))->toBeNull(); // uppercase
        });
    });

    describe('validation and error handling', function () {
        it('throws exception for invalid string values', function () {
            expect(fn() => VerisoulDecision::from('invalid'))
                ->toThrow(ValueError::class);
        });

        it('handles case sensitivity correctly', function () {
            // Only exact case matches should work
            expect(VerisoulDecision::tryFrom('real'))->toBeNull()
                ->and(VerisoulDecision::tryFrom('REAL'))->toBeNull()
                ->and(VerisoulDecision::tryFrom('Real'))->toBe(VerisoulDecision::Real);

            expect(VerisoulDecision::tryFrom('fake'))->toBeNull()
                ->and(VerisoulDecision::tryFrom('FAKE'))->toBeNull()
                ->and(VerisoulDecision::tryFrom('Fake'))->toBe(VerisoulDecision::Fake);
        });

    });

    describe('decision workflow support', function () {
        it('supports fraud detection workflow', function () {
            $testScenarios = [
                ['decision' => VerisoulDecision::Real, 'action' => 'approve'],
                ['decision' => VerisoulDecision::Fake, 'action' => 'reject'],
                ['decision' => VerisoulDecision::Suspicious, 'action' => 'flag'],
                ['decision' => VerisoulDecision::Unknown, 'action' => 'investigate'],
            ];

            foreach ($testScenarios as $scenario) {
                $decision = $scenario['decision'];
                $expectedAction = $scenario['action'];

                $action = match ($decision) {
                    VerisoulDecision::Real => 'approve',
                    VerisoulDecision::Fake => 'reject',
                    VerisoulDecision::Suspicious => 'flag',
                    VerisoulDecision::Unknown => 'investigate',
                };

                expect($action)->toBe($expectedAction);
            }
        });

        it('supports decision aggregation', function () {
            $decisions = [
                VerisoulDecision::Real,
                VerisoulDecision::Suspicious,
                VerisoulDecision::Fake,
                VerisoulDecision::Unknown,
            ];

            // Count decisions by type
            $counts = [];
            foreach ($decisions as $decision) {
                $counts[$decision->value] = ($counts[$decision->value] ?? 0) + 1;
            }

            expect($counts)->toHaveKey('Real')
                ->and($counts)->toHaveKey('Suspicious')
                ->and($counts)->toHaveKey('Fake')
                ->and($counts)->toHaveKey('Unknown')
                ->and(array_sum($counts))->toBe(4);
        });
    });

    describe('string representation', function () {
        it('converts to string correctly', function () {
            expect(VerisoulDecision::Real->value)->toBe('Real')
                ->and(VerisoulDecision::Fake->value)->toBe('Fake')
                ->and(VerisoulDecision::Suspicious->value)->toBe('Suspicious')
                ->and(VerisoulDecision::Unknown->value)->toBe('Unknown');
        });

        it('provides API-compatible string values', function () {
            foreach (VerisoulDecision::cases() as $decision) {
                expect($decision->value)->toBeString()
                    ->and(strlen($decision->value))->toBeGreaterThan(0);
            }
        });

        it('maintains consistent capitalization', function () {
            foreach (VerisoulDecision::cases() as $decision) {
                $value = $decision->value;
                expect($value[0])->toBe(strtoupper($value[0])); // First letter capitalized
                expect(substr($value, 1))->toBe(strtolower(substr($value, 1))); // Rest lowercase
            }
        });
    });
});