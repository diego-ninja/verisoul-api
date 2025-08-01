<?php

use Ninja\Verisoul\Enums\VerisoulEnvironment;

describe('VerisoulEnvironment Enum', function (): void {
    describe('enum cases', function (): void {
        it('has all expected cases', function (): void {
            $cases = VerisoulEnvironment::cases();
            $values = array_map(fn($case) => $case->value, $cases);

            expect($cases)->toHaveCount(2)
                ->and($values)->toContain('sandbox')
                ->and($values)->toContain('production');
        });

        it('has correct enum values', function (): void {
            expect(VerisoulEnvironment::Sandbox->value)->toBe('sandbox')
                ->and(VerisoulEnvironment::Production->value)->toBe('production');
        });

        it('can be created from string values', function (): void {
            expect(VerisoulEnvironment::from('sandbox'))->toBe(VerisoulEnvironment::Sandbox)
                ->and(VerisoulEnvironment::from('production'))->toBe(VerisoulEnvironment::Production);
        });

        it('can try to create from string values', function (): void {
            expect(VerisoulEnvironment::tryFrom('sandbox'))->toBe(VerisoulEnvironment::Sandbox)
                ->and(VerisoulEnvironment::tryFrom('production'))->toBe(VerisoulEnvironment::Production)
                ->and(VerisoulEnvironment::tryFrom('invalid'))->toBeNull();
        });
    });

    describe('getBaseUrl method', function (): void {
        it('returns correct sandbox URL', function (): void {
            $url = VerisoulEnvironment::Sandbox->getBaseUrl();

            expect($url)->toBe('https://api.sandbox.verisoul.ai')
                ->and($url)->toStartWith('https://')
                ->and($url)->toContain('sandbox');
        });

        it('returns correct production URL', function (): void {
            $url = VerisoulEnvironment::Production->getBaseUrl();

            expect($url)->toBe('https://api.verisoul.ai')
                ->and($url)->toStartWith('https://')
                ->and($url)->not->toContain('sandbox');
        });

        it('URLs are properly formatted', function (): void {
            $sandboxUrl = VerisoulEnvironment::Sandbox->getBaseUrl();
            $productionUrl = VerisoulEnvironment::Production->getBaseUrl();

            // Both should be valid HTTPS URLs
            expect(filter_var($sandboxUrl, FILTER_VALIDATE_URL))->not->toBeFalse()
                ->and(filter_var($productionUrl, FILTER_VALIDATE_URL))->not->toBeFalse();

            // Should not end with trailing slash
            expect($sandboxUrl)->not->toEndWith('/')
                ->and($productionUrl)->not->toEndWith('/');

            // Should use HTTPS
            expect(parse_url($sandboxUrl, PHP_URL_SCHEME))->toBe('https')
                ->and(parse_url($productionUrl, PHP_URL_SCHEME))->toBe('https');
        });

        it('provides different URLs for different environments', function (): void {
            $sandboxUrl = VerisoulEnvironment::Sandbox->getBaseUrl();
            $productionUrl = VerisoulEnvironment::Production->getBaseUrl();

            expect($sandboxUrl)->not->toBe($productionUrl);
        });
    });

    describe('enum behavior', function (): void {
        it('supports comparison operations', function (): void {
            expect(VerisoulEnvironment::Sandbox === VerisoulEnvironment::Sandbox)->toBeTrue()
                ->and(VerisoulEnvironment::Sandbox === VerisoulEnvironment::Production)->toBeFalse()
                ->and(VerisoulEnvironment::Sandbox !== VerisoulEnvironment::Production)->toBeTrue();
        });

        it('can be used in match expressions', function (): void {
            $env = VerisoulEnvironment::Production;

            $description = match ($env) {
                VerisoulEnvironment::Sandbox => 'Testing environment',
                VerisoulEnvironment::Production => 'Live environment',
            };

            expect($description)->toBe('Live environment');
        });

        it('can be used in conditional logic', function (): void {
            $env = VerisoulEnvironment::Sandbox;
            $isProduction = VerisoulEnvironment::Production === $env;
            $isSandbox = VerisoulEnvironment::Sandbox === $env;

            expect($isProduction)->toBeFalse()
                ->and($isSandbox)->toBeTrue();
        });

        it('can be used in arrays', function (): void {
            $environments = [VerisoulEnvironment::Sandbox, VerisoulEnvironment::Production];

            expect($environments)->toHaveCount(2)
                ->and(in_array(VerisoulEnvironment::Sandbox, $environments))->toBeTrue()
                ->and(in_array(VerisoulEnvironment::Production, $environments))->toBeTrue();
        });

        it('supports serialization', function (): void {
            $env = VerisoulEnvironment::Production;
            $serialized = serialize($env);
            $unserialized = unserialize($serialized);

            expect($unserialized)->toBe(VerisoulEnvironment::Production)
                ->and($unserialized->value)->toBe('production')
                ->and($unserialized->getBaseUrl())->toBe('https://api.verisoul.ai');
        });
    });

    describe('integration scenarios', function (): void {
        it('provides correct URLs for API client configuration', function (): void {
            $configurations = [
                [VerisoulEnvironment::Sandbox, 'https://api.sandbox.verisoul.ai'],
                [VerisoulEnvironment::Production, 'https://api.verisoul.ai'],
            ];

            foreach ($configurations as [$env, $expectedUrl]) {
                expect($env->getBaseUrl())->toBe($expectedUrl);
            }
        });

        it('supports environment switching logic', function (): void {
            $startEnv = VerisoulEnvironment::Sandbox;
            $targetEnv = VerisoulEnvironment::Production;

            $startUrl = $startEnv->getBaseUrl();
            $targetUrl = $targetEnv->getBaseUrl();

            expect($startUrl)->toContain('sandbox')
                ->and($targetUrl)->not->toContain('sandbox')
                ->and($startUrl)->not->toBe($targetUrl);
        });

        it('handles environment detection patterns', function (): void {
            // Simulate common environment detection scenarios
            $testCases = [
                ['sandbox', VerisoulEnvironment::Sandbox],
                ['production', VerisoulEnvironment::Production],
                ['prod', null], // Should return null for tryFrom
                ['dev', null],  // Should return null for tryFrom
            ];

            foreach ($testCases as [$envString, $expectedEnum]) {
                $result = VerisoulEnvironment::tryFrom($envString);
                expect($result)->toBe($expectedEnum);
            }
        });
    });

    describe('URL construction and validation', function (): void {
        it('constructs valid API endpoints', function (): void {
            $sandboxBase = VerisoulEnvironment::Sandbox->getBaseUrl();
            $productionBase = VerisoulEnvironment::Production->getBaseUrl();

            // Test that we can construct valid endpoints
            $endpoints = ['/sessions', '/accounts', '/verify/phone'];

            foreach ($endpoints as $endpoint) {
                $sandboxUrl = $sandboxBase . $endpoint;
                $productionUrl = $productionBase . $endpoint;

                expect(filter_var($sandboxUrl, FILTER_VALIDATE_URL))->not->toBeFalse()
                    ->and(filter_var($productionUrl, FILTER_VALIDATE_URL))->not->toBeFalse();
            }
        });

        it('provides consistent URL structure', function (): void {
            $sandboxUrl = VerisoulEnvironment::Sandbox->getBaseUrl();
            $productionUrl = VerisoulEnvironment::Production->getBaseUrl();

            // Both should follow same pattern: https://api[.environment].domain.tld
            expect($sandboxUrl)->toMatch('/^https:\/\/api/')
                ->and($productionUrl)->toMatch('/^https:\/\/api/')
                ->and($sandboxUrl)->toEndWith('.verisoul.ai')
                ->and($productionUrl)->toEndWith('.verisoul.ai');
        });

        it('maintains URL immutability', function (): void {
            $env = VerisoulEnvironment::Sandbox;
            $url1 = $env->getBaseUrl();
            $url2 = $env->getBaseUrl();

            expect($url1)->toBe($url2)
                ->and($url1)->toBe('https://api.sandbox.verisoul.ai');
        });
    });

    describe('validation and error handling', function (): void {
        it('throws exception for invalid string values', function (): void {
            expect(fn() => VerisoulEnvironment::from('invalid'))
                ->toThrow(ValueError::class);
        });

        it('handles case sensitivity correctly', function (): void {
            expect(VerisoulEnvironment::tryFrom('SANDBOX'))->toBeNull()
                ->and(VerisoulEnvironment::tryFrom('Sandbox'))->toBeNull()
                ->and(VerisoulEnvironment::tryFrom('PRODUCTION'))->toBeNull()
                ->and(VerisoulEnvironment::tryFrom('Production'))->toBeNull();
        });

    });

    describe('string representation', function (): void {
        it('converts to string correctly', function (): void {
            expect(VerisoulEnvironment::Sandbox->value)->toBe('sandbox')
                ->and(VerisoulEnvironment::Production->value)->toBe('production');
        });

        it('provides meaningful string representation', function (): void {
            expect((string) VerisoulEnvironment::Sandbox->value)->toBe('sandbox')
                ->and((string) VerisoulEnvironment::Production->value)->toBe('production');
        });
    });

    describe('development workflow support', function (): void {
        it('supports typical development environment patterns', function (): void {
            // Test common development patterns
            $devEnv = VerisoulEnvironment::Sandbox;
            $prodEnv = VerisoulEnvironment::Production;

            expect($devEnv->getBaseUrl())->toContain('sandbox')
                ->and($prodEnv->getBaseUrl())->not->toContain('sandbox');

            // Sandbox should be suitable for testing
            expect($devEnv)->toBe(VerisoulEnvironment::Sandbox);

            // Production should be separate
            expect($prodEnv)->toBe(VerisoulEnvironment::Production);
        });

        it('enables environment-specific configuration', function (): void {
            $environments = VerisoulEnvironment::cases();

            foreach ($environments as $env) {
                $url = $env->getBaseUrl();

                // Each environment should have a unique, valid URL
                expect($url)->toStartWith('https://')
                    ->and(filter_var($url, FILTER_VALIDATE_URL))->not->toBeFalse();
            }
        });
    });
});
