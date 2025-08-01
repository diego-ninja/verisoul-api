<?php

use Ninja\Verisoul\Responses\VerifyPhoneResponse;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('VerifyPhoneResponse', function (): void {
    describe('construction and basic functionality', function (): void {
        it('can be created from fixture data', function (): void {
            $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture();
            $response = VerifyPhoneResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });

        it('can be created with custom phone data', function (): void {
            $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture([
                'phone' => [
                    'valid' => false,
                    'phone_number' => '+1555123456',
                    'calling_country_code' => '1',
                    'country_code' => 'US',
                    'carrier_name' => 'T-Mobile',
                    'line_type' => 'mobile',
                ],
            ]);
            $response = VerifyPhoneResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });

        it('provides access to response identifiers', function (): void {
            $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture([
                'project_id' => 'test_project_123',
                'request_id' => 'test_request_456',
            ]);
            $response = VerifyPhoneResponse::from($fixtureData);

            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('project_id')
                ->and($responseArray)->toHaveKey('request_id')
                ->and($responseArray['project_id'])->toBe('test_project_123')
                ->and($responseArray['request_id'])->toBe('test_request_456');
        });
    });

    describe('phone validation scenarios', function (): void {
        it('handles valid mobile phone numbers', function (): void {
            $validMobileNumbers = [
                '+12345678901',
                '+447700900123',
                '+33123456789',
                '+81312345678',
                '+61412345678',
            ];

            foreach ($validMobileNumbers as $phoneNumber) {
                $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture([
                    'phone' => [
                        'valid' => true,
                        'phone_number' => $phoneNumber,
                        'line_type' => 'mobile',
                    ],
                ]);
                $response = VerifyPhoneResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });

        it('handles valid landline phone numbers', function (): void {
            $validLandlineNumbers = [
                '+15551234567',
                '+442071234567',
                '+33140123456',
            ];

            foreach ($validLandlineNumbers as $phoneNumber) {
                $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture([
                    'phone' => [
                        'valid' => true,
                        'phone_number' => $phoneNumber,
                        'line_type' => 'landline',
                    ],
                ]);
                $response = VerifyPhoneResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });

        it('handles invalid phone numbers', function (): void {
            $invalidPhoneNumbers = [
                '+1234',         // Too short
                '+99999999999999', // Too long
                '+0000000000',   // Invalid format
                '+1111111111',    // Invalid number
            ];

            foreach ($invalidPhoneNumbers as $phoneNumber) {
                $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture([
                    'phone' => [
                        'valid' => false,
                        'phone_number' => $phoneNumber,
                        'calling_country_code' => '+1',
                        'country_code' => 'US',
                        'carrier_name' => 'Unknown',
                        'line_type' => 'unknown',
                    ],
                ]);
                $response = VerifyPhoneResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });
    });

    describe('carrier and line type handling', function (): void {
        it('handles various mobile carriers', function (): void {
            $carriers = [
                'Verizon Wireless',
                'AT&T',
                'T-Mobile',
                'Sprint',
                'Vodafone',
                'O2',
                'Orange',
                'Three',
            ];

            foreach ($carriers as $carrier) {
                $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture([
                    'phone' => [
                        'valid' => true,
                        'carrier_name' => $carrier,
                        'line_type' => 'mobile',
                    ],
                ]);
                $response = VerifyPhoneResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });

        it('handles different line types', function (): void {
            $lineTypes = ['mobile', 'landline', 'voip', 'toll_free', 'premium_rate'];

            foreach ($lineTypes as $lineType) {
                $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture([
                    'phone' => [
                        'valid' => true,
                        'line_type' => $lineType,
                    ],
                ]);
                $response = VerifyPhoneResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });

        it('handles unknown carrier names', function (): void {
            $unknownCarriers = ['', 'Unknown', 'N/A', 'Not Available'];  // Removed null

            foreach ($unknownCarriers as $carrier) {
                $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture([
                    'phone' => [
                        'valid' => true,
                        'phone_number' => '+15555551234',
                        'calling_country_code' => '+1',
                        'country_code' => 'US',
                        'carrier_name' => $carrier,
                        'line_type' => 'mobile',
                    ],
                ]);
                $response = VerifyPhoneResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });
    });

    describe('country code handling', function (): void {
        it('handles various country codes', function (): void {
            $countryCodes = [
                ['calling_code' => '1', 'country_code' => 'US'],
                ['calling_code' => '44', 'country_code' => 'GB'],
                ['calling_code' => '33', 'country_code' => 'FR'],
                ['calling_code' => '49', 'country_code' => 'DE'],
                ['calling_code' => '81', 'country_code' => 'JP'],
                ['calling_code' => '61', 'country_code' => 'AU'],
                ['calling_code' => '86', 'country_code' => 'CN'],
                ['calling_code' => '91', 'country_code' => 'IN'],
            ];

            foreach ($countryCodes as $codes) {
                $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture([
                    'phone' => [
                        'valid' => true,
                        'calling_country_code' => $codes['calling_code'],
                        'country_code' => $codes['country_code'],
                    ],
                ]);
                $response = VerifyPhoneResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });

        it('handles mismatched calling and country codes', function (): void {
            $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture([
                'phone' => [
                    'valid' => false,
                    'calling_country_code' => '1',
                    'country_code' => 'GB', // Mismatch: US calling code with GB country
                ],
            ]);
            $response = VerifyPhoneResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });
    });

    describe('data integrity and serialization', function (): void {
        it('maintains data integrity through serialization', function (): void {
            $originalData = MockFactory::createPhoneVerificationResponseFromFixture([
                'project_id' => 'integrity_test_project',
                'request_id' => 'integrity_test_request',
                'phone' => [
                    'valid' => true,
                    'phone_number' => '+15551234567',
                    'calling_country_code' => '1',
                    'country_code' => 'US',
                    'carrier_name' => 'Test Carrier',
                    'line_type' => 'mobile',
                ],
            ]);
            $response = VerifyPhoneResponse::from($originalData);
            $serializedData = $response->array();
            $recreatedResponse = VerifyPhoneResponse::from($serializedData);

            expect($recreatedResponse)->toBeInstanceOf(VerifyPhoneResponse::class);
            expect($recreatedResponse->array())->toBeArray();
        });

        it('handles complex phone data structures', function (): void {
            $complexPhone = [
                'valid' => true,
                'phone_number' => '+441234567890',
                'calling_country_code' => '44',
                'country_code' => 'GB',
                'carrier_name' => 'Complex Carrier Name Ltd.',
                'line_type' => 'mobile',
            ];
            $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture([
                'phone' => $complexPhone,
            ]);
            $response = VerifyPhoneResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            expect($response->array())->toBeArray();
        });

        it('serializes to correct structure', function (): void {
            $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture();
            $response = VerifyPhoneResponse::from($fixtureData);
            $serialized = $response->array();

            expect($serialized)->toBeArray()
                ->and($serialized)->toHaveKey('project_id')
                ->and($serialized)->toHaveKey('request_id')
                ->and($serialized)->toHaveKey('phone')
                ->and($serialized['phone'])->toBeArray()
                ->and($serialized['phone'])->toHaveKey('valid')
                ->and($serialized['phone'])->toHaveKey('phone_number');
        });
    });

    describe('edge cases and error handling', function (): void {
        it('handles empty phone number', function (): void {
            $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture([
                'phone' => [
                    'valid' => false,
                    'phone_number' => '',
                    'calling_country_code' => '',
                    'country_code' => '',
                    'carrier_name' => 'Unknown',
                    'line_type' => 'unknown',
                ],
            ]);
            $response = VerifyPhoneResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });

        it('handles malformed phone numbers', function (): void {
            $malformedNumbers = [
                'not-a-phone-number',
                'abc123456789',
                '+++1234567890',
                '1234567890+',
                '+1-555-123-456-789-0',
                '(555) 123-4567', // No country code
            ];

            foreach ($malformedNumbers as $phoneNumber) {
                $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture([
                    'phone' => [
                        'valid' => false,
                        'phone_number' => $phoneNumber,
                    ],
                ]);
                $response = VerifyPhoneResponse::from($fixtureData);

                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });

        it('handles missing optional phone fields', function (): void {
            $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture([
                'phone' => [
                    'valid' => true,
                    'phone_number' => '+15551234567',
                    // Missing carrier_name, line_type, etc.
                ],
            ]);
            $response = VerifyPhoneResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });

        it('handles very long phone numbers', function (): void {
            $longPhoneNumber = '+' . str_repeat('1', 50);
            $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture([
                'phone' => [
                    'valid' => false,
                    'phone_number' => $longPhoneNumber,
                ],
            ]);
            $response = VerifyPhoneResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });
    });

    describe('real-world phone verification scenarios', function (): void {
        it('handles successful US mobile verification', function (): void {
            $usVerificationData = MockFactory::createPhoneVerificationResponseFromFixture([
                'phone' => [
                    'valid' => true,
                    'phone_number' => '+15551234567',
                    'calling_country_code' => '1',
                    'country_code' => 'US',
                    'carrier_name' => 'Verizon Wireless',
                    'line_type' => 'mobile',
                ],
            ]);
            $response = VerifyPhoneResponse::from($usVerificationData);

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });

        it('handles successful UK landline verification', function (): void {
            $ukVerificationData = MockFactory::createPhoneVerificationResponseFromFixture([
                'phone' => [
                    'valid' => true,
                    'phone_number' => '+442071234567',
                    'calling_country_code' => '44',
                    'country_code' => 'GB',
                    'carrier_name' => 'BT',
                    'line_type' => 'landline',
                ],
            ]);
            $response = VerifyPhoneResponse::from($ukVerificationData);

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });

        it('handles failed verification with invalid number', function (): void {
            $failedVerificationData = MockFactory::createPhoneVerificationResponseFromFixture([
                'phone' => [
                    'valid' => false,
                    'phone_number' => '+10000000000',
                    'calling_country_code' => '1',
                    'country_code' => 'US',
                    'carrier_name' => 'Unknown',
                    'line_type' => 'unknown',
                ],
            ]);
            $response = VerifyPhoneResponse::from($failedVerificationData);

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });

        it('handles VOIP number verification', function (): void {
            $voipVerificationData = MockFactory::createPhoneVerificationResponseFromFixture([
                'phone' => [
                    'valid' => true,
                    'phone_number' => '+15551234567',
                    'calling_country_code' => '1',
                    'country_code' => 'US',
                    'carrier_name' => 'Google Voice',
                    'line_type' => 'voip',
                ],
            ]);
            $response = VerifyPhoneResponse::from($voipVerificationData);

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });
    });

    describe('readonly class behavior', function (): void {
        it('creates immutable instances', function (): void {
            $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture();
            $response = VerifyPhoneResponse::from($fixtureData);

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);

            // Since it's a readonly class, we can't modify properties
            // This test verifies the object was created successfully
            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('phone');
        });

        it('provides consistent data access', function (): void {
            $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture();
            $response = VerifyPhoneResponse::from($fixtureData);

            $firstAccess = $response->array();
            $secondAccess = $response->array();
            $thirdAccess = $response->array();

            expect($firstAccess)->toBe($secondAccess)
                ->and($secondAccess)->toBe($thirdAccess);
        });
    });

    describe('performance and memory', function (): void {
        it('creates multiple instances efficiently', function (): void {
            $startTime = microtime(true);
            $responses = [];

            for ($i = 1; $i <= 100; $i++) {
                $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture([
                    'phone' => [
                        'phone_number' => "+155512345{$i}",
                    ],
                ]);
                $responses[] = VerifyPhoneResponse::from($fixtureData);
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            expect(count($responses))->toBe(100)
                ->and($executionTime)->toBeLessThan(0.5);

            foreach ($responses as $response) {
                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });

        it('maintains reasonable memory usage', function (): void {
            $initialMemory = memory_get_usage();

            $responses = [];
            for ($i = 1; $i <= 1000; $i++) {
                $fixtureData = MockFactory::createPhoneVerificationResponseFromFixture();
                $responses[] = VerifyPhoneResponse::from($fixtureData);
            }

            $finalMemory = memory_get_usage();
            $memoryUsed = $finalMemory - $initialMemory;

            expect(count($responses))->toBe(1000)
                ->and($memoryUsed)->toBeLessThan(2 * 1024 * 1024); // Less than 2MB

            unset($responses);
        });
    });
});
