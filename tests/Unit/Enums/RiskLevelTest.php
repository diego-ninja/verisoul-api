<?php

use Ninja\Verisoul\Enums\RiskLevel;

describe('RiskLevel Enum', function (): void {
    describe('enum cases', function (): void {
        it('has all expected cases', function (): void {
            $cases = RiskLevel::cases();
            $values = array_map(fn($case) => $case->value, $cases);

            expect($cases)->toHaveCount(5)
                ->and($values)->toContain('low')
                ->and($values)->toContain('moderate')
                ->and($values)->toContain('high')
                ->and($values)->toContain('critical')
                ->and($values)->toContain('unknown');
        });

        it('has correct enum values', function (): void {
            expect(RiskLevel::Low->value)->toBe('low')
                ->and(RiskLevel::Moderate->value)->toBe('moderate')
                ->and(RiskLevel::High->value)->toBe('high')
                ->and(RiskLevel::Critical->value)->toBe('critical')
                ->and(RiskLevel::Unknown->value)->toBe('unknown');
        });

        it('can be created from string values', function (): void {
            expect(RiskLevel::from('low'))->toBe(RiskLevel::Low)
                ->and(RiskLevel::from('moderate'))->toBe(RiskLevel::Moderate)
                ->and(RiskLevel::from('high'))->toBe(RiskLevel::High)
                ->and(RiskLevel::from('critical'))->toBe(RiskLevel::Critical)
                ->and(RiskLevel::from('unknown'))->toBe(RiskLevel::Unknown);
        });

        it('can try to create from string values', function (): void {
            expect(RiskLevel::tryFrom('low'))->toBe(RiskLevel::Low)
                ->and(RiskLevel::tryFrom('invalid'))->toBeNull();
        });
    });

    describe('values method', function (): void {
        it('returns all enum values as array', function (): void {
            $values = RiskLevel::values();

            expect($values)->toBeArray()
                ->and($values)->toHaveCount(5)
                ->and($values)->toContain('low')
                ->and($values)->toContain('moderate')
                ->and($values)->toContain('high')
                ->and($values)->toContain('critical')
                ->and($values)->toContain('unknown');
        });

        it('maintains correct order', function (): void {
            $values = RiskLevel::values();

            expect($values[0])->toBe('low')
                ->and($values[1])->toBe('moderate')
                ->and($values[2])->toBe('high')
                ->and($values[3])->toBe('critical')
                ->and($values[4])->toBe('unknown');
        });
    });

    describe('withScore method', function (): void {
        it('works with Score objects', function (): void {
            $lowScore = new Ninja\Verisoul\ValueObjects\Score(0.3);
            $moderateScore = new Ninja\Verisoul\ValueObjects\Score(0.5);
            $highScore = new Ninja\Verisoul\ValueObjects\Score(0.8);
            $criticalScore = new Ninja\Verisoul\ValueObjects\Score(0.95);
            $unknownScore = new Ninja\Verisoul\ValueObjects\Score(0.0);

            expect(RiskLevel::withScore($lowScore))->toBe(RiskLevel::Low)
                ->and(RiskLevel::withScore($moderateScore))->toBe(RiskLevel::Moderate)
                ->and(RiskLevel::withScore($highScore))->toBe(RiskLevel::High)
                ->and(RiskLevel::withScore($criticalScore))->toBe(RiskLevel::Critical)
                ->and(RiskLevel::withScore($unknownScore))->toBe(RiskLevel::Unknown);
        });

        it('returns Critical for scores above critical threshold', function (): void {
            expect(RiskLevel::withScore(0.95))->toBe(RiskLevel::Critical)
                ->and(RiskLevel::withScore(0.9))->toBe(RiskLevel::Critical)
                ->and(RiskLevel::withScore(1.0))->toBe(RiskLevel::Critical);
        });

        it('returns High for scores above high threshold', function (): void {
            expect(RiskLevel::withScore(0.85))->toBe(RiskLevel::High)
                ->and(RiskLevel::withScore(0.7))->toBe(RiskLevel::High)
                ->and(RiskLevel::withScore(0.89))->toBe(RiskLevel::High);
        });

        it('returns Medium for scores above moderate threshold', function (): void {
            expect(RiskLevel::withScore(0.6))->toBe(RiskLevel::Moderate)
                ->and(RiskLevel::withScore(0.4))->toBe(RiskLevel::Moderate)
                ->and(RiskLevel::withScore(0.69))->toBe(RiskLevel::Moderate);
        });

        it('returns Low for positive scores below moderate threshold', function (): void {
            expect(RiskLevel::withScore(0.3))->toBe(RiskLevel::Low)
                ->and(RiskLevel::withScore(0.1))->toBe(RiskLevel::Low)
                ->and(RiskLevel::withScore(0.01))->toBe(RiskLevel::Low);
        });

        it('returns Unknown for zero or negative scores', function (): void {
            expect(RiskLevel::withScore(0.0))->toBe(RiskLevel::Unknown)
                ->and(RiskLevel::withScore(-0.1))->toBe(RiskLevel::Unknown)
                ->and(RiskLevel::withScore(-1.0))->toBe(RiskLevel::Unknown);
        });

        it('handles edge cases correctly', function (): void {
            // Test boundary values
            expect(RiskLevel::withScore(0.00001))->toBe(RiskLevel::Low) // Just above 0
                ->and(RiskLevel::withScore(0.39999))->toBe(RiskLevel::Low) // Just below moderate
                ->and(RiskLevel::withScore(0.69999))->toBe(RiskLevel::Moderate) // Just below high
                ->and(RiskLevel::withScore(0.89999))->toBe(RiskLevel::High); // Just below critical
        });

        it('handles precision correctly', function (): void {
            expect(RiskLevel::withScore(0.4000001))->toBe(RiskLevel::Moderate)
                ->and(RiskLevel::withScore(0.7000001))->toBe(RiskLevel::High)
                ->and(RiskLevel::withScore(0.9000001))->toBe(RiskLevel::Critical);
        });
    });

    describe('enum behavior', function (): void {
        it('supports comparison operations', function (): void {
            expect(RiskLevel::Low === RiskLevel::Low)->toBeTrue()
                ->and(RiskLevel::Low === RiskLevel::Moderate)->toBeFalse()
                ->and(RiskLevel::Low !== RiskLevel::Moderate)->toBeTrue();
        });

        it('can be used in match expressions', function (): void {
            $level = RiskLevel::High;

            $message = match ($level) {
                RiskLevel::Low => 'Low risk',
                RiskLevel::Moderate => 'Medium risk',
                RiskLevel::High => 'High risk',
                RiskLevel::Critical => 'Critical risk',
                RiskLevel::Unknown => 'Unknown risk',
            };

            expect($message)->toBe('High risk');
        });

        it('can be used in arrays', function (): void {
            $levels = [RiskLevel::Low, RiskLevel::High, RiskLevel::Critical];

            expect($levels)->toHaveCount(3)
                ->and(in_array(RiskLevel::Low, $levels))->toBeTrue()
                ->and(in_array(RiskLevel::Moderate, $levels))->toBeFalse();
        });

        it('supports serialization', function (): void {
            $level = RiskLevel::Critical;
            $serialized = serialize($level);
            $unserialized = unserialize($serialized);

            expect($unserialized)->toBe(RiskLevel::Critical)
                ->and($unserialized->value)->toBe('critical');
        });
    });

    describe('integration with risk assessment', function (): void {
        it('can categorize various risk scores', function (): void {
            $testScores = [
                [0.05, RiskLevel::Low],
                [0.25, RiskLevel::Low],
                [0.45, RiskLevel::Moderate],
                [0.65, RiskLevel::Moderate],
                [0.75, RiskLevel::High],
                [0.85, RiskLevel::High],
                [0.95, RiskLevel::Critical],
                [1.0, RiskLevel::Critical],
                [0.0, RiskLevel::Unknown],
                [-0.1, RiskLevel::Unknown],
            ];

            foreach ($testScores as [$score, $expectedLevel]) {
                expect(RiskLevel::withScore($score))->toBe($expectedLevel);
            }
        });

        it('provides consistent risk assessment', function (): void {
            // Test that same score always returns same level
            $score = 0.75;
            $level1 = RiskLevel::withScore($score);
            $level2 = RiskLevel::withScore($score);

            expect($level1)->toBe($level2)
                ->and($level1)->toBe(RiskLevel::High);
        });
    });

    describe('validation and error handling', function (): void {
        it('throws exception for invalid string values', function (): void {
            expect(fn() => RiskLevel::from('invalid'))
                ->toThrow(ValueError::class);
        });

        it('handles extreme score values', function (): void {
            expect(RiskLevel::withScore(PHP_FLOAT_MAX))->toBe(RiskLevel::Critical)
                ->and(RiskLevel::withScore(-PHP_FLOAT_MAX))->toBe(RiskLevel::Unknown)
                ->and(RiskLevel::withScore(INF))->toBe(RiskLevel::Critical)
                ->and(RiskLevel::withScore(-INF))->toBe(RiskLevel::Unknown);
        });

        it('handles NaN values gracefully', function (): void {
            // NaN comparisons are always false, so should fall through to Unknown
            expect(RiskLevel::withScore(NAN))->toBe(RiskLevel::Unknown);
        });
    });

    describe('string representation', function (): void {
        it('converts to string correctly', function (): void {
            expect(RiskLevel::Low->value)->toBe('low')
                ->and(RiskLevel::Moderate->value)->toBe('moderate')
                ->and(RiskLevel::High->value)->toBe('high')
                ->and(RiskLevel::Critical->value)->toBe('critical')
                ->and(RiskLevel::Unknown->value)->toBe('unknown');
        });

        it('can be cast to string', function (): void {
            expect(RiskLevel::Critical->value)->toBe('critical');
        });
    });
});
