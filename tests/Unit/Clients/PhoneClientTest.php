<?php

use Ninja\Verisoul\Clients\PhoneClient;
use Ninja\Verisoul\Enums\VerisoulEnvironment;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Ninja\Verisoul\Responses\VerifyPhoneResponse;
use Ninja\Verisoul\Tests\Helpers\MockFactory;
use Ninja\Verisoul\Contracts\HttpClientInterface;

describe('PhoneClient', function () {
    describe('construction', function () {
        it('can be created with default parameters', function () {
            $client = new PhoneClient('test_api_key');
            
            expect($client)->toBeInstanceOf(PhoneClient::class)
                ->and($client->getEnvironment())->toBe(VerisoulEnvironment::Sandbox);
        });

        it('can be created with custom environment', function () {
            $client = new PhoneClient('prod_key', VerisoulEnvironment::Production);
            
            expect($client->getEnvironment())->toBe(VerisoulEnvironment::Production);
        });

        it('inherits from Client base class', function () {
            $client = new PhoneClient('test_api_key');
            
            expect($client)->toBeInstanceOf(\Ninja\Verisoul\Clients\Client::class);
        });

        it('implements PhoneInterface', function () {
            $client = new PhoneClient('test_api_key');
            
            expect($client)->toBeInstanceOf(\Ninja\Verisoul\Contracts\PhoneInterface::class);
        });
    });

    describe('verifyPhone method', function () {
        it('creates VerifyPhoneResponse object', function () {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'post' => [
                    'phone_number' => '+1234567890',
                    'is_valid' => true,
                    'carrier' => 'Verizon',
                    'line_type' => 'mobile',
                    'country_code' => 'US'
                ]
            ]);

            $client = new PhoneClient('test_api_key', httpClient: $mockHttpClient);
            
            $response = $client->verifyPhone('+1234567890');

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });

        it('passes phone number correctly in request data', function () {
            $phoneNumber = '+15551234567';

            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();
            
            $mockHttpClient->shouldReceive('post')
                ->once()
                ->withArgs(function ($url, $data) use ($phoneNumber) {
                    return str_contains($url, '/phone') &&
                           isset($data['phone_number']) &&
                           $data['phone_number'] === $phoneNumber;
                })
                ->andReturn([
                    'phone_number' => $phoneNumber,
                    'is_valid' => true,
                    'carrier' => 'AT&T'
                ]);

            $client = new PhoneClient('test_key', httpClient: $mockHttpClient);
            $response = $client->verifyPhone($phoneNumber);

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });

        it('handles US phone numbers', function () {
            $usPhoneNumbers = [
                '+1234567890',
                '+15551234567',
                '+1800555HELP',
                '+12125551234',  // NYC
                '+14151234567',  // San Francisco
                '+13105551234'   // Los Angeles
            ];

            foreach ($usPhoneNumbers as $phoneNumber) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'post' => [
                        'phone_number' => $phoneNumber,
                        'is_valid' => true,
                        'country_code' => 'US',
                        'carrier' => 'Test Carrier'
                    ]
                ]);
                
                $client = new PhoneClient('test_key', httpClient: $mockHttpClient);
                $response = $client->verifyPhone($phoneNumber);
                
                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });

        it('handles international phone numbers', function () {
            $internationalNumbers = [
                '+44207123456',     // UK London
                '+33142345678',     // France Paris
                '+4930123456',      // Germany Berlin
                '+81312345678',     // Japan Tokyo
                '+861012345678',    // China Beijing
                '+5511987654321',   // Brazil São Paulo
                '+61287654321',     // Australia Sydney
                '+917012345678'     // India Mumbai
            ];

            foreach ($internationalNumbers as $phoneNumber) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'post' => [
                        'phone_number' => $phoneNumber,
                        'is_valid' => true,
                        'carrier' => 'International Carrier'
                    ]
                ]);
                
                $client = new PhoneClient('test_key', httpClient: $mockHttpClient);
                $response = $client->verifyPhone($phoneNumber);
                
                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });

        it('handles invalid phone number formats', function () {
            $invalidNumbers = [
                '123-456-7890',     // US format without country code
                '(555) 123-4567',   // US format with parentheses
                '555.123.4567',     // US format with dots
                '1234567890',       // No country code or symbols
                '+1-555-123-4567',  // Hyphens in international format
                'invalid_phone'     // Non-numeric content
            ];

            foreach ($invalidNumbers as $phoneNumber) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'post' => [
                        'phone_number' => $phoneNumber,
                        'is_valid' => false,
                        'error' => 'Invalid phone number format'
                    ]
                ]);
                
                $client = new PhoneClient('test_key', httpClient: $mockHttpClient);
                $response = $client->verifyPhone($phoneNumber);
                
                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });

        it('handles empty and null phone numbers', function () {
            $edgeCaseNumbers = ['', '   ', '+'];

            foreach ($edgeCaseNumbers as $phoneNumber) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'post' => [
                        'phone_number' => $phoneNumber,
                        'is_valid' => false,
                        'error' => 'Phone number is required'
                    ]
                ]);
                
                $client = new PhoneClient('test_key', httpClient: $mockHttpClient);
                $response = $client->verifyPhone($phoneNumber);
                
                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });

        it('throws VerisoulConnectionException on connection failure', function () {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(PhoneClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->verifyPhone('+1234567890'))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function () {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(PhoneClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->verifyPhone('+1234567890'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('environment integration', function () {
        it('uses sandbox URLs in sandbox environment', function () {
            $client = new PhoneClient('sandbox_key', VerisoulEnvironment::Sandbox);
            
            expect($client->getBaseUrl())->toBe('https://api.sandbox.verisoul.ai');
        });

        it('uses production URLs in production environment', function () {
            $client = new PhoneClient('prod_key', VerisoulEnvironment::Production);
            
            expect($client->getBaseUrl())->toBe('https://api.verisoul.ai');
        });

        it('makes requests to correct environment', function () {
            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();
            
            $mockHttpClient->shouldReceive('post')
                ->once()
                ->withArgs(function ($url, $data) {
                    return str_contains($url, 'https://api.verisoul.ai');
                })
                ->andReturn([
                    'phone_number' => '+1234567890',
                    'is_valid' => true
                ]);

            $prodClient = new PhoneClient(
                'prod_key', 
                VerisoulEnvironment::Production, 
                httpClient: $mockHttpClient
            );
            
            $prodClient->verifyPhone('+1234567890');
        });
    });

    describe('response object handling', function () {
        it('correctly creates VerifyPhoneResponse from API response', function () {
            $apiResponse = [
                'phone_number' => '+15551234567',
                'is_valid' => true,
                'carrier' => 'Verizon Wireless',
                'line_type' => 'mobile',
                'country_code' => 'US',
                'national_format' => '(555) 123-4567',
                'international_format' => '+1 555-123-4567',
                'risk_score' => 0.15,
                'carrier_risk' => 'low',
                'line_type_confidence' => 0.95,
                'roaming_status' => 'domestic'
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $apiResponse]);
            $client = new PhoneClient('test_key', httpClient: $mockHttpClient);

            $response = $client->verifyPhone('+15551234567');

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });

        it('handles comprehensive phone verification response', function () {
            $comprehensiveResponse = [
                'phone_number' => '+447891234567',
                'is_valid' => true,
                'carrier' => 'Vodafone UK',
                'line_type' => 'mobile',
                'country_code' => 'GB',
                'country_name' => 'United Kingdom',
                'national_format' => '07891 234567',
                'international_format' => '+44 7891 234567',
                'e164_format' => '+447891234567',
                'region' => 'London',
                'timezone' => 'Europe/London',
                'risk_score' => 0.25,
                'carrier_risk' => 'medium',
                'line_type_confidence' => 0.92,
                'roaming_status' => 'international',
                'ported' => false,
                'do_not_call_registered' => false,
                'reachability' => 'reachable',
                'validation_errors' => [],
                'additional_info' => [
                    'number_type' => 'geographic',
                    'usage_type' => 'personal'
                ]
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $comprehensiveResponse]);
            $client = new PhoneClient('test_key', httpClient: $mockHttpClient);

            $response = $client->verifyPhone('+447891234567');

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });

        it('handles invalid phone response', function () {
            $invalidResponse = [
                'phone_number' => 'invalid_phone',
                'is_valid' => false,
                'error_code' => 'INVALID_FORMAT',
                'error_message' => 'Phone number format is invalid',
                'carrier' => null,
                'line_type' => null,
                'country_code' => null,
                'risk_score' => 1.0,
                'validation_errors' => [
                    'format' => 'Phone number must start with country code',
                    'length' => 'Phone number length is invalid'
                ]
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $invalidResponse]);
            $client = new PhoneClient('test_key', httpClient: $mockHttpClient);

            $response = $client->verifyPhone('invalid_phone');

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });
    });

    describe('real-world usage scenarios', function () {
        it('handles user registration phone verification', function () {
            $registrationResponse = [
                'phone_number' => '+12345551234',
                'is_valid' => true,
                'carrier' => 'T-Mobile USA',
                'line_type' => 'mobile',
                'country_code' => 'US',
                'risk_score' => 0.1,
                'carrier_risk' => 'low',
                'reachability' => 'reachable',
                'do_not_call_registered' => false,
                'verification_recommendation' => 'sms_ok'
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $registrationResponse]);
            $client = new PhoneClient('test_key', httpClient: $mockHttpClient);

            $response = $client->verifyPhone('+12345551234');

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });

        it('handles fraud detection phone check', function () {
            $fraudCheckResponse = [
                'phone_number' => '+15551234567',
                'is_valid' => true,
                'carrier' => 'Suspicious Carrier',
                'line_type' => 'voip',
                'country_code' => 'US',
                'risk_score' => 0.85,
                'carrier_risk' => 'high',
                'reachability' => 'unreachable',
                'ported' => true,
                'number_age_days' => 7,
                'fraud_indicators' => [
                    'recently_ported',
                    'voip_service',
                    'high_risk_carrier'
                ],
                'verification_recommendation' => 'manual_review'
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $fraudCheckResponse]);
            $client = new PhoneClient('test_key', httpClient: $mockHttpClient);

            $response = $client->verifyPhone('+15551234567');

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });

        it('handles international business verification', function () {
            $businessResponse = [
                'phone_number' => '+442071234567',
                'is_valid' => true,
                'carrier' => 'BT Group',
                'line_type' => 'landline',
                'country_code' => 'GB',
                'country_name' => 'United Kingdom',
                'region' => 'London',
                'business_hours' => '09:00-17:00 GMT',
                'risk_score' => 0.05,
                'carrier_risk' => 'low',
                'reachability' => 'reachable',
                'number_type' => 'geographic',
                'usage_type' => 'business',
                'business_verified' => true,
                'verification_recommendation' => 'voice_call_ok'
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $businessResponse]);
            $client = new PhoneClient('test_key', httpClient: $mockHttpClient);

            $response = $client->verifyPhone('+442071234567');

            expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
        });

        it('handles bulk phone verification workflow', function () {
            $bulkPhones = [
                '+12125551234',  // NYC landline
                '+14151234567',  // SF mobile
                '+13105556789',  // LA mobile
                '+18005551234',  // Toll-free
                '+15551234567'   // Generic mobile
            ];

            foreach ($bulkPhones as $phone) {
                $response = [
                    'phone_number' => $phone,
                    'is_valid' => true,
                    'carrier' => 'Test Carrier',
                    'country_code' => 'US',
                    'processed_at' => date('c')
                ];

                $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $response]);
                $client = new PhoneClient('test_key', httpClient: $mockHttpClient);

                $result = $client->verifyPhone($phone);
                expect($result)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });
    });

    describe('phone number format scenarios', function () {
        it('handles various valid international formats', function () {
            $validFormats = [
                '+1234567890' => 'US standard',
                '+441234567890' => 'UK mobile',
                '+33123456789' => 'France mobile',
                '+49123456789' => 'Germany mobile',
                '+81901234567' => 'Japan mobile',
                '+86138000000' => 'China mobile',
                '+5511987654321' => 'Brazil mobile',
                '+919876543210' => 'India mobile'
            ];

            foreach ($validFormats as $phoneNumber => $description) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'post' => [
                        'phone_number' => $phoneNumber,
                        'is_valid' => true,
                        'description' => $description
                    ]
                ]);
                
                $client = new PhoneClient('test_key', httpClient: $mockHttpClient);
                $response = $client->verifyPhone($phoneNumber);
                
                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });

        it('handles various invalid formats gracefully', function () {
            $invalidFormats = [
                '123-456-7890' => 'Missing country code',
                '(555) 123-4567' => 'US domestic format',
                '555.123.4567' => 'Dotted format',
                '+1-555-123-4567' => 'Hyphenated international',
                'abc-def-ghij' => 'Non-numeric',
                '+' => 'Just plus sign',
                '++1234567890' => 'Double plus',
                '+1 (555) 123-4567 ext 123' => 'With extension'
            ];

            foreach ($invalidFormats as $phoneNumber => $reason) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'post' => [
                        'phone_number' => $phoneNumber,
                        'is_valid' => false,
                        'error_reason' => $reason
                    ]
                ]);
                
                $client = new PhoneClient('test_key', httpClient: $mockHttpClient);
                $response = $client->verifyPhone($phoneNumber);
                
                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });

        it('handles edge case phone numbers', function () {
            $edgeCases = [
                '+15555555555' => 'Repeating digits',
                '+11234567890' => 'Starting with 1',
                '+0123456789' => 'Starting with 0',
                '+999999999999999' => 'Very long number',
                '+1' => 'Too short',
                '+123' => 'Minimal digits'
            ];

            foreach ($edgeCases as $phoneNumber => $description) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'post' => [
                        'phone_number' => $phoneNumber,
                        'is_valid' => str_starts_with($phoneNumber, '+1') && strlen($phoneNumber) >= 10,
                        'edge_case' => $description
                    ]
                ]);
                
                $client = new PhoneClient('test_key', httpClient: $mockHttpClient);
                $response = $client->verifyPhone($phoneNumber);
                
                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });
    });

    describe('carrier and line type scenarios', function () {
        it('handles different carrier types', function () {
            $carrierScenarios = [
                ['phone' => '+15551111111', 'carrier' => 'Verizon', 'type' => 'major'],
                ['phone' => '+15552222222', 'carrier' => 'AT&T', 'type' => 'major'],
                ['phone' => '+15553333333', 'carrier' => 'T-Mobile', 'type' => 'major'],
                ['phone' => '+15554444444', 'carrier' => 'Sprint', 'type' => 'major'],
                ['phone' => '+15555555555', 'carrier' => 'Google Voice', 'type' => 'voip'],
                ['phone' => '+15556666666', 'carrier' => 'Skype', 'type' => 'voip'],
                ['phone' => '+15557777777', 'carrier' => 'Regional Carrier', 'type' => 'regional']
            ];

            foreach ($carrierScenarios as $scenario) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'post' => [
                        'phone_number' => $scenario['phone'],
                        'is_valid' => true,
                        'carrier' => $scenario['carrier'],
                        'carrier_type' => $scenario['type'],
                        'line_type' => $scenario['type'] === 'voip' ? 'voip' : 'mobile'
                    ]
                ]);
                
                $client = new PhoneClient('test_key', httpClient: $mockHttpClient);
                $response = $client->verifyPhone($scenario['phone']);
                
                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });

        it('handles different line types', function () {
            $lineTypeScenarios = [
                ['phone' => '+15551111111', 'line_type' => 'mobile', 'description' => 'Standard mobile'],
                ['phone' => '+15552222222', 'line_type' => 'landline', 'description' => 'Traditional landline'],
                ['phone' => '+15553333333', 'line_type' => 'voip', 'description' => 'Voice over IP'],
                ['phone' => '+18005555555', 'line_type' => 'toll_free', 'description' => 'Toll-free number'],
                ['phone' => '+19005555555', 'line_type' => 'premium', 'description' => 'Premium rate'],
                ['phone' => '+15554444444', 'line_type' => 'unknown', 'description' => 'Unknown type']
            ];

            foreach ($lineTypeScenarios as $scenario) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'post' => [
                        'phone_number' => $scenario['phone'],
                        'is_valid' => true,
                        'line_type' => $scenario['line_type'],
                        'line_description' => $scenario['description']
                    ]
                ]);
                
                $client = new PhoneClient('test_key', httpClient: $mockHttpClient);
                $response = $client->verifyPhone($scenario['phone']);
                
                expect($response)->toBeInstanceOf(VerifyPhoneResponse::class);
            }
        });
    });
});