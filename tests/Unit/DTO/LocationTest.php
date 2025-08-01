<?php

use Ninja\Verisoul\DTO\Location;

describe('Location DTO', function (): void {
    describe('construction', function (): void {
        it('can be created with all properties', function (): void {
            $location = new Location(
                continent: 'North America',
                countryCode: 'US',
                state: 'California',
                city: 'San Francisco',
                zipCode: '94105',
                timezone: 'America/Los_Angeles',
                latitude: 37.7749,
                longitude: -122.4194,
            );

            expect($location->continent)->toBe('North America')
                ->and($location->countryCode)->toBe('US')
                ->and($location->state)->toBe('California')
                ->and($location->city)->toBe('San Francisco')
                ->and($location->zipCode)->toBe('94105')
                ->and($location->timezone)->toBe('America/Los_Angeles')
                ->and($location->latitude)->toBe(37.7749)
                ->and($location->longitude)->toBe(-122.4194);
        });

        it('can be created with null properties', function (): void {
            $location = new Location(
                continent: null,
                countryCode: null,
                state: null,
                city: null,
                zipCode: null,
                timezone: null,
                latitude: null,
                longitude: null,
            );

            expect($location->continent)->toBeNull()
                ->and($location->countryCode)->toBeNull()
                ->and($location->state)->toBeNull()
                ->and($location->city)->toBeNull()
                ->and($location->zipCode)->toBeNull()
                ->and($location->timezone)->toBeNull()
                ->and($location->latitude)->toBeNull()
                ->and($location->longitude)->toBeNull();
        });

        it('can be created with partial data', function (): void {
            $location = new Location(
                continent: 'Europe',
                countryCode: 'GB',
                state: null,
                city: 'London',
                zipCode: null,
                timezone: 'Europe/London',
                latitude: 51.5074,
                longitude: -0.1278,
            );

            expect($location->continent)->toBe('Europe')
                ->and($location->countryCode)->toBe('GB')
                ->and($location->state)->toBeNull()
                ->and($location->city)->toBe('London')
                ->and($location->zipCode)->toBeNull()
                ->and($location->timezone)->toBe('Europe/London')
                ->and($location->latitude)->toBe(51.5074)
                ->and($location->longitude)->toBe(-0.1278);
        });
    });

    describe('immutability', function (): void {
        it('is readonly and immutable', function (): void {
            $location = new Location(
                continent: 'Asia',
                countryCode: 'JP',
                state: 'Tokyo',
                city: 'Tokyo',
                zipCode: '100-0001',
                timezone: 'Asia/Tokyo',
                latitude: 35.6762,
                longitude: 139.6503,
            );

            $reflection = new ReflectionClass($location);
            $properties = $reflection->getProperties();

            foreach ($properties as $property) {
                expect($property->isReadOnly())->toBeTrue(
                    "Property {$property->getName()} should be readonly",
                );
            }
        });

        it('maintains data integrity', function (): void {
            $location = new Location(
                continent: 'South America',
                countryCode: 'BR',
                state: 'São Paulo',
                city: 'São Paulo',
                zipCode: '01310-100',
                timezone: 'America/Sao_Paulo',
                latitude: -23.5505,
                longitude: -46.6333,
            );

            expect($location->continent)->toBe('South America');
            expect($location->continent)->toBe('South America'); // Second call
            expect($location->latitude)->toBe(-23.5505);
            expect($location->latitude)->toBe(-23.5505); // Second call
        });
    });

    describe('serialization with GraniteDTO', function (): void {
        it('can be serialized to array', function (): void {
            $location = new Location(
                continent: 'Australia',
                countryCode: 'AU',
                state: 'New South Wales',
                city: 'Sydney',
                zipCode: '2000',
                timezone: 'Australia/Sydney',
                latitude: -33.8688,
                longitude: 151.2093,
            );

            $array = $location->array();

            expect($array)->toBeArray()
                ->and($array)->toHaveKeys([
                    'continent', 'country_code', 'state', 'city',
                    'zip_code', 'timezone', 'latitude', 'longitude',
                ])
                ->and($array['continent'])->toBe('Australia')
                ->and($array['country_code'])->toBe('AU')
                ->and($array['state'])->toBe('New South Wales')
                ->and($array['city'])->toBe('Sydney')
                ->and($array['zip_code'])->toBe('2000')
                ->and($array['timezone'])->toBe('Australia/Sydney')
                ->and($array['latitude'])->toBe(-33.8688)
                ->and($array['longitude'])->toBe(151.2093);
        });

        it('can be created from array', function (): void {
            $data = [
                'continent' => 'Africa',
                'countryCode' => 'ZA',
                'state' => 'Western Cape',
                'city' => 'Cape Town',
                'zipCode' => '8001',
                'timezone' => 'Africa/Johannesburg',
                'latitude' => -33.9249,
                'longitude' => 18.4241,
            ];

            $location = Location::from($data);

            expect($location)->toBeInstanceOf(Location::class)
                ->and($location->continent)->toBe('Africa')
                ->and($location->countryCode)->toBe('ZA')
                ->and($location->state)->toBe('Western Cape')
                ->and($location->city)->toBe('Cape Town')
                ->and($location->zipCode)->toBe('8001')
                ->and($location->timezone)->toBe('Africa/Johannesburg')
                ->and($location->latitude)->toBe(-33.9249)
                ->and($location->longitude)->toBe(18.4241);
        });

        it('maintains consistency through serialization roundtrip', function (): void {
            $original = new Location(
                continent: 'North America',
                countryCode: 'CA',
                state: 'Ontario',
                city: 'Toronto',
                zipCode: 'M5V 3L9',
                timezone: 'America/Toronto',
                latitude: 43.6532,
                longitude: -79.3832,
            );

            $array = $original->array();
            $restored = Location::from($array);

            expect($restored->continent)->toBe($original->continent)
                ->and($restored->countryCode)->toBe($original->countryCode)
                ->and($restored->state)->toBe($original->state)
                ->and($restored->city)->toBe($original->city)
                ->and($restored->zipCode)->toBe($original->zipCode)
                ->and($restored->timezone)->toBe($original->timezone)
                ->and($restored->latitude)->toBe($original->latitude)
                ->and($restored->longitude)->toBe($original->longitude);
        });

        it('can be created from JSON string', function (): void {
            $json = '{"continent":"Europe","countryCode":"FR","state":"Île-de-France","city":"Paris","zipCode":"75001","timezone":"Europe/Paris","latitude":48.8566,"longitude":2.3522}';

            $location = Location::from($json);

            expect($location)->toBeInstanceOf(Location::class)
                ->and($location->continent)->toBe('Europe')
                ->and($location->countryCode)->toBe('FR')
                ->and($location->city)->toBe('Paris')
                ->and($location->latitude)->toBe(48.8566)
                ->and($location->longitude)->toBe(2.3522);
        });

        it('handles null values in serialization', function (): void {
            $location = new Location(
                continent: 'Asia',
                countryCode: 'IN',
                state: null,
                city: 'Mumbai',
                zipCode: null,
                timezone: 'Asia/Kolkata',
                latitude: 19.0760,
                longitude: 72.8777,
            );

            $array = $location->array();

            expect($array['continent'])->toBe('Asia')
                ->and($array['country_code'])->toBe('IN')
                ->and($array['state'])->toBeNull()
                ->and($array['city'])->toBe('Mumbai')
                ->and($array['zip_code'])->toBeNull()
                ->and($array['timezone'])->toBe('Asia/Kolkata')
                ->and($array['latitude'])->toBe(19.0760)
                ->and($array['longitude'])->toBe(72.8777);
        });
    });

    describe('coordinate handling', function (): void {
        it('handles positive and negative coordinates', function (): void {
            $locations = [
                // Northern Hemisphere, Eastern Longitude
                [40.7128, 74.0060, 'New York'],
                // Southern Hemisphere, Western Longitude
                [-34.6037, -58.3816, 'Buenos Aires'],
                // Northern Hemisphere, Western Longitude
                [55.7558, -37.6176, 'Moscow'],
                // Southern Hemisphere, Eastern Longitude
                [-37.8136, 144.9631, 'Melbourne'],
            ];

            foreach ($locations as [$lat, $lng, $cityName]) {
                $location = new Location(
                    continent: null,
                    countryCode: null,
                    state: null,
                    city: $cityName,
                    zipCode: null,
                    timezone: null,
                    latitude: $lat,
                    longitude: $lng,
                );

                expect($location->latitude)->toBe($lat)
                    ->and($location->longitude)->toBe($lng)
                    ->and($location->city)->toBe($cityName);
            }
        });

        it('handles extreme coordinate values', function (): void {
            // North Pole
            $northPole = new Location(
                continent: null,
                countryCode: null,
                state: null,
                city: null,
                zipCode: null,
                timezone: null,
                latitude: 90.0,
                longitude: 0.0,
            );

            // South Pole
            $southPole = new Location(
                continent: 'Antarctica',
                countryCode: null,
                state: null,
                city: null,
                zipCode: null,
                timezone: null,
                latitude: -90.0,
                longitude: 0.0,
            );

            expect($northPole->latitude)->toBe(90.0)
                ->and($southPole->latitude)->toBe(-90.0)
                ->and($southPole->continent)->toBe('Antarctica');
        });

        it('handles high precision coordinates', function (): void {
            $location = new Location(
                continent: null,
                countryCode: null,
                state: null,
                city: null,
                zipCode: null,
                timezone: null,
                latitude: 37.774929496,
                longitude: -122.419415582,
            );

            expect($location->latitude)->toBe(37.774929496)
                ->and($location->longitude)->toBe(-122.419415582);
        });
    });

    describe('timezone handling', function (): void {
        it('handles various timezone formats', function (): void {
            $timezones = [
                'America/New_York',
                'Europe/London',
                'Asia/Tokyo',
                'Australia/Sydney',
                'Africa/Cairo',
                'America/Los_Angeles',
                'Europe/Berlin',
                'Asia/Shanghai',
                'Pacific/Auckland',
                'America/Sao_Paulo',
            ];

            foreach ($timezones as $timezone) {
                $location = new Location(
                    continent: null,
                    countryCode: null,
                    state: null,
                    city: null,
                    zipCode: null,
                    timezone: $timezone,
                    latitude: null,
                    longitude: null,
                );

                expect($location->timezone)->toBe($timezone);
            }
        });

        it('handles UTC and GMT variations', function (): void {
            $utcVariations = [
                'UTC',
                'GMT',
                'UTC+0',
                'GMT+0',
                'Etc/UTC',
                'Etc/GMT',
            ];

            foreach ($utcVariations as $timezone) {
                $location = new Location(
                    continent: null,
                    countryCode: null,
                    state: null,
                    city: null,
                    zipCode: null,
                    timezone: $timezone,
                    latitude: null,
                    longitude: null,
                );

                expect($location->timezone)->toBe($timezone);
            }
        });
    });

    describe('international locations', function (): void {
        it('handles various country codes', function (): void {
            $countries = [
                ['US', 'United States'],
                ['GB', 'United Kingdom'],
                ['DE', 'Germany'],
                ['JP', 'Japan'],
                ['AU', 'Australia'],
                ['BR', 'Brazil'],
                ['IN', 'India'],
                ['CN', 'China'],
                ['ZA', 'South Africa'],
                ['EG', 'Egypt'],
            ];

            foreach ($countries as [$code, $name]) {
                $location = new Location(
                    continent: null,
                    countryCode: $code,
                    state: null,
                    city: $name,
                    zipCode: null,
                    timezone: null,
                    latitude: null,
                    longitude: null,
                );

                expect($location->countryCode)->toBe($code)
                    ->and($location->city)->toBe($name);
            }
        });

        it('handles international postal codes', function (): void {
            $postalCodes = [
                ['US', '94105'],       // US ZIP
                ['GB', 'SW1A 1AA'],   // UK Postcode
                ['CA', 'M5V 3L9'],    // Canadian Postal Code
                ['DE', '10115'],      // German PLZ
                ['FR', '75001'],      // French Code Postal
                ['JP', '100-0001'],   // Japanese Postal Code
                ['AU', '2000'],       // Australian Postcode
                ['BR', '01310-100'],  // Brazilian CEP
            ];

            foreach ($postalCodes as [$country, $zipCode]) {
                $location = new Location(
                    continent: null,
                    countryCode: $country,
                    state: null,
                    city: null,
                    zipCode: $zipCode,
                    timezone: null,
                    latitude: null,
                    longitude: null,
                );

                expect($location->countryCode)->toBe($country)
                    ->and($location->zipCode)->toBe($zipCode);
            }
        });

        it('handles unicode city and state names', function (): void {
            $unicodeLocations = [
                ['São Paulo', 'São Paulo', 'BR'],
                ['Москва', 'Московская область', 'RU'],
                ['北京', '北京市', 'CN'],
                ['東京', '東京都', 'JP'],
                ['القاهرة', 'محافظة القاهرة', 'EG'],
                ['München', 'Bayern', 'DE'],
                ['Zürich', 'Zürich', 'CH'],
            ];

            foreach ($unicodeLocations as [$city, $state, $country]) {
                $location = new Location(
                    continent: null,
                    countryCode: $country,
                    state: $state,
                    city: $city,
                    zipCode: null,
                    timezone: null,
                    latitude: null,
                    longitude: null,
                );

                expect($location->city)->toBe($city)
                    ->and($location->state)->toBe($state)
                    ->and($location->countryCode)->toBe($country);
            }
        });
    });

    describe('real-world usage patterns', function (): void {
        it('supports typical IP geolocation data', function (): void {
            $ipLocation = new Location(
                continent: 'North America',
                countryCode: 'US',
                state: 'California',
                city: 'Mountain View',
                zipCode: '94043',
                timezone: 'America/Los_Angeles',
                latitude: 37.4419,
                longitude: -122.1430,
            );

            expect($ipLocation->continent)->toBe('North America')
                ->and($ipLocation->countryCode)->toBe('US')
                ->and($ipLocation->state)->toBe('California')
                ->and($ipLocation->city)->toBe('Mountain View')
                ->and($ipLocation->zipCode)->toBe('94043')
                ->and($ipLocation->timezone)->toBe('America/Los_Angeles');
        });

        it('supports partial location data from mobile devices', function (): void {
            $mobileLocation = new Location(
                continent: null,
                countryCode: 'US',
                state: null,
                city: 'San Francisco',
                zipCode: null,
                timezone: 'America/Los_Angeles',
                latitude: 37.7749,
                longitude: -122.4194,
            );

            expect($mobileLocation->countryCode)->toBe('US')
                ->and($mobileLocation->city)->toBe('San Francisco')
                ->and($mobileLocation->state)->toBeNull()
                ->and($mobileLocation->latitude)->toBe(37.7749)
                ->and($mobileLocation->longitude)->toBe(-122.4194);
        });

        it('supports VPN/proxy detection scenarios', function (): void {
            $vpnLocation = new Location(
                continent: 'Europe',
                countryCode: 'NL',
                state: 'North Holland',
                city: 'Amsterdam',
                zipCode: null,
                timezone: 'Europe/Amsterdam',
                latitude: 52.3676,
                longitude: 4.9041,
            );

            expect($vpnLocation->continent)->toBe('Europe')
                ->and($vpnLocation->countryCode)->toBe('NL')
                ->and($vpnLocation->city)->toBe('Amsterdam')
                ->and($vpnLocation->timezone)->toBe('Europe/Amsterdam');
        });
    });
});
