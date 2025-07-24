<?php

use Ninja\Verisoul\DTO\Phone;
use Ninja\Verisoul\Tests\Helpers\DataProvider;

describe('Phone DTO', function () {
    describe('construction', function () {
        it('can be created with all required properties', function () {
            $phone = new Phone(
                valid: true,
                phoneNumber: '+14155552671',
                callingCountryCode: '1',
                countryCode: 'US',
                carrierName: 'Verizon',
                lineType: 'mobile'
            );

            expect($phone->valid)->toBeTrue()
                ->and($phone->phoneNumber)->toBe('+14155552671')
                ->and($phone->callingCountryCode)->toBe('1')
                ->and($phone->countryCode)->toBe('US')
                ->and($phone->carrierName)->toBe('Verizon')
                ->and($phone->lineType)->toBe('mobile');
        });

        it('can be created with invalid phone data', function () {
            $phone = new Phone(
                valid: false,
                phoneNumber: 'invalid',
                callingCountryCode: '',
                countryCode: '',
                carrierName: '',
                lineType: 'unknown'
            );

            expect($phone->valid)->toBeFalse()
                ->and($phone->phoneNumber)->toBe('invalid')
                ->and($phone->carrierName)->toBe('')
                ->and($phone->lineType)->toBe('unknown');
        });

        it('handles different line types', function () {
            $lineTypes = ['mobile', 'landline', 'voip', 'unknown'];

            foreach ($lineTypes as $lineType) {
                $phone = new Phone(
                    valid: true,
                    phoneNumber: '+14155552671',
                    callingCountryCode: '1',
                    countryCode: 'US',
                    carrierName: 'Test Carrier',
                    lineType: $lineType
                );

                expect($phone->lineType)->toBe($lineType);
            }
        });

        it('handles different country codes', function () {
            $testCases = [
                ['US', '1', '+14155552671'],
                ['GB', '44', '+442071234567'],
                ['FR', '33', '+33123456789'],
                ['JP', '81', '+81312345678'],
            ];

            foreach ($testCases as [$countryCode, $callingCode, $phoneNumber]) {
                $phone = new Phone(
                    valid: true,
                    phoneNumber: $phoneNumber,
                    callingCountryCode: $callingCode,
                    countryCode: $countryCode,
                    carrierName: 'Test Carrier',
                    lineType: 'mobile'
                );

                expect($phone->countryCode)->toBe($countryCode)
                    ->and($phone->callingCountryCode)->toBe($callingCode)
                    ->and($phone->phoneNumber)->toBe($phoneNumber);
            }
        });
    });

    describe('immutability', function () {
        it('is readonly and immutable', function () {
            $phone = new Phone(
                valid: true,
                phoneNumber: '+14155552671',
                callingCountryCode: '1',
                countryCode: 'US',
                carrierName: 'Verizon',
                lineType: 'mobile'
            );

            $reflection = new ReflectionClass($phone);
            $properties = $reflection->getProperties();

            foreach ($properties as $property) {
                expect($property->isReadOnly())->toBeTrue(
                    "Property {$property->getName()} should be readonly"
                );
            }
        });

        it('maintains data integrity', function () {
            $phone = new Phone(
                valid: true,
                phoneNumber: '+14155552671',
                callingCountryCode: '1',
                countryCode: 'US',
                carrierName: 'Verizon',
                lineType: 'mobile'
            );

            // Verify data doesn't change
            expect($phone->phoneNumber)->toBe('+14155552671');
            expect($phone->phoneNumber)->toBe('+14155552671'); // Second call
            expect($phone->valid)->toBeTrue();
            expect($phone->valid)->toBeTrue(); // Second call
        });
    });

    describe('serialization with GraniteDTO', function () {
        it('can be serialized to array', function () {
            $phone = new Phone(
                valid: true,
                phoneNumber: '+14155552671',
                callingCountryCode: '1',
                countryCode: 'US',
                carrierName: 'Verizon',
                lineType: 'mobile'
            );

            $array = $phone->array();

            expect($array)->toBeArray()
                ->and($array)->toHaveKeys([
                    'valid', 'phoneNumber', 'callingCountryCode', 
                    'countryCode', 'carrierName', 'lineType'
                ])
                ->and($array['valid'])->toBeTrue()
                ->and($array['phoneNumber'])->toBe('+14155552671')
                ->and($array['callingCountryCode'])->toBe('1')
                ->and($array['countryCode'])->toBe('US')
                ->and($array['carrierName'])->toBe('Verizon')
                ->and($array['lineType'])->toBe('mobile');
        });

        it('can be created from array', function () {
            $data = [
                'valid' => true,
                'phoneNumber' => '+14155552671',
                'callingCountryCode' => '1',
                'countryCode' => 'US',
                'carrierName' => 'Verizon',
                'lineType' => 'mobile',
            ];

            $phone = Phone::from($data);

            expect($phone)->toBeInstanceOf(Phone::class)
                ->and($phone->valid)->toBeTrue()
                ->and($phone->phoneNumber)->toBe('+14155552671')
                ->and($phone->callingCountryCode)->toBe('1')
                ->and($phone->countryCode)->toBe('US')
                ->and($phone->carrierName)->toBe('Verizon')
                ->and($phone->lineType)->toBe('mobile');
        });

        it('maintains consistency through serialization roundtrip', function () {
            $original = new Phone(
                valid: false,
                phoneNumber: '+442071234567',
                callingCountryCode: '44',
                countryCode: 'GB',
                carrierName: 'British Telecom',
                lineType: 'landline'
            );

            $array = $original->array();
            $restored = Phone::from($array);

            expect($restored->valid)->toBe($original->valid)
                ->and($restored->phoneNumber)->toBe($original->phoneNumber)
                ->and($restored->callingCountryCode)->toBe($original->callingCountryCode)
                ->and($restored->countryCode)->toBe($original->countryCode)
                ->and($restored->carrierName)->toBe($original->carrierName)
                ->and($restored->lineType)->toBe($original->lineType);
        });

        it('can create from JSON string', function () {
            $json = '{"valid":true,"phoneNumber":"+14155552671","callingCountryCode":"1","countryCode":"US","carrierName":"Verizon","lineType":"mobile"}';
            
            $phone = Phone::from($json);

            expect($phone)->toBeInstanceOf(Phone::class)
                ->and($phone->valid)->toBeTrue()
                ->and($phone->phoneNumber)->toBe('+14155552671')
                ->and($phone->carrierName)->toBe('Verizon');
        });
    });

    describe('edge cases and validation', function () {
        it('handles empty carrier names', function () {
            $phone = new Phone(
                valid: false,
                phoneNumber: '+14155552671',
                callingCountryCode: '1',
                countryCode: 'US',
                carrierName: '',
                lineType: 'unknown'
            );

            expect($phone->carrierName)->toBe('')
                ->and($phone->valid)->toBeFalse();
        });

        it('handles international phone numbers', function () {
            $internationalNumbers = [
                ['+442071234567', '44', 'GB'],
                ['+33123456789', '33', 'FR'],
                ['+81312345678', '81', 'JP'],
                ['+86012345678', '86', 'CN'],
                ['+5511987654321', '55', 'BR'],
            ];

            foreach ($internationalNumbers as [$number, $callingCode, $countryCode]) {
                $phone = new Phone(
                    valid: true,
                    phoneNumber: $number,
                    callingCountryCode: $callingCode,
                    countryCode: $countryCode,
                    carrierName: 'Test Carrier',
                    lineType: 'mobile'
                );

                expect($phone->phoneNumber)->toBe($number)
                    ->and($phone->callingCountryCode)->toBe($callingCode)
                    ->and($phone->countryCode)->toBe($countryCode);
            }
        });

        it('handles various line types correctly', function () {
            $lineTypes = [
                'mobile',
                'landline', 
                'voip',
                'toll-free',
                'premium',
                'unknown',
                'personal',
                'business'
            ];

            foreach ($lineTypes as $lineType) {
                $phone = new Phone(
                    valid: true,
                    phoneNumber: '+14155552671',
                    callingCountryCode: '1',
                    countryCode: 'US',
                    carrierName: 'Test Carrier',
                    lineType: $lineType
                );

                expect($phone->lineType)->toBe($lineType);
            }
        });

        it('handles special characters in carrier names', function () {
            $carrierNames = [
                'AT&T',
                "O'Reilly Mobile",
                'T-Mobile',
                'Carrier with (parentheses)',
                'Carrier/Subsidiary',
                '한국통신', // Korean characters
                'Telefónica', // Accented characters
            ];

            foreach ($carrierNames as $carrierName) {
                $phone = new Phone(
                    valid: true,
                    phoneNumber: '+14155552671',
                    callingCountryCode: '1',
                    countryCode: 'US',
                    carrierName: $carrierName,
                    lineType: 'mobile'
                );

                expect($phone->carrierName)->toBe($carrierName);
            }
        });
    });

    describe('real-world scenarios', function () {
        it('handles typical US mobile number', function () {
            $phone = new Phone(
                valid: true,
                phoneNumber: '+14155552671',
                callingCountryCode: '1',
                countryCode: 'US',
                carrierName: 'Verizon Wireless',
                lineType: 'mobile'
            );

            expect($phone->valid)->toBeTrue()
                ->and($phone->phoneNumber)->toStartWith('+1')
                ->and($phone->countryCode)->toBe('US')
                ->and($phone->lineType)->toBe('mobile');
        });

        it('handles invalid phone number format', function () {
            $phone = new Phone(
                valid: false,
                phoneNumber: '123',
                callingCountryCode: '',
                countryCode: '',
                carrierName: '',
                lineType: 'unknown'
            );

            expect($phone->valid)->toBeFalse()
                ->and($phone->phoneNumber)->toBe('123')
                ->and($phone->lineType)->toBe('unknown');
        });

        it('handles VOIP numbers', function () {
            $phone = new Phone(
                valid: true,
                phoneNumber: '+14155552671',
                callingCountryCode: '1',
                countryCode: 'US',
                carrierName: 'Google Voice',
                lineType: 'voip'
            );

            expect($phone->valid)->toBeTrue()
                ->and($phone->carrierName)->toBe('Google Voice')
                ->and($phone->lineType)->toBe('voip');
        });

        it('handles toll-free numbers', function () {
            $phone = new Phone(
                valid: true,
                phoneNumber: '+18005551234',
                callingCountryCode: '1',
                countryCode: 'US',
                carrierName: 'Toll Free Service',
                lineType: 'toll-free'
            );

            expect($phone->phoneNumber)->toStartWith('+1800')
                ->and($phone->lineType)->toBe('toll-free');
        });
    });
});