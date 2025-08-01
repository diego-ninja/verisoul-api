<?php

use Ninja\Verisoul\DTO\Device;

describe('Device DTO', function (): void {
    describe('construction', function (): void {
        it('can be created with all properties', function (): void {
            $device = Device::from([
                'category' => 'mobile',
                'type' => 'iPhone',
                'os' => 'iOS 16.5',
                'cpuCores' => 6,
                'memory' => 8,
                'gpu' => 'Apple A16 Bionic GPU',
                'screenHeight' => 2556,
                'screenWidth' => 1179,
            ]);

            expect($device->category)->toBe('mobile')
                ->and($device->type)->toBe('iPhone')
                ->and($device->os)->toBe('iOS 16.5')
                ->and($device->cpuCores)->toBe(6)
                ->and($device->memory)->toBe(8)
                ->and($device->gpu)->toBe('Apple A16 Bionic GPU')
                ->and($device->screenHeight)->toBe(2556.0)
                ->and($device->screenWidth)->toBe(1179.0);
        });

        it('can be created with minimal properties', function (): void {
            $device = Device::from([
                'category' => 'desktop',
                'type' => 'Windows PC',
                'os' => null,
                'cpuCores' => null,
                'memory' => null,
                'gpu' => null,
                'screenHeight' => null,
                'screenWidth' => null,
            ]);

            expect($device->category)->toBe('desktop')
                ->and($device->type)->toBe('Windows PC')
                ->and($device->os)->toBeNull()
                ->and($device->cpuCores)->toBeNull()
                ->and($device->memory)->toBeNull()
                ->and($device->gpu)->toBeNull()
                ->and($device->screenHeight)->toBeNull()
                ->and($device->screenWidth)->toBeNull();
        });
    });

    describe('device categories', function (): void {
        it('handles mobile devices correctly', function (): void {
            $mobileDevice = Device::from([
                'category' => 'mobile',
                'type' => 'Samsung Galaxy S23',
                'os' => 'Android 13',
                'screenHeight' => 2340,
                'screenWidth' => 1080,
            ]);

            expect($mobileDevice->category)->toBe('mobile')
                ->and($mobileDevice->type)->toBe('Samsung Galaxy S23')
                ->and($mobileDevice->os)->toBe('Android 13');
        });

        it('handles desktop devices correctly', function (): void {
            $desktopDevice = Device::from([
                'category' => 'desktop',
                'type' => 'MacBook Pro',
                'os' => 'macOS 13.4',
                'cpuCores' => 8,
                'memory' => 16,
                'screenHeight' => 1440,
                'screenWidth' => 2560,
            ]);

            expect($desktopDevice->category)->toBe('desktop')
                ->and($desktopDevice->cpuCores)->toBe(8)
                ->and($desktopDevice->memory)->toBe(16);
        });

        it('handles tablet devices correctly', function (): void {
            $tabletDevice = Device::from([
                'category' => 'tablet',
                'type' => 'iPad Pro',
                'os' => 'iPadOS 16.5',
                'screenHeight' => 2732,
                'screenWidth' => 2048,
            ]);

            expect($tabletDevice->category)->toBe('tablet')
                ->and($tabletDevice->type)->toBe('iPad Pro');
        });
    });

    describe('hardware specifications', function (): void {
        it('handles various CPU core counts', function (): void {
            $devices = [
                ['cores' => 2, 'type' => 'Budget Phone'],
                ['cores' => 4, 'type' => 'Mid-range Phone'],
                ['cores' => 8, 'type' => 'Flagship Phone'],
                ['cores' => 16, 'type' => 'High-end Laptop'],
            ];

            foreach ($devices as $deviceSpec) {
                $device = Device::from([
                    'category' => 'mobile',
                    'type' => $deviceSpec['type'],
                    'cpuCores' => $deviceSpec['cores'],
                ]);

                expect($device->cpuCores)->toBe($deviceSpec['cores']);
            }
        });

        it('handles various memory sizes', function (): void {
            $memoryConfigs = [1, 2, 4, 6, 8, 12, 16, 32, 64];

            foreach ($memoryConfigs as $memory) {
                $device = Device::from([
                    'category' => 'mobile',
                    'type' => 'Test Device',
                    'memory' => $memory,
                ]);

                expect($device->memory)->toBe($memory);
            }
        });

        it('handles various GPU descriptions', function (): void {
            $gpuSpecs = [
                'Apple A17 Pro GPU',
                'Adreno 740',
                'Mali-G715 MC11',
                'NVIDIA RTX 4090',
                'AMD Radeon RX 7900 XTX',
                'Intel Iris Xe Graphics',
            ];

            foreach ($gpuSpecs as $gpu) {
                $device = Device::from([
                    'category' => 'desktop',
                    'type' => 'Gaming PC',
                    'gpu' => $gpu,
                ]);

                expect($device->gpu)->toBe($gpu);
            }
        });
    });

    describe('screen dimensions', function (): void {
        it('handles various screen resolutions', function (): void {
            $resolutions = [
                ['width' => 720, 'height' => 1280, 'name' => 'HD'],
                ['width' => 1080, 'height' => 1920, 'name' => 'Full HD'],
                ['width' => 1440, 'height' => 2560, 'name' => 'QHD'],
                ['width' => 2160, 'height' => 3840, 'name' => '4K'],
                ['width' => 1920, 'height' => 1080, 'name' => 'Desktop Full HD'],
            ];

            foreach ($resolutions as $resolution) {
                $device = Device::from([
                    'category' => 'mobile',
                    'type' => $resolution['name'] . ' Device',
                    'screenWidth' => $resolution['width'],
                    'screenHeight' => $resolution['height'],
                ]);

                expect($device->screenWidth)->toBe((float) $resolution['width'])
                    ->and($device->screenHeight)->toBe((float) $resolution['height']);
            }
        });

        it('handles edge cases for screen dimensions', function (): void {
            // Ultra-wide monitor
            $ultraWideDevice = Device::from([
                'category' => 'desktop',
                'type' => 'Ultra-wide Monitor',
                'screenWidth' => 3440,
                'screenHeight' => 1440,
            ]);

            expect($ultraWideDevice->screenWidth)->toBe(3440.0)
                ->and($ultraWideDevice->screenHeight)->toBe(1440.0);

            // Foldable phone
            $foldableDevice = Device::from([
                'category' => 'mobile',
                'type' => 'Foldable Phone',
                'screenWidth' => 2208,
                'screenHeight' => 1768,
            ]);

            expect($foldableDevice->screenWidth)->toBe(2208.0)
                ->and($foldableDevice->screenHeight)->toBe(1768.0);
        });
    });

    describe('operating systems', function (): void {
        it('handles various mobile operating systems', function (): void {
            $mobileOS = [
                'iOS 17.0',
                'Android 14',
                'HarmonyOS 4.0',
                'iOS 16.5.1',
                'Android 13.0',
            ];

            foreach ($mobileOS as $os) {
                $device = Device::from([
                    'category' => 'mobile',
                    'type' => 'Smartphone',
                    'os' => $os,
                ]);

                expect($device->os)->toBe($os);
            }
        });

        it('handles various desktop operating systems', function (): void {
            $desktopOS = [
                'Windows 11 Pro',
                'macOS 14.0 Sonoma',
                'Ubuntu 23.04',
                'Windows 10 Home',
                'macOS 13.4 Ventura',
                'Fedora 38',
            ];

            foreach ($desktopOS as $os) {
                $device = Device::from([
                    'category' => 'desktop',
                    'type' => 'Computer',
                    'os' => $os,
                ]);

                expect($device->os)->toBe($os);
            }
        });
    });

    describe('immutability and serialization', function (): void {
        it('is readonly and immutable', function (): void {
            $device = Device::from([
                'category' => 'mobile',
                'type' => 'Test Device',
                'os' => 'Test OS',
                'cpuCores' => 4,
                'memory' => 8,
            ]);

            expect($device)->toBeInstanceOf(Device::class);

            // Properties should be accessible but not modifiable
            expect($device->category)->toBe('mobile');
        });

        it('can be serialized and deserialized', function (): void {
            $originalDevice = Device::from([
                'category' => 'tablet',
                'type' => 'iPad Air',
                'os' => 'iPadOS 16.5',
                'cpuCores' => 8,
                'memory' => 8,
                'gpu' => 'Apple M1 GPU',
                'screenWidth' => 2360,
                'screenHeight' => 1640,
            ]);

            $serialized = $originalDevice->array();
            $deserializedDevice = Device::from($serialized);

            expect($deserializedDevice->category)->toBe($originalDevice->category)
                ->and($deserializedDevice->type)->toBe($originalDevice->type)
                ->and($deserializedDevice->os)->toBe($originalDevice->os)
                ->and($deserializedDevice->cpuCores)->toBe($originalDevice->cpuCores)
                ->and($deserializedDevice->memory)->toBe($originalDevice->memory)
                ->and($deserializedDevice->gpu)->toBe($originalDevice->gpu)
                ->and($deserializedDevice->screenWidth)->toBe($originalDevice->screenWidth)
                ->and($deserializedDevice->screenHeight)->toBe($originalDevice->screenHeight);
        });
    });

    describe('real-world device examples', function (): void {
        it('handles iPhone specifications correctly', function (): void {
            $iPhone = Device::from([
                'category' => 'mobile',
                'type' => 'iPhone 15 Pro',
                'os' => 'iOS 17.0',
                'cpuCores' => 6,
                'memory' => 8,
                'gpu' => 'Apple A17 Pro GPU',
                'screenWidth' => 1179,
                'screenHeight' => 2556,
            ]);

            expect($iPhone->category)->toBe('mobile')
                ->and($iPhone->type)->toBe('iPhone 15 Pro')
                ->and($iPhone->os)->toBe('iOS 17.0')
                ->and($iPhone->cpuCores)->toBe(6)
                ->and($iPhone->memory)->toBe(8);
        });

        it('handles Android flagship specifications correctly', function (): void {
            $androidPhone = Device::from([
                'category' => 'mobile',
                'type' => 'Samsung Galaxy S24 Ultra',
                'os' => 'Android 14',
                'cpuCores' => 8,
                'memory' => 12,
                'gpu' => 'Adreno 750',
                'screenWidth' => 1440,
                'screenHeight' => 3120,
            ]);

            expect($androidPhone->category)->toBe('mobile')
                ->and($androidPhone->type)->toBe('Samsung Galaxy S24 Ultra')
                ->and($androidPhone->memory)->toBe(12)
                ->and($androidPhone->cpuCores)->toBe(8);
        });

        it('handles laptop specifications correctly', function (): void {
            $laptop = Device::from([
                'category' => 'desktop',
                'type' => 'MacBook Pro 16-inch',
                'os' => 'macOS 14.0',
                'cpuCores' => 12,
                'memory' => 32,
                'gpu' => 'Apple M3 Max GPU',
                'screenWidth' => 3456,
                'screenHeight' => 2234,
            ]);

            expect($laptop->category)->toBe('desktop')
                ->and($laptop->type)->toBe('MacBook Pro 16-inch')
                ->and($laptop->cpuCores)->toBe(12)
                ->and($laptop->memory)->toBe(32);
        });
    });

    describe('edge cases and validation', function (): void {
        it('handles devices with unknown specifications', function (): void {
            $unknownDevice = Device::from([
                'category' => 'unknown',
                'type' => 'Custom Device',
                'os' => null,
                'cpuCores' => null,
                'memory' => null,
                'gpu' => null,
                'screenHeight' => null,
                'screenWidth' => null,
            ]);

            expect($unknownDevice->category)->toBe('unknown')
                ->and($unknownDevice->type)->toBe('Custom Device')
                ->and($unknownDevice->os)->toBeNull()
                ->and($unknownDevice->cpuCores)->toBeNull();
        });

        it('handles devices with extreme specifications', function (): void {
            $extremeDevice = Device::from([
                'category' => 'server',
                'type' => 'High-performance Server',
                'os' => 'Linux Server',
                'cpuCores' => 128,
                'memory' => 1024,
                'screenWidth' => 7680,
                'screenHeight' => 4320,
            ]);

            expect($extremeDevice->cpuCores)->toBe(128)
                ->and($extremeDevice->memory)->toBe(1024)
                ->and($extremeDevice->screenWidth)->toBe(7680.0);
        });

        it('handles devices with minimal specifications', function (): void {
            $minimalDevice = Device::from([
                'category' => 'iot',
                'type' => 'IoT Device',
                'cpuCores' => 1,
                'memory' => 0, // Some IoT devices have minimal RAM
            ]);

            expect($minimalDevice->cpuCores)->toBe(1)
                ->and($minimalDevice->memory)->toBe(0);
        });
    });
});
