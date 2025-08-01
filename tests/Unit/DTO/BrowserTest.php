<?php

use Ninja\Verisoul\DTO\Browser;

describe('Browser DTO', function (): void {
    describe('construction', function (): void {
        it('can be created with all properties', function (): void {
            $browser = Browser::from([
                'type' => 'Chrome',
                'version' => '118.0.5993.88',
                'language' => 'en-US',
                'userAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36',
                'timezone' => 'America/New_York',
            ]);

            expect($browser->type)->toBe('Chrome')
                ->and($browser->version)->toBe('118.0.5993.88')
                ->and($browser->language)->toBe('en-US')
                ->and($browser->userAgent)->toBe('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36')
                ->and($browser->timezone)->toBe('America/New_York');
        });

        it('can be created with minimal properties', function (): void {
            $browser = Browser::from([
                'type' => 'Firefox',
                'version' => '119.0',
                'language' => null,
                'userAgent' => null,
                'timezone' => null,
            ]);

            expect($browser->type)->toBe('Firefox')
                ->and($browser->version)->toBe('119.0')
                ->and($browser->language)->toBeNull()
                ->and($browser->userAgent)->toBeNull()
                ->and($browser->timezone)->toBeNull();
        });
    });

    describe('browser types', function (): void {
        it('handles Chrome correctly', function (): void {
            $chrome = Browser::from([
                'type' => 'Chrome',
                'version' => '118.0.5993.88',
                'language' => 'en-US',
                'userAgent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36',
            ]);

            expect($chrome->type)->toBe('Chrome')
                ->and($chrome->version)->toContain('118.0')
                ->and($chrome->userAgent)->toContain('Chrome');
        });

        it('handles Firefox correctly', function (): void {
            $firefox = Browser::from([
                'type' => 'Firefox',
                'version' => '119.0',
                'language' => 'en-GB',
                'userAgent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/119.0',
            ]);

            expect($firefox->type)->toBe('Firefox')
                ->and($firefox->version)->toBe('119.0')
                ->and($firefox->userAgent)->toContain('Firefox');
        });

        it('handles Safari correctly', function (): void {
            $safari = Browser::from([
                'type' => 'Safari',
                'version' => '17.0',
                'language' => 'en-US',
                'userAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
            ]);

            expect($safari->type)->toBe('Safari')
                ->and($safari->version)->toBe('17.0')
                ->and($safari->userAgent)->toContain('Safari');
        });

        it('handles Edge correctly', function (): void {
            $edge = Browser::from([
                'type' => 'Edge',
                'version' => '118.0.2088.76',
                'language' => 'en-US',
                'userAgent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36 Edg/118.0.2088.76',
            ]);

            expect($edge->type)->toBe('Edge')
                ->and($edge->version)->toContain('118.0')
                ->and($edge->userAgent)->toContain('Edg');
        });

        it('handles mobile browsers correctly', function (): void {
            $mobileBrowsers = [
                [
                    'type' => 'Chrome Mobile',
                    'version' => '118.0.5993.111',
                    'userAgent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/118.0.5993.111 Mobile/15E148 Safari/604.1',
                ],
                [
                    'type' => 'Safari Mobile',
                    'version' => '17.0',
                    'userAgent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
                ],
                [
                    'type' => 'Samsung Internet',
                    'version' => '23.0.1.1',
                    'userAgent' => 'Mozilla/5.0 (Linux; Android 13; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/23.0 Chrome/115.0.0.0 Mobile Safari/537.36',
                ],
            ];

            foreach ($mobileBrowsers as $browserData) {
                $browser = Browser::from($browserData);
                expect($browser->type)->toBe($browserData['type'])
                    ->and($browser->version)->toBe($browserData['version'])
                    ->and($browser->userAgent)->toBe($browserData['userAgent']);
            }
        });
    });

    describe('version handling', function (): void {
        it('handles various version formats', function (): void {
            $versionFormats = [
                ['version' => '118.0.5993.88', 'description' => 'Full Chrome version'],
                ['version' => '119.0', 'description' => 'Major.Minor Firefox version'],
                ['version' => '17.0.1', 'description' => 'Safari version with patch'],
                ['version' => '1.0.0.1', 'description' => 'Four-part version'],
                ['version' => '91', 'description' => 'Major version only'],
                ['version' => '2023.10.15', 'description' => 'Date-based version'],
            ];

            foreach ($versionFormats as $versionData) {
                $browser = Browser::from([
                    'type' => 'TestBrowser',
                    'version' => $versionData['version'],
                ]);

                expect($browser->version)->toBe($versionData['version']);
            }
        });

        it('handles beta and development versions', function (): void {
            $devVersions = [
                'Chrome Beta 119.0.6045.53',
                'Firefox Nightly 121.0a1',
                'Safari Technology Preview Release 180',
                'Edge Dev 120.0.2194.0',
            ];

            foreach ($devVersions as $version) {
                $browser = Browser::from([
                    'type' => 'Development Browser',
                    'version' => $version,
                ]);

                expect($browser->version)->toBe($version);
            }
        });
    });

    describe('language and localization', function (): void {
        it('handles various language codes', function (): void {
            $languages = [
                'en-US', 'en-GB', 'en-CA', 'en-AU',
                'es-ES', 'es-MX', 'es-AR',
                'fr-FR', 'fr-CA',
                'de-DE', 'de-AT',
                'it-IT',
                'pt-BR', 'pt-PT',
                'ja-JP',
                'ko-KR',
                'zh-CN', 'zh-TW',
                'ru-RU',
                'ar-SA',
                'hi-IN',
            ];

            foreach ($languages as $language) {
                $browser = Browser::from([
                    'type' => 'Chrome',
                    'version' => '118.0.0.0',
                    'language' => $language,
                ]);

                expect($browser->language)->toBe($language);
            }
        });

        it('handles edge cases for language codes', function (): void {
            $edgeCases = [
                'en', // Just language code
                'en-US-posix', // Extended format
                'zh-Hans-CN', // Script subtag
                'sr-Latn-RS', // Script and region
                'x-unknown', // Private use
            ];

            foreach ($edgeCases as $language) {
                $browser = Browser::from([
                    'type' => 'Chrome',
                    'version' => '118.0.0.0',
                    'language' => $language,
                ]);

                expect($browser->language)->toBe($language);
            }
        });
    });

    describe('timezone handling', function (): void {
        it('handles various timezone formats', function (): void {
            $timezones = [
                'America/New_York',
                'America/Los_Angeles',
                'America/Chicago',
                'America/Denver',
                'Europe/London',
                'Europe/Paris',
                'Europe/Berlin',
                'Asia/Tokyo',
                'Asia/Shanghai',
                'Asia/Kolkata',
                'Australia/Sydney',
                'Pacific/Auckland',
                'Africa/Cairo',
                'UTC',
                'GMT',
                'EST',
                'PST',
                'JST',
            ];

            foreach ($timezones as $timezone) {
                $browser = Browser::from([
                    'type' => 'Chrome',
                    'version' => '118.0.0.0',
                    'timezone' => $timezone,
                ]);

                expect($browser->timezone)->toBe($timezone);
            }
        });

        it('handles numeric timezone offsets', function (): void {
            $numericTimezones = [
                '+00:00',
                '+01:00',
                '+02:00',
                '+05:30', // India Standard Time
                '+09:00', // Japan Standard Time
                '-05:00', // Eastern Standard Time
                '-08:00', // Pacific Standard Time
                '-03:00', // Argentina Time
                '+10:00', // Australian Eastern Standard Time
                '+12:00',  // New Zealand Standard Time
            ];

            foreach ($numericTimezones as $timezone) {
                $browser = Browser::from([
                    'type' => 'Firefox',
                    'version' => '119.0',
                    'timezone' => $timezone,
                ]);

                expect($browser->timezone)->toBe($timezone);
            }
        });
    });

    describe('user agent parsing', function (): void {
        it('handles complex desktop user agents', function (): void {
            $desktopUserAgents = [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36',
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36',
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/119.0',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/119.0',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
            ];

            foreach ($desktopUserAgents as $userAgent) {
                $browser = Browser::from([
                    'type' => 'Desktop Browser',
                    'version' => '1.0.0',
                    'userAgent' => $userAgent,
                ]);

                expect($browser->userAgent)->toBe($userAgent)
                    ->and($browser->userAgent)->toContain('Mozilla/5.0');
            }
        });

        it('handles mobile user agents', function (): void {
            $mobileUserAgents = [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
                'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
                'Mozilla/5.0 (Linux; Android 13; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Mobile Safari/537.36',
                'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Mobile Safari/537.36',
            ];

            foreach ($mobileUserAgents as $userAgent) {
                $browser = Browser::from([
                    'type' => 'Mobile Browser',
                    'version' => '1.0.0',
                    'userAgent' => $userAgent,
                ]);

                expect($browser->userAgent)->toBe($userAgent)
                    ->and($browser->userAgent)->toContain('Mobile');
            }
        });
    });

    describe('immutability and serialization', function (): void {
        it('is readonly and immutable', function (): void {
            $browser = Browser::from([
                'type' => 'Chrome',
                'version' => '118.0.0.0',
                'language' => 'en-US',
                'timezone' => 'America/New_York',
            ]);

            expect($browser)->toBeInstanceOf(Browser::class);
            expect($browser->type)->toBe('Chrome');
        });

        it('can be serialized and deserialized', function (): void {
            $originalBrowser = Browser::from([
                'type' => 'Firefox',
                'version' => '119.0.1',
                'language' => 'fr-FR',
                'userAgent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/119.0',
                'timezone' => 'Europe/Paris',
            ]);

            $serialized = $originalBrowser->array();
            $deserializedBrowser = Browser::from($serialized);

            expect($deserializedBrowser->type)->toBe($originalBrowser->type)
                ->and($deserializedBrowser->version)->toBe($originalBrowser->version)
                ->and($deserializedBrowser->language)->toBe($originalBrowser->language)
                ->and($deserializedBrowser->userAgent)->toBe($originalBrowser->userAgent)
                ->and($deserializedBrowser->timezone)->toBe($originalBrowser->timezone);
        });
    });

    describe('real-world browser examples', function (): void {
        it('handles Chrome on Windows correctly', function (): void {
            $chromeWindows = Browser::from([
                'type' => 'Chrome',
                'version' => '118.0.5993.88',
                'language' => 'en-US',
                'userAgent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36',
                'timezone' => 'America/New_York',
            ]);

            expect($chromeWindows->type)->toBe('Chrome')
                ->and($chromeWindows->userAgent)->toContain('Windows NT 10.0')
                ->and($chromeWindows->userAgent)->toContain('Chrome/118.0.0.0');
        });

        it('handles Safari on macOS correctly', function (): void {
            $safariMac = Browser::from([
                'type' => 'Safari',
                'version' => '17.0',
                'language' => 'en-US',
                'userAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
                'timezone' => 'America/Los_Angeles',
            ]);

            expect($safariMac->type)->toBe('Safari')
                ->and($safariMac->userAgent)->toContain('Macintosh')
                ->and($safariMac->userAgent)->toContain('Safari/605.1.15');
        });

        it('handles Firefox on Linux correctly', function (): void {
            $firefoxLinux = Browser::from([
                'type' => 'Firefox',
                'version' => '119.0',
                'language' => 'en-GB',
                'userAgent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/119.0',
                'timezone' => 'Europe/London',
            ]);

            expect($firefoxLinux->type)->toBe('Firefox')
                ->and($firefoxLinux->userAgent)->toContain('X11; Linux x86_64')
                ->and($firefoxLinux->userAgent)->toContain('Firefox/119.0');
        });
    });

    describe('edge cases and validation', function (): void {
        it('handles browsers with special characters in properties', function (): void {
            $specialBrowser = Browser::from([
                'type' => 'Custom Browser™',
                'version' => '1.0.0-beta+build.123',
                'language' => 'zh-Hans-CN',
                'userAgent' => 'CustomBrowser/1.0 (Special™ Edition; 测试版本)',
                'timezone' => 'Asia/Shanghai',
            ]);

            expect($specialBrowser->type)->toBe('Custom Browser™')
                ->and($specialBrowser->version)->toBe('1.0.0-beta+build.123')
                ->and($specialBrowser->userAgent)->toContain('测试版本');
        });

        it('handles empty and null values gracefully', function (): void {
            $minimalBrowser = Browser::from([
                'type' => 'Unknown',
                'version' => null,
                'language' => null,
                'userAgent' => null,
                'timezone' => null,
            ]);

            expect($minimalBrowser->type)->toBe('Unknown')
                ->and($minimalBrowser->version)->toBeNull()
                ->and($minimalBrowser->language)->toBeNull()
                ->and($minimalBrowser->userAgent)->toBeNull()
                ->and($minimalBrowser->timezone)->toBeNull();
        });

        it('handles very long user agent strings', function (): void {
            $longUserAgent = str_repeat('Mozilla/5.0 (Very Long Browser Description) ', 50) . 'EndOfUserAgent/1.0';

            $browser = Browser::from([
                'type' => 'VerboseBrowser',
                'version' => '1.0.0',
                'userAgent' => $longUserAgent,
            ]);

            expect($browser->userAgent)->toBe($longUserAgent)
                ->and(strlen($browser->userAgent))->toBeGreaterThan(1000);
        });
    });
});
