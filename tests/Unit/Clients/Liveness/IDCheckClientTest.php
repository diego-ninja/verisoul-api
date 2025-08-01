<?php

use Ninja\Verisoul\Clients\Liveness\IDCheckClient;
use Ninja\Verisoul\Contracts\HttpClientInterface;
use Ninja\Verisoul\DTO\UserAccount;
use Ninja\Verisoul\Enums\VerisoulEnvironment;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Ninja\Verisoul\Responses\EnrollAccountResponse;
use Ninja\Verisoul\Responses\LivenessSessionResponse;
use Ninja\Verisoul\Responses\VerifyIdResponse;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('IDCheckClient', function (): void {
    describe('construction', function (): void {
        it('can be created with default parameters', function (): void {
            $client = new IDCheckClient('test_api_key');

            expect($client)->toBeInstanceOf(IDCheckClient::class)
                ->and($client->getEnvironment())->toBe(VerisoulEnvironment::Sandbox);
        });

        it('can be created with custom environment', function (): void {
            $client = new IDCheckClient('prod_key', VerisoulEnvironment::Production);

            expect($client->getEnvironment())->toBe(VerisoulEnvironment::Production);
        });

        it('inherits from LivenessApiClient', function (): void {
            $client = new IDCheckClient('test_api_key');

            expect($client)->toBeInstanceOf(Ninja\Verisoul\Clients\Liveness\LivenessApiClient::class);
        });

        it('implements IDCheckInterface', function (): void {
            $client = new IDCheckClient('test_api_key');

            expect($client)->toBeInstanceOf(Ninja\Verisoul\Contracts\IDCheckInterface::class);
        });

        it('implements BiometricInterface through inheritance', function (): void {
            $client = new IDCheckClient('test_api_key');

            expect($client)->toBeInstanceOf(Ninja\Verisoul\Contracts\BiometricInterface::class);
        });
    });

    describe('session method', function (): void {
        it('creates LivenessSessionResponse object without referring session', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => [
                    'session_id' => 'id_check_session_123',
                    'session_url' => 'https://liveness.verisoul.ai/id-check/session_123',
                    'expires_at' => '2024-01-15T12:30:00Z',
                    'type' => 'id_check',
                    'id_required' => true,
                ],
            ]);

            $client = new IDCheckClient('test_api_key', httpClient: $mockHttpClient);

            $response = $client->session();

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);
        });

        it('creates LivenessSessionResponse object with referring session', function (): void {
            $referringSessionId = 'referring_session_456';

            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('get')
                ->once()
                ->withArgs(fn($url, $params) => str_contains($url, '/liveness/session') &&
                           str_contains($url, "id=true") &&
                           str_contains($url, "referring_session_id={$referringSessionId}"))
                ->andReturn([
                    'session_id' => 'id_check_with_ref_789',
                    'referring_session_id' => $referringSessionId,
                    'session_url' => 'https://liveness.verisoul.ai/id-check/session_789',
                    'type' => 'id_check',
                    'id_required' => true,
                ]);

            $client = new IDCheckClient('test_key', httpClient: $mockHttpClient);
            $response = $client->session($referringSessionId);

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);
        });

        it('always includes id parameter in URL', function (): void {
            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('get')
                ->once()
                ->withArgs(fn($url, $params) => str_contains($url, '/liveness/session') &&
                           str_contains($url, 'id=true'))
                ->andReturn([
                    'session_id' => 'id_required_session',
                    'session_url' => 'https://liveness.verisoul.ai/id-check/required',
                    'type' => 'id_check',
                    'id_required' => true,
                ]);

            $client = new IDCheckClient('test_key', httpClient: $mockHttpClient);
            $response = $client->session();

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);
        });

        it('handles null referring session ID', function (): void {
            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('get')
                ->once()
                ->withArgs(fn($url, $params) => str_contains($url, '/liveness/session') &&
                           str_contains($url, 'id=true') &&
                           ! str_contains($url, 'referring_session_id'))
                ->andReturn([
                    'session_id' => 'standalone_id_session',
                    'session_url' => 'https://liveness.verisoul.ai/id-check/standalone',
                    'type' => 'id_check',
                    'id_required' => true,
                ]);

            $client = new IDCheckClient('test_key', httpClient: $mockHttpClient);
            $response = $client->session(null);

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);
        });

        it('throws VerisoulConnectionException on connection failure', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(IDCheckClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->session())
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(IDCheckClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->session('invalid_session'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('verify method', function (): void {
        it('creates VerifyIdResponse object', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'post' => [
                    'session_id' => 'verify_id_session_123',
                    'id_verified' => true,
                    'document_type' => 'drivers_license',
                    'document_country' => 'US',
                    'document_state' => 'CA',
                    'authenticity_score' => 0.96,
                    'extracted_data' => [
                        'name' => 'John Doe',
                        'date_of_birth' => '1990-01-01',
                        'document_number' => 'DL123456789',
                    ],
                ],
            ]);

            $client = new IDCheckClient('test_api_key', httpClient: $mockHttpClient);

            $response = $client->verify('verify_id_session_123');

            expect($response)->toBeInstanceOf(VerifyIdResponse::class);
        });

        it('passes session ID correctly in request data', function (): void {
            $sessionId = 'id_verify_session_456';

            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('post')
                ->once()
                ->withArgs(fn($url, $data) => str_contains($url, '/liveness/verify-id') &&
                           isset($data['session_id']) &&
                           $data['session_id'] === $sessionId)
                ->andReturn([
                    'session_id' => $sessionId,
                    'id_verified' => true,
                    'document_type' => 'passport',
                ]);

            $client = new IDCheckClient('test_key', httpClient: $mockHttpClient);
            $response = $client->verify($sessionId);

            expect($response)->toBeInstanceOf(VerifyIdResponse::class);
        });

        it('handles various document types and verification outcomes', function (): void {
            $documentScenarios = [
                [
                    'session_id' => 'drivers_license_session',
                    'id_verified' => true,
                    'document_type' => 'drivers_license',
                    'document_country' => 'US',
                    'document_state' => 'CA',
                    'authenticity_score' => 0.97,
                    'outcome' => 'verified',
                ],
                [
                    'session_id' => 'passport_session',
                    'id_verified' => true,
                    'document_type' => 'passport',
                    'document_country' => 'US',
                    'authenticity_score' => 0.95,
                    'outcome' => 'verified',
                ],
                [
                    'session_id' => 'national_id_session',
                    'id_verified' => true,
                    'document_type' => 'national_id',
                    'document_country' => 'CA',
                    'authenticity_score' => 0.94,
                    'outcome' => 'verified',
                ],
                [
                    'session_id' => 'fake_document_session',
                    'id_verified' => false,
                    'document_type' => 'unknown',
                    'authenticity_score' => 0.15,
                    'outcome' => 'rejected',
                    'rejection_reasons' => ['document_tampered', 'low_quality'],
                ],
            ];

            foreach ($documentScenarios as $scenario) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $scenario]);
                $client = new IDCheckClient('test_key', httpClient: $mockHttpClient);
                $response = $client->verify($scenario['session_id']);

                expect($response)->toBeInstanceOf(VerifyIdResponse::class);
            }
        });

        it('handles international documents', function (): void {
            $internationalDocs = [
                [
                    'document_type' => 'passport',
                    'document_country' => 'GB',
                    'extracted_data' => ['name' => 'Jane Smith', 'nationality' => 'British'],
                ],
                [
                    'document_type' => 'national_id',
                    'document_country' => 'DE',
                    'extracted_data' => ['name' => 'Hans Mueller', 'nationality' => 'German'],
                ],
                [
                    'document_type' => 'residence_permit',
                    'document_country' => 'FR',
                    'extracted_data' => ['name' => 'Marie Dubois', 'permit_type' => 'permanent'],
                ],
            ];

            foreach ($internationalDocs as $doc) {
                $scenario = array_merge([
                    'session_id' => 'international_session',
                    'id_verified' => true,
                    'authenticity_score' => 0.91,
                ], $doc);

                $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $scenario]);
                $client = new IDCheckClient('test_key', httpClient: $mockHttpClient);
                $response = $client->verify('international_session');

                expect($response)->toBeInstanceOf(VerifyIdResponse::class);
            }
        });

        it('throws VerisoulConnectionException on connection failure', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(IDCheckClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->verify('session_123'))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(IDCheckClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->verify('invalid_session'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('enroll method (inherited)', function (): void {
        it('creates EnrollAccountResponse object', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'post' => [
                    'session_id' => 'id_enroll_session_123',
                    'account_id' => 'id_enroll_account_456',
                    'enrolled' => true,
                    'enrollment_status' => 'completed',
                    'document_template_id' => 'id_template_789',
                    'document_verified' => true,
                ],
            ]);

            $client = new IDCheckClient('test_api_key', httpClient: $mockHttpClient);
            $userAccount = UserAccount::from(['id' => 'id_enroll_account_456']);

            $response = $client->enroll('id_enroll_session_123', $userAccount);

            expect($response)->toBeInstanceOf(EnrollAccountResponse::class);
        });

        it('passes session ID and account ID correctly', function (): void {
            $sessionId = 'id_enroll_test_session_789';
            $accountId = 'id_enroll_test_account_321';

            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('post')
                ->once()
                ->withArgs(fn($url, $data) => str_contains($url, '/liveness/enroll') &&
                           isset($data['session_id'], $data['account_id'])
                            &&
                           $data['session_id'] === $sessionId &&
                           $data['account_id'] === $accountId)
                ->andReturn([
                    'session_id' => $sessionId,
                    'account_id' => $accountId,
                    'enrolled' => true,
                ]);

            $client = new IDCheckClient('test_key', httpClient: $mockHttpClient);
            $userAccount = UserAccount::from(['id' => $accountId]);
            $response = $client->enroll($sessionId, $userAccount);

            expect($response)->toBeInstanceOf(EnrollAccountResponse::class);
        });

        it('throws VerisoulConnectionException on connection failure', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(IDCheckClient::class, ['httpClient' => $failingClient]);
            $userAccount = UserAccount::from(['id' => 'test_account']);

            expect(fn() => $client->enroll('session_123', $userAccount))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(IDCheckClient::class, ['httpClient' => $failingClient]);
            $userAccount = UserAccount::from(['id' => 'invalid_account']);

            expect(fn() => $client->enroll('invalid_session', $userAccount))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('environment integration', function (): void {
        it('uses sandbox URLs in sandbox environment', function (): void {
            $client = new IDCheckClient('sandbox_key', VerisoulEnvironment::Sandbox);

            expect($client->getBaseUrl())->toBe('https://api.sandbox.verisoul.ai');
        });

        it('uses production URLs in production environment', function (): void {
            $client = new IDCheckClient('prod_key', VerisoulEnvironment::Production);

            expect($client->getBaseUrl())->toBe('https://api.verisoul.ai');
        });

        it('makes requests to correct environment', function (): void {
            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('get')
                ->once()
                ->withArgs(fn($url, $params) => str_contains($url, 'https://api.verisoul.ai'))
                ->andReturn(['session_id' => 'prod_id_session', 'type' => 'id_check']);

            $prodClient = new IDCheckClient(
                'prod_key',
                VerisoulEnvironment::Production,
                httpClient: $mockHttpClient,
            );

            $prodClient->session();
        });
    });

    describe('real-world usage scenarios', function (): void {
        it('handles complete ID verification workflow', function (): void {
            // Start ID check session
            $sessionResponse = [
                'session_id' => 'id_workflow_session',
                'session_url' => 'https://liveness.verisoul.ai/id-check/workflow',
                'expires_at' => '2024-01-15T13:00:00Z',
                'type' => 'id_check',
                'id_required' => true,
            ];

            // Verify ID document
            $verifyResponse = [
                'session_id' => 'id_workflow_session',
                'id_verified' => true,
                'document_type' => 'drivers_license',
                'document_country' => 'US',
                'document_state' => 'NY',
                'authenticity_score' => 0.95,
                'extracted_data' => [
                    'name' => 'John Smith',
                    'date_of_birth' => '1985-05-15',
                    'document_number' => 'DL987654321',
                    'expiration_date' => '2028-05-15',
                ],
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => $sessionResponse,
                'post' => $verifyResponse,
            ]);

            $client = new IDCheckClient('test_key', httpClient: $mockHttpClient);

            $sessionResult = $client->session('referring_session_456');
            $verifyResult = $client->verify('id_workflow_session');

            expect($sessionResult)->toBeInstanceOf(LivenessSessionResponse::class)
                ->and($verifyResult)->toBeInstanceOf(VerifyIdResponse::class);
        });

        it('handles enrollment with ID verification workflow', function (): void {
            // Enroll account with ID verification
            $enrollResponse = [
                'session_id' => 'id_enroll_workflow_session',
                'account_id' => 'id_workflow_account_123',
                'enrolled' => true,
                'enrollment_status' => 'completed',
                'document_template_id' => 'id_template_workflow_456',
                'document_verified' => true,
                'document_data' => [
                    'document_type' => 'passport',
                    'document_number' => 'P123456789',
                    'issuing_country' => 'US',
                ],
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $enrollResponse]);
            $client = new IDCheckClient('test_key', httpClient: $mockHttpClient);
            $userAccount = UserAccount::from(['id' => 'id_workflow_account_123']);

            $enrollResult = $client->enroll('id_enroll_workflow_session', $userAccount);

            expect($enrollResult)->toBeInstanceOf(EnrollAccountResponse::class);
        });

        it('handles high-security ID verification scenario', function (): void {
            // High-security session with additional requirements
            $sessionResponse = [
                'session_id' => 'high_security_id_session',
                'referring_session_id' => 'secure_referring_session',
                'session_url' => 'https://liveness.verisoul.ai/id-check/high-security',
                'security_level' => 'high',
                'id_required' => true,
                'additional_checks' => ['hologram_verification', 'barcode_scan'],
                'expires_at' => '2024-01-15T12:15:00Z',
            ];

            // High-confidence ID verification
            $verifyResponse = [
                'session_id' => 'high_security_id_session',
                'id_verified' => true,
                'document_type' => 'passport',
                'document_country' => 'US',
                'authenticity_score' => 0.99,
                'security_features_verified' => [
                    'hologram' => true,
                    'barcode' => true,
                    'rfid_chip' => true,
                    'watermark' => true,
                ],
                'confidence' => 'very_high',
                'risk_indicators' => [],
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => $sessionResponse,
                'post' => $verifyResponse,
            ]);

            $client = new IDCheckClient('test_key', httpClient: $mockHttpClient);

            $sessionResult = $client->session('secure_referring_session');
            $verifyResult = $client->verify('high_security_id_session');

            expect($sessionResult)->toBeInstanceOf(LivenessSessionResponse::class)
                ->and($verifyResult)->toBeInstanceOf(VerifyIdResponse::class);
        });

        it('handles fraudulent document detection scenario', function (): void {
            // Verify document with fraud indicators
            $fraudResponse = [
                'session_id' => 'fraud_detection_session',
                'id_verified' => false,
                'document_type' => 'drivers_license',
                'document_country' => 'US',
                'authenticity_score' => 0.25,
                'verification_status' => 'rejected',
                'fraud_indicators' => [
                    'tampered_text',
                    'invalid_security_features',
                    'inconsistent_fonts',
                    'photo_manipulation',
                ],
                'confidence' => 'very_low',
                'recommendation' => 'manual_review_required',
                'extracted_data' => null,
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $fraudResponse]);
            $client = new IDCheckClient('test_key', httpClient: $mockHttpClient);

            $verifyResult = $client->verify('fraud_detection_session');

            expect($verifyResult)->toBeInstanceOf(VerifyIdResponse::class);
        });

        it('handles international document verification workflow', function (): void {
            // International passport verification
            $internationalResponse = [
                'session_id' => 'international_session',
                'id_verified' => true,
                'document_type' => 'passport',
                'document_country' => 'GB',
                'authenticity_score' => 0.93,
                'extracted_data' => [
                    'name' => 'Emma Watson',
                    'nationality' => 'British',
                    'date_of_birth' => '1990-04-15',
                    'passport_number' => 'GB123456789',
                    'issuing_authority' => 'HM Passport Office',
                    'issue_date' => '2019-06-01',
                    'expiration_date' => '2029-06-01',
                ],
                'mrz_verified' => true,
                'security_features' => [
                    'biometric_chip' => true,
                    'holographic_elements' => true,
                    'watermarks' => true,
                ],
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $internationalResponse]);
            $client = new IDCheckClient('test_key', httpClient: $mockHttpClient);

            $verifyResult = $client->verify('international_session');

            expect($verifyResult)->toBeInstanceOf(VerifyIdResponse::class);
        });

        it('handles document quality issues scenario', function (): void {
            // Poor quality document verification
            $qualityIssuesResponse = [
                'session_id' => 'quality_issues_session',
                'id_verified' => false,
                'document_type' => 'drivers_license',
                'document_country' => 'US',
                'authenticity_score' => 0.65,
                'verification_status' => 'requires_retry',
                'quality_issues' => [
                    'image_blurry',
                    'poor_lighting',
                    'partial_document_visible',
                    'glare_detected',
                ],
                'recommendation' => 'retake_photo',
                'extracted_data' => [
                    'name' => 'John [UNCLEAR]',
                    'date_of_birth' => null,
                    'document_number' => 'DL[PARTIALLY_VISIBLE]',
                ],
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $qualityIssuesResponse]);
            $client = new IDCheckClient('test_key', httpClient: $mockHttpClient);

            $verifyResult = $client->verify('quality_issues_session');

            expect($verifyResult)->toBeInstanceOf(VerifyIdResponse::class);
        });
    });

    describe('document type scenarios', function (): void {
        it('handles US state driver licenses', function (): void {
            $stateScenarios = [
                ['state' => 'CA', 'format' => 'Real ID'],
                ['state' => 'NY', 'format' => 'Enhanced'],
                ['state' => 'TX', 'format' => 'Standard'],
                ['state' => 'FL', 'format' => 'Real ID'],
            ];

            foreach ($stateScenarios as $scenario) {
                $response = [
                    'session_id' => 'state_test_session',
                    'id_verified' => true,
                    'document_type' => 'drivers_license',
                    'document_country' => 'US',
                    'document_state' => $scenario['state'],
                    'document_format' => $scenario['format'],
                    'authenticity_score' => 0.94,
                ];

                $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $response]);
                $client = new IDCheckClient('test_key', httpClient: $mockHttpClient);
                $result = $client->verify('state_test_session');

                expect($result)->toBeInstanceOf(VerifyIdResponse::class);
            }
        });

        it('handles various international document types', function (): void {
            $internationalDocs = [
                [
                    'document_type' => 'national_id',
                    'document_country' => 'DE',
                    'features' => ['rfid_chip', 'hologram'],
                ],
                [
                    'document_type' => 'residence_permit',
                    'document_country' => 'FR',
                    'features' => ['biometric_data', 'security_thread'],
                ],
                [
                    'document_type' => 'voter_id',
                    'document_country' => 'IN',
                    'features' => ['barcode', 'photograph'],
                ],
            ];

            foreach ($internationalDocs as $doc) {
                $response = array_merge([
                    'session_id' => 'international_doc_session',
                    'id_verified' => true,
                    'authenticity_score' => 0.89,
                ], $doc);

                $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $response]);
                $client = new IDCheckClient('test_key', httpClient: $mockHttpClient);
                $result = $client->verify('international_doc_session');

                expect($result)->toBeInstanceOf(VerifyIdResponse::class);
            }
        });
    });
});
