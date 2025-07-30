<?php

use Ninja\Verisoul\DTO\Address;

describe('Address DTO', function () {
    describe('construction', function () {
        it('can be created with all properties', function () {
            $address = new Address(
                city: 'San Francisco',
                country: 'US',
                postalCode: '94105',
                state: 'CA',
                street: '123 Main St'
            );

            expect($address->city)->toBe('San Francisco')
                ->and($address->country)->toBe('US')
                ->and($address->postalCode)->toBe('94105')
                ->and($address->state)->toBe('CA')
                ->and($address->street)->toBe('123 Main St');
        });

        it('can be created with null properties', function () {
            $address = new Address(
                city: null,
                country: null,
                postalCode: null,
                state: null,
                street: null
            );

            expect($address->city)->toBeNull()
                ->and($address->country)->toBeNull()
                ->and($address->postalCode)->toBeNull()
                ->and($address->state)->toBeNull()
                ->and($address->street)->toBeNull();
        });

        it('can be created with partial data', function () {
            $address = new Address(
                city: 'New York',
                country: 'US',
                postalCode: null,
                state: 'NY',
                street: null
            );

            expect($address->city)->toBe('New York')
                ->and($address->country)->toBe('US')
                ->and($address->postalCode)->toBeNull()
                ->and($address->state)->toBe('NY')
                ->and($address->street)->toBeNull();
        });
    });

    describe('hasData method', function () {
        it('returns true when any field has data', function () {
            $addresses = [
                new Address('City', null, null, null, null),
                new Address(null, 'Country', null, null, null),
                new Address(null, null, '12345', null, null),
                new Address(null, null, null, 'State', null),
                new Address(null, null, null, null, 'Street'),
            ];

            foreach ($addresses as $address) {
                expect($address->hasData())->toBeTrue();
            }
        });

        it('returns false when all fields are null', function () {
            $address = new Address(null, null, null, null, null);
            
            expect($address->hasData())->toBeFalse();
        });

        it('returns false when all fields are empty strings', function () {
            $address = new Address('', '', '', '', '');
            
            expect($address->hasData())->toBeFalse();
        });

        it('returns true when at least one field is not empty', function () {
            $address = new Address('', '', '', '', '123 Main St');
            
            expect($address->hasData())->toBeTrue();
        });
    });

    describe('isComplete method', function () {
        it('returns true when all required fields are filled', function () {
            $address = new Address(
                city: 'San Francisco',
                country: 'US',
                postalCode: '94105',
                state: 'CA',
                street: '123 Main St'
            );

            expect($address->isComplete())->toBeTrue();
        });

        it('returns false when any required field is missing', function () {
            $incompleteAddresses = [
                new Address(null, 'US', '94105', 'CA', '123 Main St'),     // missing city
                new Address('SF', null, '94105', 'CA', '123 Main St'),     // missing country
                new Address('SF', 'US', null, 'CA', '123 Main St'),        // missing postal code
                new Address('SF', 'US', '94105', null, '123 Main St'),     // missing state
                new Address('SF', 'US', '94105', 'CA', null),              // missing street
            ];

            foreach ($incompleteAddresses as $address) {
                expect($address->isComplete())->toBeFalse();
            }
        });

        it('returns false when any required field is empty string', function () {
            $incompleteAddresses = [
                new Address('', 'US', '94105', 'CA', '123 Main St'),
                new Address('SF', '', '94105', 'CA', '123 Main St'),
                new Address('SF', 'US', '', 'CA', '123 Main St'),
                new Address('SF', 'US', '94105', '', '123 Main St'),
                new Address('SF', 'US', '94105', 'CA', ''),
            ];

            foreach ($incompleteAddresses as $address) {
                expect($address->isComplete())->toBeFalse();
            }
        });
    });

    describe('getFormattedAddress method', function () {
        it('returns formatted address with all fields', function () {
            $address = new Address(
                city: 'San Francisco',
                country: 'US',
                postalCode: '94105',
                state: 'CA',
                street: '123 Main St'
            );

            $formatted = $address->getFormattedAddress();
            
            expect($formatted)->toBe('123 Main St, San Francisco, CA, 94105, US');
        });

        it('excludes null fields from formatting', function () {
            $address = new Address(
                city: 'New York',
                country: 'US',
                postalCode: null,
                state: 'NY',
                street: '456 Broadway'
            );

            $formatted = $address->getFormattedAddress();
            
            expect($formatted)->toBe('456 Broadway, New York, NY, US');
        });

        it('excludes empty string fields from formatting', function () {
            $address = new Address(
                city: 'Boston',
                country: 'US',
                postalCode: '',
                state: 'MA',
                street: '789 Commonwealth Ave'
            );

            $formatted = $address->getFormattedAddress();
            
            expect($formatted)->toBe('789 Commonwealth Ave, Boston, MA, US');
        });

        it('returns empty string when no data', function () {
            $address = new Address(null, null, null, null, null);
            
            expect($address->getFormattedAddress())->toBe('');
        });

        it('handles single field address', function () {
            $address = new Address(
                city: 'Chicago',
                country: null,
                postalCode: null,
                state: null,
                street: null
            );

            expect($address->getFormattedAddress())->toBe('Chicago');
        });
    });

    describe('getCompletionPercentage method', function () {
        it('returns 100% for complete address', function () {
            $address = new Address(
                city: 'San Francisco',
                country: 'US',
                postalCode: '94105',
                state: 'CA',
                street: '123 Main St'
            );

            expect($address->getCompletionPercentage())->toBe(100.0);
        });

        it('returns 0% for empty address', function () {
            $address = new Address(null, null, null, null, null);
            
            expect($address->getCompletionPercentage())->toBe(0.0);
        });

        it('returns correct percentage for partial addresses', function () {
            $testCases = [
                [1, 20.0], // 1 field filled
                [2, 40.0], // 2 fields filled
                [3, 60.0], // 3 fields filled
                [4, 80.0], // 4 fields filled
            ];

            foreach ($testCases as [$filledCount, $expectedPercentage]) {
                $fields = array_fill(0, $filledCount, 'filled');
                $nullFields = array_fill(0, 5 - $filledCount, null);
                $allFields = array_merge($fields, $nullFields);

                $address = new Address(
                    city: $allFields[0],
                    country: $allFields[1],
                    postalCode: $allFields[2],
                    state: $allFields[3],
                    street: $allFields[4]
                );

                expect($address->getCompletionPercentage())->toBe($expectedPercentage);
            }
        });

        it('treats empty strings as not filled', function () {
            $address = new Address(
                city: 'San Francisco',
                country: '',
                postalCode: '94105',
                state: '',
                street: '123 Main St'
            );

            // 3 fields filled (city, postalCode, street), 2 empty
            expect($address->getCompletionPercentage())->toBe(60.0);
        });
    });

    describe('immutability', function () {
        it('is readonly and immutable', function () {
            $address = new Address(
                city: 'Test City',
                country: 'US',
                postalCode: '12345',
                state: 'TS',
                street: 'Test St'
            );

            $reflection = new ReflectionClass($address);
            $properties = $reflection->getProperties();

            foreach ($properties as $property) {
                expect($property->isReadOnly())->toBeTrue(
                    "Property {$property->getName()} should be readonly"
                );
            }
        });
    });

    describe('serialization with GraniteDTO', function () {
        it('can be serialized to array', function () {
            $address = new Address(
                city: 'Portland',
                country: 'US',
                postalCode: '97201',
                state: 'OR',
                street: '1234 NW Everett St'
            );

            $array = $address->array();

            expect($array)->toBeArray()
                ->and($array)->toHaveKeys(['city', 'country', 'postal_code', 'state', 'street'])
                ->and($array['city'])->toBe('Portland')
                ->and($array['country'])->toBe('US')
                ->and($array['postal_code'])->toBe('97201')
                ->and($array['state'])->toBe('OR')
                ->and($array['street'])->toBe('1234 NW Everett St');
        });

        it('can be created from array', function () {
            $data = [
                'city' => 'Seattle',
                'country' => 'US',
                'postalCode' => '98101',
                'state' => 'WA',
                'street' => '500 Pine St',
            ];

            $address = Address::from($data);

            expect($address)->toBeInstanceOf(Address::class)
                ->and($address->city)->toBe('Seattle')
                ->and($address->country)->toBe('US')
                ->and($address->postalCode)->toBe('98101')
                ->and($address->state)->toBe('WA')
                ->and($address->street)->toBe('500 Pine St');
        });

        it('maintains consistency through serialization roundtrip', function () {
            $original = new Address(
                city: 'Denver',
                country: 'US',
                postalCode: null,
                state: 'CO',
                street: '16th Street Mall'
            );

            $array = $original->array();
            $restored = Address::from($array);

            expect($restored->city)->toBe($original->city)
                ->and($restored->country)->toBe($original->country)
                ->and($restored->postalCode)->toBe($original->postalCode)
                ->and($restored->state)->toBe($original->state)
                ->and($restored->street)->toBe($original->street);
        });

        it('handles null values in serialization', function () {
            $address = new Address(
                city: 'Seattle',
                country: 'US',
                postalCode: null,
                state: 'WA',
                street: '500 Pine St'
            );

            $array = $address->array();

            expect($array['city'])->toBe('Seattle')
                ->and($array['country'])->toBe('US')
                ->and($array['postal_code'])->toBeNull()
                ->and($array['state'])->toBe('WA')
                ->and($array['street'])->toBe('500 Pine St');
        });
    });

    describe('international addresses', function () {
        it('handles UK addresses', function () {
            $address = new Address(
                city: 'London',
                country: 'GB',
                postalCode: 'SW1A 1AA',
                state: 'England',
                street: '10 Downing Street'
            );

            expect($address->isComplete())->toBeTrue()
                ->and($address->getFormattedAddress())->toBe('10 Downing Street, London, England, SW1A 1AA, GB');
        });

        it('handles Canadian addresses', function () {
            $address = new Address(
                city: 'Toronto',
                country: 'CA',
                postalCode: 'M5V 3L9',
                state: 'ON',
                street: '290 Bremner Blvd'
            );

            expect($address->isComplete())->toBeTrue()
                ->and($address->getFormattedAddress())->toBe('290 Bremner Blvd, Toronto, ON, M5V 3L9, CA');
        });

        it('handles addresses with special characters', function () {
            $address = new Address(
                city: 'São Paulo',
                country: 'BR',
                postalCode: '01310-100',
                state: 'SP',
                street: 'Avenida Paulista, 1578'
            );

            expect($address->city)->toBe('São Paulo')
                ->and($address->street)->toBe('Avenida Paulista, 1578')
                ->and($address->postalCode)->toBe('01310-100');
        });
    });

    describe('edge cases', function () {
        it('handles very long addresses', function () {
            $longStreet = 'This is a very long street name that might exceed typical length limits for street addresses in some systems but should still be handled correctly';
            
            $address = new Address(
                city: 'LongCityNameThatMightBeLongerThanUsual',
                country: 'US',
                postalCode: '12345-6789',
                state: 'CA',
                street: $longStreet
            );

            expect($address->street)->toBe($longStreet)
                ->and($address->isComplete())->toBeTrue();
        });

        it('handles addresses with only numeric values', function () {
            $address = new Address(
                city: '123',
                country: '456',
                postalCode: '789',
                state: '012',
                street: '345'
            );

            expect($address->isComplete())->toBeTrue()
                ->and($address->getCompletionPercentage())->toBe(100.0);
        });
    });
});