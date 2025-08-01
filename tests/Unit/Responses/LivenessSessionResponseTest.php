<?php

use Ninja\Verisoul\Enums\VerisoulEnvironment;
use Ninja\Verisoul\Responses\LivenessSessionResponse;

describe('LivenessSessionResponse', function (): void {
    describe('construction and basic functionality', function (): void {
        it('can be created with basic data', function (): void {
            $data = [
                'request_id' => 'req_' . bin2hex(random_bytes(12)),
                'session_id' => 'liveness_sess_' . bin2hex(random_bytes(12)),
            ];
            $response = LivenessSessionResponse::from($data);

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);
        });

        it('can be created with custom session data', function (): void {
            $data = [
                'request_id' => 'req_custom_test',
                'session_id' => 'liveness_custom_session_123',
            ];
            $response = LivenessSessionResponse::from($data);

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);
        });

        it('provides access to response identifiers', function (): void {
            $data = [
                'request_id' => 'test_request_456',
                'session_id' => 'test_session_789',
            ];
            $response = LivenessSessionResponse::from($data);

            $responseArray = $response->array();
            expect($responseArray)->toHaveKey('request_id')
                ->and($responseArray)->toHaveKey('session_id')
                ->and($responseArray['request_id'])->toBe('test_request_456')
                ->and($responseArray['session_id'])->toBe('test_session_789');
        });
    });

    describe('session ID handling', function (): void {
        it('handles various session ID formats', function (): void {
            $sessionIdFormats = [
                'liveness_sess_12345678901234567890',
                'session_abc123',
                '550e8400-e29b-41d4-a716-446655440000',
                'simple_session',
                'liveness-session-with-dashes',
                'LivenessSessionCamelCase',
                'liveness_session_with_underscores_123',
                'UPPERCASE_LIVENESS_SESSION',
                'mixedCASE_Liveness_Session_456',
            ];

            foreach ($sessionIdFormats as $sessionId) {
                $data = [
                    'request_id' => 'req_test',
                    'session_id' => $sessionId,
                ];
                $response = LivenessSessionResponse::from($data);

                expect($response)->toBeInstanceOf(LivenessSessionResponse::class);

                $responseArray = $response->array();
                expect($responseArray['session_id'])->toBe($sessionId);
            }
        });

        it('handles UUID-formatted session IDs', function (): void {
            $uuidSessions = [
                '550e8400-e29b-41d4-a716-446655440000',
                'f47ac10b-58cc-4372-a567-0e02b2c3d479',
                '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
                '123e4567-e89b-12d3-a456-426614174000',
            ];

            foreach ($uuidSessions as $sessionId) {
                $data = [
                    'request_id' => 'req_uuid_test',
                    'session_id' => $sessionId,
                ];
                $response = LivenessSessionResponse::from($data);

                expect($response)->toBeInstanceOf(LivenessSessionResponse::class);

                $responseArray = $response->array();
                expect($responseArray['session_id'])->toBe($sessionId);
            }
        });

        it('handles very long session IDs', function (): void {
            $longSessionId = str_repeat('liveness_session_', 20) . 'end';
            $data = [
                'request_id' => 'req_long_session',
                'session_id' => $longSessionId,
            ];
            $response = LivenessSessionResponse::from($data);

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);

            $responseArray = $response->array();
            expect($responseArray['session_id'])->toBe($longSessionId);
        });

        it('handles empty session ID', function (): void {
            $data = [
                'request_id' => 'req_empty_session',
                'session_id' => '',
            ];
            $response = LivenessSessionResponse::from($data);

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);
        });

        it('handles sessions with special characters', function (): void {
            $specialCharSessions = [
                'session@domain.com',
                'session+tag',
                'session.with.dots',
                'session#with#hashes',
                'session%20encoded',
                'session&with&ampersands',
            ];

            foreach ($specialCharSessions as $sessionId) {
                $data = [
                    'request_id' => 'req_special_test',
                    'session_id' => $sessionId,
                ];
                $response = LivenessSessionResponse::from($data);

                expect($response)->toBeInstanceOf(LivenessSessionResponse::class);
            }
        });
    });

    describe('request ID handling', function (): void {
        it('handles various request ID formats', function (): void {
            $requestIds = [
                'req_12345678901234567890',
                'request_abc123',
                'liveness_req_test_789',
                '550e8400-e29b-41d4-a716-446655440000',
                'simple_request_id',
            ];

            foreach ($requestIds as $requestId) {
                $data = [
                    'request_id' => $requestId,
                    'session_id' => 'test_session',
                ];
                $response = LivenessSessionResponse::from($data);

                expect($response)->toBeInstanceOf(LivenessSessionResponse::class);

                $responseArray = $response->array();
                expect($responseArray['request_id'])->toBe($requestId);
            }
        });

        it('handles empty request ID', function (): void {
            $data = [
                'request_id' => '',
                'session_id' => 'test_session',
            ];
            $response = LivenessSessionResponse::from($data);

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);
        });

        it('handles very long request ID', function (): void {
            $longRequestId = str_repeat('req_', 100) . 'end';
            $data = [
                'request_id' => $longRequestId,
                'session_id' => 'test_session',
            ];
            $response = LivenessSessionResponse::from($data);

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);

            $responseArray = $response->array();
            expect($responseArray['request_id'])->toBe($longRequestId);
        });
    });

    describe('redirect URL functionality', function (): void {
        it('generates correct redirect URL for sandbox environment', function (): void {
            $sessionId = 'test_session_123';
            $data = [
                'request_id' => 'req_redirect_test',
                'session_id' => $sessionId,
            ];
            $response = LivenessSessionResponse::from($data);

            $redirectUrl = $response->redirectUrl(VerisoulEnvironment::Sandbox);

            expect($redirectUrl)->toBe("https://app.sandbox.verisoul.ai/?session_id={$sessionId}");
        });

        it('generates correct redirect URL for production environment', function (): void {
            $sessionId = 'test_session_456';
            $data = [
                'request_id' => 'req_redirect_prod_test',
                'session_id' => $sessionId,
            ];
            $response = LivenessSessionResponse::from($data);

            $redirectUrl = $response->redirectUrl(VerisoulEnvironment::Production);

            expect($redirectUrl)->toBe("https://app.production.verisoul.ai/?session_id={$sessionId}");
        });

        it('generates correct redirect URL with custom redirect parameter', function (): void {
            $sessionId = 'test_session_789';
            $customRedirect = 'https://myapp.com/success';
            $data = [
                'request_id' => 'req_custom_redirect_test',
                'session_id' => $sessionId,
            ];
            $response = LivenessSessionResponse::from($data);

            $redirectUrl = $response->redirectUrl(VerisoulEnvironment::Sandbox, $customRedirect);
            $expectedUrl = "https://app.sandbox.verisoul.ai/?session_id={$sessionId}&redirect_url=" . urlencode($customRedirect);

            expect($redirectUrl)->toBe($expectedUrl);
        });

        it('uses sandbox environment as default', function (): void {
            $sessionId = 'test_session_default';
            $data = [
                'request_id' => 'req_default_env_test',
                'session_id' => $sessionId,
            ];
            $response = LivenessSessionResponse::from($data);

            $redirectUrl = $response->redirectUrl();

            expect($redirectUrl)->toBe("https://app.sandbox.verisoul.ai/?session_id={$sessionId}");
        });

        it('handles complex redirect URLs', function (): void {
            $sessionId = 'test_session_complex';
            $complexRedirect = 'https://myapp.com/success?user=123&token=abc&next=/dashboard';
            $data = [
                'request_id' => 'req_complex_redirect_test',
                'session_id' => $sessionId,
            ];
            $response = LivenessSessionResponse::from($data);

            $redirectUrl = $response->redirectUrl(VerisoulEnvironment::Sandbox, $complexRedirect);
            $expectedUrl = "https://app.sandbox.verisoul.ai/?session_id={$sessionId}&redirect_url=" . urlencode($complexRedirect);

            expect($redirectUrl)->toBe($expectedUrl);
        });

        it('handles redirect URLs with special characters', function (): void {
            $sessionId = 'test_session_special';
            $specialRedirect = 'https://myapp.com/success?message=Hello World!&status=✅';
            $data = [
                'request_id' => 'req_special_redirect_test',
                'session_id' => $sessionId,
            ];
            $response = LivenessSessionResponse::from($data);

            $redirectUrl = $response->redirectUrl(VerisoulEnvironment::Sandbox, $specialRedirect);
            $expectedUrl = "https://app.sandbox.verisoul.ai/?session_id={$sessionId}&redirect_url=" . urlencode($specialRedirect);

            expect($redirectUrl)->toBe($expectedUrl);
        });

        it('handles empty redirect URL', function (): void {
            $sessionId = 'test_session_empty_redirect';
            $data = [
                'request_id' => 'req_empty_redirect_test',
                'session_id' => $sessionId,
            ];
            $response = LivenessSessionResponse::from($data);

            $redirectUrl = $response->redirectUrl(VerisoulEnvironment::Sandbox, '');

            expect($redirectUrl)->toBe("https://app.sandbox.verisoul.ai/?session_id={$sessionId}");
        });

        it('handles null redirect URL', function (): void {
            $sessionId = 'test_session_null_redirect';
            $data = [
                'request_id' => 'req_null_redirect_test',
                'session_id' => $sessionId,
            ];
            $response = LivenessSessionResponse::from($data);

            $redirectUrl = $response->redirectUrl(VerisoulEnvironment::Sandbox, null);

            expect($redirectUrl)->toBe("https://app.sandbox.verisoul.ai/?session_id={$sessionId}");
        });
    });

    describe('data integrity and serialization', function (): void {
        it('maintains data integrity through serialization', function (): void {
            $originalData = [
                'request_id' => 'integrity_test_request',
                'session_id' => 'integrity_test_session',
            ];
            $response = LivenessSessionResponse::from($originalData);
            $serializedData = $response->array();
            $recreatedResponse = LivenessSessionResponse::from($serializedData);

            expect($recreatedResponse)->toBeInstanceOf(LivenessSessionResponse::class);

            $originalArray = $response->array();
            $recreatedArray = $recreatedResponse->array();

            expect($recreatedArray['request_id'])->toBe($originalArray['request_id'])
                ->and($recreatedArray['session_id'])->toBe($originalArray['session_id']);
        });

        it('serializes to correct structure', function (): void {
            $data = [
                'request_id' => 'structure_test',
                'session_id' => 'structure_test_session',
            ];
            $response = LivenessSessionResponse::from($data);
            $serialized = $response->array();

            expect($serialized)->toBeArray()
                ->and($serialized)->toHaveCount(2)
                ->and($serialized)->toHaveKey('request_id')
                ->and($serialized)->toHaveKey('session_id')
                ->and($serialized['request_id'])->toBe('structure_test')
                ->and($serialized['session_id'])->toBe('structure_test_session');
        });

        it('handles JSON serialization correctly', function (): void {
            $data = [
                'request_id' => 'json_test',
                'session_id' => 'json_test_session',
            ];
            $response = LivenessSessionResponse::from($data);

            $jsonString = json_encode($response->array());
            $decodedData = json_decode($jsonString, true);
            $recreatedResponse = LivenessSessionResponse::from($decodedData);

            expect($recreatedResponse)->toBeInstanceOf(LivenessSessionResponse::class);
            expect(json_last_error())->toBe(JSON_ERROR_NONE);
        });
    });

    describe('readonly class behavior', function (): void {
        it('creates immutable instances', function (): void {
            $data = [
                'request_id' => 'immutable_test',
                'session_id' => 'immutable_test_session',
            ];
            $response = LivenessSessionResponse::from($data);

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);

            // Since it's a readonly class, we can't modify properties
            // This test verifies the object was created successfully
            $responseArray = $response->array();
            expect($responseArray['request_id'])->toBe('immutable_test');
        });

        it('provides consistent data access', function (): void {
            $data = [
                'request_id' => 'consistent_test',
                'session_id' => 'consistent_test_session',
            ];
            $response = LivenessSessionResponse::from($data);

            $firstAccess = $response->array();
            $secondAccess = $response->array();
            $thirdAccess = $response->array();

            expect($firstAccess)->toBe($secondAccess)
                ->and($secondAccess)->toBe($thirdAccess);
        });

        it('provides consistent redirect URL generation', function (): void {
            $data = [
                'request_id' => 'consistent_redirect_test',
                'session_id' => 'consistent_redirect_session',
            ];
            $response = LivenessSessionResponse::from($data);

            $firstUrl = $response->redirectUrl();
            $secondUrl = $response->redirectUrl();
            $thirdUrl = $response->redirectUrl();

            expect($firstUrl)->toBe($secondUrl)
                ->and($secondUrl)->toBe($thirdUrl);
        });
    });

    describe('real-world liveness session scenarios', function (): void {
        it('handles face match liveness session creation', function (): void {
            $faceMatchData = [
                'request_id' => 'face_match_' . time(),
                'session_id' => 'face_match_sess_' . bin2hex(random_bytes(12)),
            ];
            $response = LivenessSessionResponse::from($faceMatchData);

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);

            $redirectUrl = $response->redirectUrl(VerisoulEnvironment::Sandbox);
            expect($redirectUrl)->toContain('session_id=' . $faceMatchData['session_id']);
        });

        it('handles ID verification liveness session creation', function (): void {
            $idVerificationData = [
                'request_id' => 'id_verification_' . time(),
                'session_id' => 'id_verify_sess_' . bin2hex(random_bytes(12)),
            ];
            $response = LivenessSessionResponse::from($idVerificationData);

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);

            $redirectUrl = $response->redirectUrl(VerisoulEnvironment::Production);
            expect($redirectUrl)->toContain('app.production.verisoul.ai');
        });

        it('handles liveness session with return URL', function (): void {
            $returnUrlData = [
                'request_id' => 'return_url_' . time(),
                'session_id' => 'return_sess_' . bin2hex(random_bytes(12)),
            ];
            $response = LivenessSessionResponse::from($returnUrlData);

            $returnUrl = 'https://myapp.com/verification/complete';
            $redirectUrl = $response->redirectUrl(VerisoulEnvironment::Sandbox, $returnUrl);

            expect($redirectUrl)->toContain('redirect_url=' . urlencode($returnUrl));
        });

        it('handles mobile app liveness session', function (): void {
            $mobileData = [
                'request_id' => 'mobile_app_' . time(),
                'session_id' => 'mobile_sess_' . bin2hex(random_bytes(12)),
            ];
            $response = LivenessSessionResponse::from($mobileData);

            $deepLinkUrl = 'myapp://verification/success';
            $redirectUrl = $response->redirectUrl(VerisoulEnvironment::Sandbox, $deepLinkUrl);

            expect($redirectUrl)->toContain('redirect_url=' . urlencode($deepLinkUrl));
        });

        it('handles web app integration scenario', function (): void {
            $webAppData = [
                'request_id' => 'web_app_' . time(),
                'session_id' => 'web_sess_' . bin2hex(random_bytes(12)),
            ];
            $response = LivenessSessionResponse::from($webAppData);

            $webAppReturn = 'https://webapp.example.com/dashboard?verified=true';
            $redirectUrl = $response->redirectUrl(VerisoulEnvironment::Production, $webAppReturn);

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);
            expect($redirectUrl)->toContain('app.production.verisoul.ai');
        });

        it('handles enterprise integration scenario', function (): void {
            $enterpriseData = [
                'request_id' => 'enterprise_' . time(),
                'session_id' => 'enterprise_sess_' . bin2hex(random_bytes(12)),
            ];
            $response = LivenessSessionResponse::from($enterpriseData);

            $enterpriseReturn = 'https://enterprise.company.com/employee/onboarding/complete?step=liveness&employee_id=12345';
            $redirectUrl = $response->redirectUrl(VerisoulEnvironment::Production, $enterpriseReturn);

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);
            expect($redirectUrl)->toContain('redirect_url=');
        });
    });

    describe('edge cases and error handling', function (): void {
        it('handles session IDs with whitespace', function (): void {
            $whitespaceIds = [
                ' leading_space_session',
                'trailing_space_session ',
                ' both_spaces_session ',
                'internal space session',
                "tab\tsession",
                "newline\nsession",
            ];

            foreach ($whitespaceIds as $sessionId) {
                $data = [
                    'request_id' => 'whitespace_test',
                    'session_id' => $sessionId,
                ];
                $response = LivenessSessionResponse::from($data);

                expect($response)->toBeInstanceOf(LivenessSessionResponse::class);
            }
        });

        it('handles unicode characters in session IDs', function (): void {
            $unicodeIds = [
                'session_ñoño',
                'session_测试',
                'session_العربية',
                'session_🎭',
                'session_café',
            ];

            foreach ($unicodeIds as $sessionId) {
                $data = [
                    'request_id' => 'unicode_test',
                    'session_id' => $sessionId,
                ];
                $response = LivenessSessionResponse::from($data);

                expect($response)->toBeInstanceOf(LivenessSessionResponse::class);
            }
        });

        it('handles extremely long session IDs', function (): void {
            $veryLongSessionId = str_repeat('very_long_session_', 100) . 'end';
            $data = [
                'request_id' => 'very_long_test',
                'session_id' => $veryLongSessionId,
            ];
            $response = LivenessSessionResponse::from($data);

            expect($response)->toBeInstanceOf(LivenessSessionResponse::class);

            $responseArray = $response->array();
            expect($responseArray['session_id'])->toBe($veryLongSessionId);
        });

        it('handles redirect URL generation with special session IDs', function (): void {
            $specialSessionIds = [
                'session with spaces',
                'session+with+plus',
                'session&with&ampersands',
                'session%20encoded',
                'session#with#hashes',
            ];

            foreach ($specialSessionIds as $sessionId) {
                $data = [
                    'request_id' => 'special_session_test',
                    'session_id' => $sessionId,
                ];
                $response = LivenessSessionResponse::from($data);

                $redirectUrl = $response->redirectUrl();
                expect($redirectUrl)->toContain('session_id=');
            }
        });

        it('handles various environment values', function (): void {
            $sessionId = 'env_test_session';
            $data = [
                'request_id' => 'env_test',
                'session_id' => $sessionId,
            ];
            $response = LivenessSessionResponse::from($data);

            $environments = [VerisoulEnvironment::Sandbox, VerisoulEnvironment::Production];

            foreach ($environments as $env) {
                $redirectUrl = $response->redirectUrl($env);
                expect($redirectUrl)->toContain('app.' . $env->value . '.verisoul.ai');
            }
        });
    });

    describe('performance and memory', function (): void {
        it('creates multiple instances efficiently', function (): void {
            $startTime = microtime(true);
            $responses = [];

            for ($i = 1; $i <= 100; $i++) {
                $data = [
                    'request_id' => "performance_test_{$i}",
                    'session_id' => "performance_session_{$i}",
                ];
                $responses[] = LivenessSessionResponse::from($data);
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            expect(count($responses))->toBe(100)
                ->and($executionTime)->toBeLessThan(0.5);

            foreach ($responses as $response) {
                expect($response)->toBeInstanceOf(LivenessSessionResponse::class);
            }
        });

        it('generates redirect URLs efficiently', function (): void {
            $data = [
                'request_id' => 'url_performance_test',
                'session_id' => 'url_performance_session',
            ];
            $response = LivenessSessionResponse::from($data);

            $startTime = microtime(true);

            for ($i = 0; $i < 1000; $i++) {
                $response->redirectUrl(VerisoulEnvironment::Sandbox, 'https://example.com/return');
            }

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            expect($executionTime)->toBeLessThan(0.1); // Should be very fast
        });

        it('maintains reasonable memory usage', function (): void {
            $initialMemory = memory_get_usage();

            $responses = [];
            for ($i = 1; $i <= 1000; $i++) {
                $data = [
                    'request_id' => "memory_test_{$i}",
                    'session_id' => "memory_session_{$i}",
                ];
                $responses[] = LivenessSessionResponse::from($data);
            }

            $finalMemory = memory_get_usage();
            $memoryUsed = $finalMemory - $initialMemory;

            expect(count($responses))->toBe(1000)
                ->and($memoryUsed)->toBeLessThan(1024 * 1024); // Less than 1MB

            unset($responses);
        });
    });
});
