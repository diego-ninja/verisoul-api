<?php

use Ninja\Verisoul\DTO\Device;

describe('Device DTO', function () {
    describe('construction', function () {
        it('can be created with all properties', function () {
            $device = Device::from([
                'category' => 'mobile',
                'type' => 'iPhone',
                'os' => 'iOS 16.5',
                'cpu_cores' => 6,
                'memory' => 8,
                'gpu' => 'Apple A16 Bionic GPU',
                'screen_height' => 2556,
                'screen_width' => 1179
            ]);

            expect($device->category)->toBe('mobile')
                ->and($device->type)->toBe('iPhone')
                ->and($device->os)->toBe('iOS 16.5')
                ->and($device->cpu_cores)->toBe(6)
                ->and($device->memory)->toBe(8)
                ->and($device->gpu)->toBe('Apple A16 Bionic GPU')
                ->and($device->screen_height)->toBe(2556)
                ->and($device->screen_width)->toBe(1179);
        });

        it('can be created with minimal properties', function () {
            $device = Device::from([
                'category' => 'desktop',
                'type' => 'Windows PC'
            ]);

            expect($device->category)->toBe('desktop')
                ->and($device->type)->toBe('Windows PC')
                ->and($device->os)->toBeNull()
                ->and($device->cpu_cores)->toBeNull()
                ->and($device->memory)->toBeNull()
                ->and($device->gpu)->toBeNull()
                ->and($device->screen_height)->toBeNull()
                ->and($device->screen_width)->toBeNull();
        });
    });

    describe('device categories', function () {
        it('handles mobile devices correctly', function () {
            $mobileDevice = Device::from([
                'category' => 'mobile',
                'type' => 'Samsung Galaxy S23',
                'os' => 'Android 13',
                'screen_height' => 2340,
                'screen_width' => 1080
            ]);

            expect($mobileDevice->category)->toBe('mobile')
                ->and($mobileDevice->type)->toBe('Samsung Galaxy S23')
                ->and($mobileDevice->os)->toBe('Android 13');
        });

        it('handles desktop devices correctly', function () {
            $desktopDevice = Device::from([
                'category' => 'desktop',
                'type' => 'MacBook Pro',
                'os' => 'macOS 13.4',
                'cpu_cores' => 8,
                'memory' => 16,
                'screen_height' => 1440,
                'screen_width' => 2560
            ]);

            expect($desktopDevice->category)->toBe('desktop')
                ->and($desktopDevice->cpu_cores)->toBe(8)
                ->and($desktopDevice->memory)->toBe(16);
        });

        it('handles tablet devices correctly', function () {
            $tabletDevice = Device::from([
                'category' => 'tablet',
                'type' => 'iPad Pro',
                'os' => 'iPadOS 16.5',
                'screen_height' => 2732,
                'screen_width' => 2048
            ]);

            expect($tabletDevice->category)->toBe('tablet')
                ->and($tabletDevice->type)->toBe('iPad Pro');
        });
    });

    describe('hardware specifications', function () {
        it('handles various CPU core counts', function () {
            $devices = [
                ['cores' => 2, 'type' => 'Budget Phone'],
                ['cores' => 4, 'type' => 'Mid-range Phone'],
                ['cores' => 8, 'type' => 'Flagship Phone'],
                ['cores' => 16, 'type' => 'High-end Laptop']
            ];

            foreach ($devices as $deviceSpec) {
                $device = Device::from([
                    'category' => 'mobile',
                    'type' => $deviceSpec['type'],
                    'cpu_cores' => $deviceSpec['cores']
                ]);

                expect($device->cpu_cores)->toBe($deviceSpec['cores']);
            }
        });

        it('handles various memory sizes', function () {
            $memoryConfigs = [1, 2, 4, 6, 8, 12, 16, 32, 64];

            foreach ($memoryConfigs as $memory) {
                $device = Device::from([
                    'category' => 'mobile',
                    'type' => 'Test Device',
                    'memory' => $memory
                ]);

                expect($device->memory)->toBe($memory);
            }
        });

        it('handles various GPU descriptions', function () {
            $gpuSpecs = [
                'Apple A17 Pro GPU',
                'Adreno 740',
                'Mali-G715 MC11',
                'NVIDIA RTX 4090',
                'AMD Radeon RX 7900 XTX',
                'Intel Iris Xe Graphics'
            ];

            foreach ($gpuSpecs as $gpu) {
                $device = Device::from([
                    'category' => 'desktop',
                    'type' => 'Gaming PC',
                    'gpu' => $gpu
                ]);

                expect($device->gpu)->toBe($gpu);
            }
        });
    });

    describe('screen dimensions', function () {
        it('handles various screen resolutions', function () {
            $resolutions = [
                ['width' => 720, 'height' => 1280, 'name' => 'HD'],
                ['width' => 1080, 'height' => 1920, 'name' => 'Full HD'],
                ['width' => 1440, 'height' => 2560, 'name' => 'QHD'],
                ['width' => 2160, 'height' => 3840, 'name' => '4K'],
                ['width' => 1920, 'height' => 1080, 'name' => 'Desktop Full HD']
            ];

            foreach ($resolutions as $resolution) {
                $device = Device::from([
                    'category' => 'mobile',
                    'type' => $resolution['name'] . ' Device',
                    'screen_width' => $resolution['width'],
                    'screen_height' => $resolution['height']
                ]);

                expect($device->screen_width)->toBe($resolution['width'])
                    ->and($device->screen_height)->toBe($resolution['height']);
            }
        });

        it('handles edge cases for screen dimensions', function () {
            // Ultra-wide monitor
            $ultraWideDevice = Device::from([
                'category' => 'desktop',
                'type' => 'Ultra-wide Monitor',
                'screen_width' => 3440,
                'screen_height' => 1440
            ]);

            expect($ultraWideDevice->screen_width)->toBe(3440)
                ->and($ultraWideDevice->screen_height)->toBe(1440);

            // Foldable phone
            $foldableDevice = Device::from([
                'category' => 'mobile',
                'type' => 'Foldable Phone',
                'screen_width' => 2208,
                'screen_height' => 1768
            ]);

            expect($foldableDevice->screen_width)->toBe(2208)
                ->and($foldableDevice->screen_height)->toBe(1768);
        });
    });

    describe('operating systems', function () {
        it('handles various mobile operating systems', function () {
            $mobileOS = [
                'iOS 17.0',
                'Android 14',
                'HarmonyOS 4.0',
                'iOS 16.5.1',
                'Android 13.0'
            ];

            foreach ($mobileOS as $os) {
                $device = Device::from([
                    'category' => 'mobile',
                    'type' => 'Smartphone',
                    'os' => $os
                ]);

                expect($device->os)->toBe($os);
            }
        });

        it('handles various desktop operating systems', function () {
            $desktopOS = [
                'Windows 11 Pro',
                'macOS 14.0 Sonoma',
                'Ubuntu 23.04',
                'Windows 10 Home',
                'macOS 13.4 Ventura',
                'Fedora 38'
            ];

            foreach ($desktopOS as $os) {
                $device = Device::from([
                    'category' => 'desktop',
                    'type' => 'Computer',
                    'os' => $os
                ]);

                expect($device->os)->toBe($os);
            }
        });
    });

    describe('immutability and serialization', function () {
        it('is readonly and immutable', function () {
            $device = Device::from([
                'category' => 'mobile',
                'type' => 'Test Device',
                'os' => 'Test OS',
                'cpu_cores' => 4,
                'memory' => 8
            ]);

            expect($device)->toBeInstanceOf(Device::class);
            
            // Properties should be accessible but not modifiable
            expect($device->category)->toBe('mobile');
        });

        it('can be serialized and deserialized', function () {
            $originalDevice = Device::from([
                'category' => 'tablet',
                'type' => 'iPad Air',
                'os' => 'iPadOS 16.5',
                'cpu_cores' => 8,
                'memory' => 8,
                'gpu' => 'Apple M1 GPU',
                'screen_width' => 2360,
                'screen_height' => 1640
            ]);

            $serialized = $originalDevice->toArray();
            $deserializedDevice = Device::from($serialized);

            expect($deserializedDevice->category)->toBe($originalDevice->category)
                ->and($deserializedDevice->type)->toBe($originalDevice->type)
                ->and($deserializedDevice->os)->toBe($originalDevice->os)
                ->and($deserializedDevice->cpu_cores)->toBe($originalDevice->cpu_cores)
                ->and($deserializedDevice->memory)->toBe($originalDevice->memory)
                ->and($deserializedDevice->gpu)->toBe($originalDevice->gpu)
                ->and($deserializedDevice->screen_width)->toBe($originalDevice->screen_width)
                ->and($deserializedDevice->screen_height)->toBe($originalDevice->screen_height);
        });
    });

    describe('real-world device examples', function () {
        it('handles iPhone specifications correctly', function () {
            $iPhone = Device::from([
                'category' => 'mobile',
                'type' => 'iPhone 15 Pro',
                'os' => 'iOS 17.0',
                'cpu_cores' => 6,
                'memory' => 8,
                'gpu' => 'Apple A17 Pro GPU',
                'screen_width' => 1179,
                'screen_height' => 2556
            ]);

            expect($iPhone->category)->toBe('mobile')
                ->and($iPhone->type)->toBe('iPhone 15 Pro')
                ->and($iPhone->os)->toBe('iOS 17.0')
                ->and($iPhone->cpu_cores)->toBe(6)
                ->and($iPhone->memory)->toBe(8);
        });

        it('handles Android flagship specifications correctly', function () {
            $androidPhone = Device::from([
                'category' => 'mobile',
                'type' => 'Samsung Galaxy S24 Ultra',
                'os' => 'Android 14',
                'cpu_cores' => 8,
                'memory' => 12,
                'gpu' => 'Adreno 750',
                'screen_width' => 1440,
                'screen_height' => 3120
            ]);

            expect($androidPhone->category)->toBe('mobile')
                ->and($androidPhone->type)->toBe('Samsung Galaxy S24 Ultra')
                ->and($androidPhone->memory)->toBe(12)
                ->and($androidPhone->cpu_cores)->toBe(8);
        });

        it('handles laptop specifications correctly', function () {
            $laptop = Device::from([
                'category' => 'desktop',
                'type' => 'MacBook Pro 16-inch',
                'os' => 'macOS 14.0',
                'cpu_cores' => 12,
                'memory' => 32,
                'gpu' => 'Apple M3 Max GPU',
                'screen_width' => 3456,
                'screen_height' => 2234
            ]);

            expect($laptop->category)->toBe('desktop')
                ->and($laptop->type)->toBe('MacBook Pro 16-inch')
                ->and($laptop->cpu_cores)->toBe(12)
                ->and($laptop->memory)->toBe(32);
        });
    });

    describe('edge cases and validation', function () {
        it('handles devices with unknown specifications', function () {
            $unknownDevice = Device::from([
                'category' => 'unknown',
                'type' => 'Custom Device'
            ]);

            expect($unknownDevice->category)->toBe('unknown')
                ->and($unknownDevice->type)->toBe('Custom Device')
                ->and($unknownDevice->os)->toBeNull()
                ->and($unknownDevice->cpu_cores)->toBeNull();
        });

        it('handles devices with extreme specifications', function () {
            $extremeDevice = Device::from([
                'category' => 'server',
                'type' => 'High-performance Server',
                'os' => 'Linux Server',
                'cpu_cores' => 128,
                'memory' => 1024,
                'screen_width' => 7680,
                'screen_height' => 4320
            ]);

            expect($extremeDevice->cpu_cores)->toBe(128)
                ->and($extremeDevice->memory)->toBe(1024)
                ->and($extremeDevice->screen_width)->toBe(7680);
        });

        it('handles devices with minimal specifications', function () {
            $minimalDevice = Device::from([
                'category' => 'iot',
                'type' => 'IoT Device',
                'cpu_cores' => 1,
                'memory' => 0 // Some IoT devices have minimal RAM
            ]);

            expect($minimalDevice->cpu_cores)->toBe(1)
                ->and($minimalDevice->memory)->toBe(0);
        });
    });
});