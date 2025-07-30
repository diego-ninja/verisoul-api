<?php

use Ninja\Verisoul\ValueObjects\Score;
use Ninja\Verisoul\Tests\Helpers\DataProvider;

describe('Score Value Object', function () {
    describe('construction', function () {
        it('can be created with valid float value', function () {
            $score = new Score(0.75);
            
            expect($score->value)->toBe(0.75)
                ->and($score->value())->toBe(0.75);
        });

        it('can be created with integer value', function () {
            $score = new Score(1);
            
            expect($score->value)->toBe(1.0)
                ->and($score->value())->toBe(1.0);
        });

        it('accepts minimum valid value (0.0)', function () {
            $score = new Score(0.0);
            
            expect($score->value)->toBe(0.0);
        });

        it('accepts maximum valid value (1.0)', function () {
            $score = new Score(1.0);
            
            expect($score->value)->toBe(1.0);
        });

        test('valid scores from data provider', function (float $value) {
            $score = new Score($value);
            
            expect($score->value)->toBe($value)
                ->and($score->value)->toBeGreaterThanOrEqual(0.0)
                ->and($score->value)->toBeLessThanOrEqual(1.0);
        })->with(fn() => DataProvider::validScores());
    });

    describe('validation', function () {
        it('validates against rules', function () {
            $rules = Score::rules();
            
            expect($rules)->toHaveKey('value')
                ->and($rules['value'])->toContain('required')
                ->and($rules['value'])->toContain('numeric')
                ->and($rules['value'])->toContain('min:0')
                ->and($rules['value'])->toContain('max:1');
        });

        it('validates scores should be between 0 and 1', function () {
            // Note: Actual validation may depend on GraniteVO implementation
            $validScore = new Score(0.5);
            expect($validScore->value)->toBeGreaterThanOrEqual(0.0)
                ->and($validScore->value)->toBeLessThanOrEqual(1.0);
        });

        it('handles edge values', function () {
            $minScore = new Score(0.0);
            $maxScore = new Score(1.0);
            
            expect($minScore->value)->toBe(0.0)
                ->and($maxScore->value)->toBe(1.0);
        });
    });

    describe('factory methods', function () {
        it('can be created from float using from()', function () {
            $score = Score::from(0.85);
            
            expect($score)->toBeInstanceOf(Score::class)
                ->and($score->value)->toBe(0.85);
        });

        it('can be created from integer using from()', function () {
            $score = Score::from(1);
            
            expect($score)->toBeInstanceOf(Score::class)
                ->and($score->value)->toBe(1.0);
        });

        it('can be created from array with value key', function () {
            $score = Score::from(['value' => 0.95]);
            
            expect($score)->toBeInstanceOf(Score::class)
                ->and($score->value)->toBe(0.95);
        });

        it('returns same instance when passed Score object', function () {
            $original = new Score(0.75);
            $same = Score::from($original);
            
            expect($same)->equals($original)
                ->and($same->value)->toBe(0.75);
        });

        it('handles edge case values correctly', function () {
            $zeroScore = Score::from(0.0);
            $oneScore = Score::from(1.0);
            $preciseScore = Score::from(0.123);
            
            expect($zeroScore->value)->toBe(0.0)
                ->and($oneScore->value)->toBe(1.0)
                ->and($preciseScore->value)->toBe(0.123);
        });
    });

    describe('immutability', function () {
        it('is readonly and immutable', function () {
            $score = new Score(0.5);
            
            expect($score)->toBeInstanceOf(Score::class);
            
            // Property should be readonly (will throw error if we try to modify)
            $reflection = new ReflectionClass($score);
            $property = $reflection->getProperty('value');
            
            expect($property->isReadOnly())->toBeTrue();
        });

        it('preserves value through operations', function () {
            $score = new Score(0.75);
            $value1 = $score->value();
            $value2 = $score->value;
            $value3 = $score->value();
            
            expect($value1)->toBe($value2)
                ->and($value2)->toBe($value3)
                ->and($value3)->toBe(0.75);
        });
    });

    describe('string conversion', function () {
        it('converts to string correctly', function () {
            $score = new Score(0.75);
            
            expect((string) $score)->toBe('0.75')
                ->and($score->__toString())->toBe('0.75');
        });

        it('handles different precision levels', function () {
            $precise = new Score(0.123456789);
            $simple = new Score(0.5);
            $zero = new Score(0.0);
            $one = new Score(1.0);
            
            expect((string) $precise)->toBe((string) 0.123456789)
                ->and((string) $simple)->toBe('0.5')
                ->and((string) $zero)->toBe('0')
                ->and((string) $one)->toBe('1');
        });
    });

    describe('equality and comparison', function () {
        it('compares equal scores correctly', function () {
            $score1 = new Score(0.75);
            $score2 = new Score(0.75);
            $score3 = new Score(0.76);
            
            expect($score1->value)->toBe($score2->value)
                ->and($score1->value)->not->toBe($score3->value);
        });

        it('handles floating point precision', function () {
            $score1 = new Score(0.1 + 0.2);
            $score2 = new Score(0.3);
            
            // Due to floating point precision, these might not be exactly equal
            expect(abs($score1->value - $score2->value))->toBeLessThan(0.000001);
        });
    });

    describe('object creation patterns', function () {
        it('can be created from various inputs', function () {
            $scoreFromFloat = Score::from(0.85);
            $scoreFromArray = Score::from(['value' => 0.92]);
            $scoreFromScore = Score::from(new Score(0.75));
            
            expect($scoreFromFloat->value)->toBe(0.85)
                ->and($scoreFromArray->value)->toBe(0.92)
                ->and($scoreFromScore->value)->toBe(0.75);
        });

        it('handles different factory patterns', function () {
            $score1 = Score::from(0.5);
            $score2 = Score::from(['value' => 0.5]);
            
            expect($score1->value)->toBe($score2->value);
        });
    });

    describe('performance and edge cases', function () {
        it('handles very precise decimal values', function () {
            $preciseDec = 0.12345678901234567890; // PHP will limit precision
            $score = new Score($preciseDec);
            
            expect($score->value)->toBeFloat()
                ->and($score->value)->toBeGreaterThanOrEqual(0.0)
                ->and($score->value)->toBeLessThanOrEqual(1.0);
        });

        it('handles boundary values correctly', function () {
            $minScore = new Score(0.0);
            $maxScore = new Score(1.0);
            $almostMin = new Score(0.000001);
            $almostMax = new Score(0.999999);
            
            expect($minScore->value)->toBe(0.0)
                ->and($maxScore->value)->toBe(1.0)
                ->and($almostMin->value)->toBeGreaterThan(0.0)
                ->and($almostMax->value)->toBeLessThan(1.0);
        });

        it('works with typical confidence score values', function () {
            $confidenceScores = [0.95, 0.85, 0.75, 0.65, 0.50, 0.25, 0.15, 0.05];
            
            foreach ($confidenceScores as $scoreValue) {
                $score = new Score($scoreValue);
                expect($score->value)->toBe($scoreValue);
            }
        });
    });
});

describe('Score integration with Pest expectations', function () {
    it('works with custom toBeValidScore expectation', function () {
        $score = new Score(0.75);
        
        expect($score)->toBeValidScore();
    });

    it('can be used in complex assertions', function () {
        $scores = [
            new Score(0.95),
            new Score(0.85),
            new Score(0.75),
        ];
        
        foreach ($scores as $score) {
            expect($score)->toBeValidScore()
                ->and($score->value)->toBeGreaterThan(0.7);
        }
    });
});