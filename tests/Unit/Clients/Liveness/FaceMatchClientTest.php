<?php

use Ninja\Verisoul\Clients\Liveness\FaceMatchClient;
use Ninja\Verisoul\Enums\VerisoulEnvironment;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Ninja\Verisoul\Responses\LivenessSessionResponse;
use Ninja\Verisoul\Responses\VerifyFaceResponse;
use Ninja\Verisoul\Responses\VerifyIdentityResponse;
use Ninja\Verisoul\Responses\EnrollAccountResponse;
use Ninja\Verisoul\DTO\UserAccount;
use Ninja\Verisoul\Tests\Helpers\MockFactory;
use Ninja\Verisoul\Contracts\HttpClientInterface;
use Illuminate\Contracts\Auth\Authenticatable;

describe('FaceMatchClient', function () {
    describe('construction', function () {
        it('can be created with default parameters', function () {
            $client = new FaceMatchClient('test_api_key');
            
            expect($client)->toBeInstanceOf(FaceMatchClient::class)
                ->and($client->getEnvironment())->toBe(VerisoulEnvironment::Sandbox);
        });

        it('can be created with custom environment', function () {
            $client = new FaceMatchClient('prod_key', VerisoulEnvironment::Production);
            
            expect($client->getEnvironment())->toBe(VerisoulEnvironment::Production);
        });

        it('inherits from LivenessApiClient', function () {
            $client = new FaceMatchClient('test_api_key');
            
            expect($client)->toBeInstanceOf(\Ninja\Verisoul\Clients\Liveness\LivenessApiClient::class);
        });

        it('implements FaceMatchInterface', function () {
            $client = new FaceMatchClient('test_api_key');
            
            expect($client)->toBeInstanceOf(\Ninja\Verisoul\Contracts\FaceMatchInterface::class);
        });

        it('implements BiometricInterface through inheritance', function () {
            $client = new FaceMatchClient('test_api_key');
            
            expect($client)->toBeInstanceOf(\Ninja\Verisoul\Contracts\BiometricInterface::class);
        });
    });

    describe('session method', function () {
        it('creates LivenessSessionResponse object without referring session', function () {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => [
                    'session_id' => 'face_session_123',
                    'session_url' => 'https://liveness.verisoul.ai/face-match/session_123',
                    'expires_at' => '2024-01-15T12:30:00Z',
                    'type' => 'face_match'
                ]
            ]);

            $client = new FaceMatchClient('test_api_key', httpClient: $mockHttpClient);
            
            $response = $client->session();

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);
        });

        it('creates LivenessSessionResponse object with referring session', function () {
            $referringSessionId = 'referring_session_456';

            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();
            
            $mockHttpClient->shouldReceive('get')
                ->once()
                ->withArgs(function ($url, $params) use ($referringSessionId) {
                    return str_contains($url, '/liveness/session') &&
                           str_contains($url, "referring_session_id={$referringSessionId}");
                })
                ->andReturn([
                    'session_id' => 'face_session_with_ref_789',
                    'referring_session_id' => $referringSessionId,
                    'session_url' => 'https://liveness.verisoul.ai/face-match/session_789',
                    'type' => 'face_match'
                ]);

            $client = new FaceMatchClient('test_key', httpClient: $mockHttpClient);
            $response = $client->session($referringSessionId);

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);
        });

        it('handles null referring session ID', function () {
            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();
            
            $mockHttpClient->shouldReceive('get')
                ->once()
                ->withArgs(function ($url, $params) {
                    return str_contains($url, '/liveness/session') &&
                           !str_contains($url, 'referring_session_id');
                })
                ->andReturn([
                    'session_id' => 'standalone_face_session',
                    'session_url' => 'https://liveness.verisoul.ai/face-match/standalone',
                    'type' => 'face_match'
                ]);

            $client = new FaceMatchClient('test_key', httpClient: $mockHttpClient);
            $response = $client->session(null);

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);
        });

        it('throws VerisoulConnectionException on connection failure', function () {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(FaceMatchClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->session())
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function () {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(FaceMatchClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->session('invalid_session'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('verify method', function () {
        it('creates VerifyFaceResponse object', function () {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'post' => [
                    'session_id' => 'verify_face_session_123',
                    'is_live' => true,
                    'face_match_score' => 0.95,
                    'liveness_score' => 0.92,
                    'verification_status' => 'verified',
                    'confidence' => 'high'
                ]
            ]);

            $client = new FaceMatchClient('test_api_key', httpClient: $mockHttpClient);
            
            $response = $client->verify('verify_face_session_123');

            expect($response)->toBeInstanceOf(VerifyFaceResponse::class);
        });

        it('passes session ID correctly in request data', function () {
            $sessionId = 'face_verify_session_456';

            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();
            
            $mockHttpClient->shouldReceive('post')
                ->once()
                ->withArgs(function ($url, $data) use ($sessionId) {
                    return str_contains($url, '/liveness/verify-face') &&
                           isset($data['session_id']) &&
                           $data['session_id'] === $sessionId;
                })
                ->andReturn([
                    'session_id' => $sessionId,
                    'is_live' => true,
                    'face_match_score' => 0.88
                ]);

            $client = new FaceMatchClient('test_key', httpClient: $mockHttpClient);
            $response = $client->verify($sessionId);

            expect($response)->toBeInstanceOf(VerifyFaceResponse::class);
        });

        it('handles various verification outcomes', function () {
            $verificationScenarios = [
                [
                    'session_id' => 'high_match_session',
                    'is_live' => true,
                    'face_match_score' => 0.98,
                    'liveness_score' => 0.96,
                    'verification_status' => 'verified',
                    'outcome' => 'approved'
                ],
                [
                    'session_id' => 'low_match_session',
                    'is_live' => true,
                    'face_match_score' => 0.45,
                    'liveness_score' => 0.89,
                    'verification_status' => 'rejected',
                    'outcome' => 'face_mismatch'
                ],
                [
                    'session_id' => 'spoof_session',
                    'is_live' => false,
                    'face_match_score' => 0.82,
                    'liveness_score' => 0.15,
                    'verification_status' => 'rejected',
                    'outcome' => 'liveness_failed'
                ]
            ];

            foreach ($verificationScenarios as $scenario) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $scenario]);
                $client = new FaceMatchClient('test_key', httpClient: $mockHttpClient);
                $response = $client->verify($scenario['session_id']);
                
                expect($response)->toBeInstanceOf(VerifyFaceResponse::class);
            }
        });

        it('throws VerisoulConnectionException on connection failure', function () {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(FaceMatchClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->verify('session_123'))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function () {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(FaceMatchClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->verify('invalid_session'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('verifyIdentity method', function () {
        it('creates VerifyIdentityResponse object', function () {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'post' => [
                    'session_id' => 'identity_session_123',
                    'account_id' => 'user_identity_123',
                    'identity_verified' => true,
                    'face_match_score' => 0.93,
                    'identity_confidence' => 0.97,
                    'verification_status' => 'verified'
                ]
            ]);

            $client = new FaceMatchClient('test_api_key', httpClient: $mockHttpClient);
            
            $response = $client->verifyIdentity('identity_session_123', 'user_identity_123');

            expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);
        });

        it('passes session ID and user identifier correctly', function () {
            $sessionId = 'identity_verify_session_456';
            $userId = 'user_identity_123'; // Use the same ID as configured in beforeEach

            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();
            
            $mockHttpClient->shouldReceive('post')
                ->once()
                ->withArgs(function ($url, $data) use ($sessionId, $userId) {
                    return str_contains($url, '/liveness/verify-identity') &&
                           isset($data['session_id']) &&
                           isset($data['account_id']) &&
                           $data['session_id'] === $sessionId &&
                           $data['account_id'] === $userId;
                })
                ->andReturn([
                    'session_id' => $sessionId,
                    'account_id' => $userId,
                    'identity_verified' => true
                ]);

            $client = new FaceMatchClient('test_key', httpClient: $mockHttpClient);
            $response = $client->verifyIdentity($sessionId, $userId);

            expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);
        });

        it('handles different user identifier types', function () {
            $userScenarios = [
                ['id' => 'uuid_12345678-1234-1234-1234-123456789012', 'type' => 'UUID'],
                ['id' => 'email_user@example.com', 'type' => 'Email'],
                ['id' => 'numeric_123456', 'type' => 'Numeric'],
                ['id' => 'custom_user_identifier_789', 'type' => 'Custom']
            ];

            foreach ($userScenarios as $scenario) {
                $mockUser = Mockery::mock(Authenticatable::class);
                $mockUser->shouldReceive('getAuthIdentifier')->andReturn($scenario['id']);

                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'post' => [
                        'session_id' => 'test_session',
                        'account_id' => $scenario['id'],
                        'identity_verified' => true,
                        'user_type' => $scenario['type']
                    ]
                ]);
                
                $client = new FaceMatchClient('test_key', httpClient: $mockHttpClient);
                $response = $client->verifyIdentity('test_session', 'user_identity_123');
                
                expect($response)->toBeInstanceOf(VerifyIdentityResponse::class);
            }
        });

        it('throws VerisoulConnectionException on connection failure', function () {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(FaceMatchClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->verifyIdentity('session_123', 'user_identity_123'))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function () {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(FaceMatchClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->verifyIdentity('invalid_session', 'user_identity_123'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('enroll method (inherited)', function () {
        it('creates EnrollAccountResponse object', function () {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'post' => [
                    'session_id' => 'enroll_session_123',
                    'account_id' => 'enroll_account_456',
                    'enrolled' => true,
                    'enrollment_status' => 'completed',
                    'biometric_template_id' => 'template_789'
                ]
            ]);

            $client = new FaceMatchClient('test_api_key', httpClient: $mockHttpClient);
            $userAccount = UserAccount::from(['id' => 'enroll_account_456']);
            
            $response = $client->enroll('enroll_session_123', $userAccount);

            expect($response)->toBeInstanceOf(EnrollAccountResponse::class);
        });

        it('passes session ID and account ID correctly', function () {
            $sessionId = 'enroll_test_session_789';
            $accountId = 'enroll_test_account_321';

            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();
            
            $mockHttpClient->shouldReceive('post')
                ->once()
                ->withArgs(function ($url, $data) use ($sessionId, $accountId) {
                    return str_contains($url, '/liveness/enroll') &&
                           isset($data['session_id']) &&
                           isset($data['account_id']) &&
                           $data['session_id'] === $sessionId &&
                           $data['account_id'] === $accountId;
                })
                ->andReturn([
                    'session_id' => $sessionId,
                    'account_id' => $accountId,
                    'enrolled' => true
                ]);

            $client = new FaceMatchClient('test_key', httpClient: $mockHttpClient);
            $userAccount = UserAccount::from(['id' => $accountId]);
            $response = $client->enroll($sessionId, $userAccount);

            expect($response)->toBeInstanceOf(EnrollAccountResponse::class);
        });

        it('throws VerisoulConnectionException on connection failure', function () {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(FaceMatchClient::class, ['httpClient' => $failingClient]);
            $userAccount = UserAccount::from(['id' => 'test_account']);

            expect(fn() => $client->enroll('session_123', $userAccount))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function () {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(FaceMatchClient::class, ['httpClient' => $failingClient]);
            $userAccount = UserAccount::from(['id' => 'invalid_account']);

            expect(fn() => $client->enroll('invalid_session', $userAccount))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('environment integration', function () {
        it('uses sandbox URLs in sandbox environment', function () {
            $client = new FaceMatchClient('sandbox_key', VerisoulEnvironment::Sandbox);
            
            expect($client->getBaseUrl())->toBe('https://api.sandbox.verisoul.ai');
        });

        it('uses production URLs in production environment', function () {
            $client = new FaceMatchClient('prod_key', VerisoulEnvironment::Production);
            
            expect($client->getBaseUrl())->toBe('https://api.verisoul.ai');
        });

        it('makes requests to correct environment', function () {
            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();
            
            $mockHttpClient->shouldReceive('get')
                ->once()
                ->withArgs(function ($url, $params) {
                    return str_contains($url, 'https://api.verisoul.ai');
                })
                ->andReturn(['session_id' => 'prod_session', 'type' => 'face_match']);

            $prodClient = new FaceMatchClient(
                'prod_key', 
                VerisoulEnvironment::Production, 
                httpClient: $mockHttpClient
            );
            
            $prodClient->session();
        });
    });

    describe('real-world usage scenarios', function () {
        it('handles complete face verification workflow', function () {
            // Start session
            $sessionResponse = [
                'session_id' => 'workflow_face_session',
                'session_url' => 'https://liveness.verisoul.ai/face-match/workflow',
                'expires_at' => '2024-01-15T13:00:00Z',
                'type' => 'face_match'
            ];

            // Verify face
            $verifyResponse = [
                'session_id' => 'workflow_face_session',
                'is_live' => true,
                'face_match_score' => 0.94,
                'liveness_score' => 0.91,
                'verification_status' => 'verified'
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => $sessionResponse,
                'post' => $verifyResponse
            ]);
            
            $client = new FaceMatchClient('test_key', httpClient: $mockHttpClient);

            $sessionResult = $client->session('referring_session_456');
            $verifyResult = $client->verify('workflow_face_session');

            expect($sessionResult)->toBeInstanceOf(LivenessSessionResponse::class)
                ->and($verifyResult)->toBeInstanceOf(VerifyFaceResponse::class);
        });

        it('handles enrollment and identity verification workflow', function () {
            // Enroll account
            $enrollResponse = [
                'session_id' => 'enroll_workflow_session',
                'account_id' => 'workflow_account_123',
                'enrolled' => true,
                'biometric_template_id' => 'template_workflow_456'
            ];

            // Verify identity
            $mockUser = Mockery::mock(Authenticatable::class);
            $mockUser->shouldReceive('getAuthIdentifier')->andReturn('workflow_account_123');

            $identityResponse = [
                'session_id' => 'identity_workflow_session',
                'account_id' => 'workflow_account_123',
                'identity_verified' => true,
                'face_match_score' => 0.96,
                'identity_confidence' => 0.98
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'post' => $enrollResponse
            ]);
            
            $client = new FaceMatchClient('test_key', httpClient: $mockHttpClient);
            $userAccount = UserAccount::from(['id' => 'workflow_account_123']);

            $enrollResult = $client->enroll('enroll_workflow_session', $userAccount);

            expect($enrollResult)->toBeInstanceOf(EnrollAccountResponse::class);
        });

        it('handles high-security verification scenario', function () {
            // High-security session with referring session
            $sessionResponse = [
                'session_id' => 'high_security_session',
                'referring_session_id' => 'secure_referring_session',
                'session_url' => 'https://liveness.verisoul.ai/face-match/high-security',
                'security_level' => 'high',
                'expires_at' => '2024-01-15T12:15:00Z'
            ];

            // High-confidence verification
            $verifyResponse = [
                'session_id' => 'high_security_session',
                'is_live' => true,
                'face_match_score' => 0.99,
                'liveness_score' => 0.98,
                'verification_status' => 'verified',
                'confidence' => 'very_high',
                'risk_indicators' => []
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => $sessionResponse,
                'post' => $verifyResponse
            ]);
            
            $client = new FaceMatchClient('test_key', httpClient: $mockHttpClient);

            $sessionResult = $client->session('secure_referring_session');
            $verifyResult = $client->verify('high_security_session');

            expect($sessionResult)->toBeInstanceOf(LivenessSessionResponse::class)
                ->and($verifyResult)->toBeInstanceOf(VerifyFaceResponse::class);
        });

        it('handles suspicious activity detection scenario', function () {
            // Verify face with suspicious indicators
            $suspiciousResponse = [
                'session_id' => 'suspicious_session',
                'is_live' => false,
                'face_match_score' => 0.35,
                'liveness_score' => 0.12,
                'verification_status' => 'rejected',
                'risk_indicators' => [
                    'potential_deepfake',
                    'low_liveness_score',
                    'face_mismatch'
                ],
                'confidence' => 'low',
                'recommendation' => 'manual_review'
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $suspiciousResponse]);
            $client = new FaceMatchClient('test_key', httpClient: $mockHttpClient);

            $verifyResult = $client->verify('suspicious_session');

            expect($verifyResult)->toBeInstanceOf(VerifyFaceResponse::class);
        });
    });
});