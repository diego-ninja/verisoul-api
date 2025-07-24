<?php

namespace Ninja\Verisoul\Tests\Helpers;

use Mockery;
use Ninja\Verisoul\Contracts\HttpClientInterface;
use Ninja\Verisoul\Enums\VerisoulEnvironment;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Ninja\Verisoul\Support\RetryStrategy;
use Psr\SimpleCache\CacheInterface;

class MockFactory
{
    /**
     * Create a mock HTTP client that returns successful responses
     */
    public static function createSuccessfulHttpClient(array $responses = []): HttpClientInterface
    {
        $mock = Mockery::mock(HttpClientInterface::class);
        
        // Setup common method expectations
        $mock->shouldReceive('setTimeout')->andReturnSelf();
        $mock->shouldReceive('setConnectTimeout')->andReturnSelf();
        $mock->shouldReceive('setHeaders')->andReturnSelf();
        
        // Default successful responses - Verisoul API returns direct data, not wrapped
        $defaultResponse = [];
        
        foreach (['get', 'post', 'put', 'delete'] as $method) {
            $response = $responses[$method] ?? $defaultResponse;
            $mock->shouldReceive($method)->andReturn($response);
        }
        
        return $mock;
    }

    /**
     * Create a mock HTTP client that throws connection exceptions
     */
    public static function createFailingHttpClient(string $exceptionClass = VerisoulConnectionException::class): HttpClientInterface
    {
        $mock = Mockery::mock(HttpClientInterface::class);
        
        $mock->shouldReceive('setTimeout')->andReturnSelf();
        $mock->shouldReceive('setConnectTimeout')->andReturnSelf();
        $mock->shouldReceive('setHeaders')->andReturnSelf();
        
        foreach (['get', 'post', 'put', 'delete'] as $method) {
            // Create proper exception based on class
            if ($exceptionClass === VerisoulConnectionException::class) {
                $exception = VerisoulConnectionException::networkError('/test', 'Connection failed');
            } elseif ($exceptionClass === VerisoulApiException::class) {
                $exception = new VerisoulApiException('API error', 500, [], '/test');
            } else {
                $exception = new $exceptionClass('Connection failed');
            }
            
            $mock->shouldReceive($method)->andThrow($exception);
        }
        
        return $mock;
    }

    /**
     * Create a mock HTTP client that returns API error responses
     */
    public static function createApiErrorHttpClient(int $errorCode = 400, string $errorMessage = 'Bad Request'): HttpClientInterface
    {
        $mock = Mockery::mock(HttpClientInterface::class);
        
        $mock->shouldReceive('setTimeout')->andReturnSelf();
        $mock->shouldReceive('setConnectTimeout')->andReturnSelf();
        $mock->shouldReceive('setHeaders')->andReturnSelf();
        
        // Verisoul API error responses are also direct, not wrapped
        $errorResponse = [
            'error' => [
                'code' => $errorCode,
                'message' => $errorMessage,
            ]
        ];
        
        foreach (['get', 'post', 'put', 'delete'] as $method) {
            $mock->shouldReceive($method)->andReturn($errorResponse);
        }
        
        return $mock;
    }

    /**
     * Create a mock cache that behaves as expected
     */
    public static function createWorkingCache(): CacheInterface
    {
        $mock = Mockery::mock(CacheInterface::class);
        $storage = [];
        
        $mock->shouldReceive('get')
            ->andReturnUsing(function ($key, $default = null) use (&$storage) {
                return $storage[$key] ?? $default;
            });
            
        $mock->shouldReceive('set')
            ->andReturnUsing(function ($key, $value, $ttl = null) use (&$storage) {
                $storage[$key] = $value;
                return true;
            });
            
        $mock->shouldReceive('delete')
            ->andReturnUsing(function ($key) use (&$storage) {
                unset($storage[$key]);
                return true;
            });
            
        $mock->shouldReceive('clear')
            ->andReturnUsing(function () use (&$storage) {
                $storage = [];
                return true;
            });
            
        $mock->shouldReceive('has')
            ->andReturnUsing(function ($key) use (&$storage) {
                return isset($storage[$key]);
            });
        
        return $mock;
    }

    /**
     * Create a mock cache that always fails
     */
    public static function createFailingCache(): CacheInterface
    {
        $mock = Mockery::mock(CacheInterface::class);
        
        $mock->shouldReceive('get')->andReturn(null);
        $mock->shouldReceive('set')->andReturn(false);
        $mock->shouldReceive('delete')->andReturn(false);
        $mock->shouldReceive('clear')->andReturn(false);
        $mock->shouldReceive('has')->andReturn(false);
        
        return $mock;
    }

    /**
     * Create test data for various DTOs
     */
    public static function createAccountData(array $overrides = []): array
    {
        return array_merge([
            'id' => 'acc_' . bin2hex(random_bytes(12)),
            'email' => 'test+' . bin2hex(random_bytes(4)) . '@example.com',
            'phone' => '+1' . rand(2000000000, 9999999999),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'created_at' => date('c'),
            'updated_at' => date('c'),
            'status' => 'active',
        ], $overrides);
    }

    public static function createSessionData(array $overrides = []): array
    {
        return array_merge([
            'session_id' => 'sess_' . bin2hex(random_bytes(12)),
            'account_id' => 'acc_' . bin2hex(random_bytes(12)),
            'risk_level' => 'low',
            'score' => round(rand(0, 1000) / 1000, 3),
            'status' => 'completed',
            'created_at' => date('c'),
            'updated_at' => date('c'),
        ], $overrides);
    }

    public static function createPhoneData(array $overrides = []): array
    {
        return array_merge([
            'number' => '+1' . rand(2000000000, 9999999999),
            'country_code' => 'US',
            'is_valid' => true,
            'carrier' => 'Verizon',
            'line_type' => 'mobile',
        ], $overrides);
    }

    public static function createAddressData(array $overrides = []): array
    {
        return array_merge([
            'street' => '123 Main St',
            'city' => 'San Francisco',
            'state' => 'CA',
            'postal_code' => '94105',
            'country' => 'US',
        ], $overrides);
    }

    public static function createEmailData(array $overrides = []): array
    {
        return array_merge([
            'address' => 'test@example.com',
            'is_valid' => true,
            'domain' => 'example.com',
            'is_disposable' => false,
        ], $overrides);
    }

    /**
     * Create API response structures - Verisoul API returns direct data
     */
    public static function createSuccessResponse(array $data = []): array
    {
        return $data;
    }

    public static function createErrorResponse(string $message = 'Error', int $code = 400): array
    {
        return [
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => [],
            ]
        ];
    }

    public static function createValidationErrorResponse(array $errors = []): array
    {
        return [
            'error' => [
                'code' => 422,
                'message' => 'Validation failed',
                'details' => $errors,
            ]
        ];
    }

    /**
     * Create realistic AuthenticateSessionResponse data based on actual Verisoul API response
     */
    public static function createAuthenticateSessionResponseData(array $overrides = []): array
    {
        $sessionId = 'sess_' . bin2hex(random_bytes(12));
        $accountId = 'acc_' . bin2hex(random_bytes(12));
        $projectId = 'proj_' . bin2hex(random_bytes(8));
        $requestId = 'req_' . bin2hex(random_bytes(12));
        
        return array_merge([
            'project_id' => $projectId,
            'session_id' => $sessionId,
            'account_id' => $accountId,
            'request_id' => $requestId,
            'decision' => 'Real',
            'account_score' => 0.2191,
            'bot' => 0,
            'multiple_accounts' => 0.2053,
            'risk_signals' => 0.2979,
            'accounts_linked' => 2,
            'lists' => ['us_users'],
            'session' => [
                'account_ids' => [$accountId],
                'request_id' => $requestId,
                'project_id' => $projectId,
                'session_id' => $sessionId,
                'start_time' => '2025-06-10T16:45:21.822Z',
                'true_country_code' => 'US',
                'network' => [
                    'ip_address' => '2600:1700:261:b810:8828:f0b6:3b95:667c',
                    'service_provider' => 'AT&T Enterprises, LLC',
                    'connection_type' => 'isp'
                ],
                'location' => [
                    'continent' => 'NA',
                    'country_code' => 'US',
                    'state' => 'Texas',
                    'city' => 'Austin',
                    'zip_code' => '78729',
                    'timezone' => 'America/Chicago',
                    'latitude' => 30.4521,
                    'longitude' => -97.7688
                ],
                'browser' => [
                    'type' => 'Chrome',
                    'version' => '137.0.0.0',
                    'language' => 'en-US',
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36',
                    'timezone' => 'America/Chicago'
                ],
                'device' => [
                    'category' => 'desktop',
                    'type' => 'Mac',
                    'os' => 'macOS 10.15.7',
                    'cpu_cores' => 16,
                    'memory' => 8,
                    'gpu' => 'ANGLE (Apple, ANGLE Metal Renderer: Apple M4 Max, Unspecified Version)',
                    'screen_height' => 1329,
                    'screen_width' => 2056
                ],
                'risk_signals' => [
                    'device_risk' => false,
                    'proxy' => false,
                    'vpn' => false,
                    'tor' => false,
                    'spoofed_ip' => false,
                    'datacenter' => false,
                    'recent_fraud_ip' => false,
                    'impossible_travel' => false,
                    'device_network_mismatch' => false
                ],
                'bot' => [
                    'mouse_num_events' => 84,
                    'click_num_events' => 2,
                    'keyboard_num_events' => 0,
                    'touch_num_events' => 0,
                    'clipboard_num_events' => 1
                ]
            ],
            'account' => [
                'account' => [
                    'id' => $accountId,
                    'email' => 'john.doe@example.com',
                    'metadata' => [],
                    'group' => ''
                ],
                'num_sessions' => 1,
                'first_seen' => '2025-06-10T16:47:29.661Z',
                'last_seen' => '2025-06-10T16:47:29.661Z',
                'last_session' => $sessionId,
                'country' => 'US',
                'countries' => ['US'],
                'unique_devices' => [
                    '1_day' => 1,
                    '7_day' => 1
                ],
                'unique_networks' => [
                    '1_day' => 1,
                    '7_day' => 1
                ],
                'email' => [
                    'email' => 'john.doe@example.com',
                    'disposable' => false,
                    'personal' => false,
                    'valid' => true
                ],
                'risk_signal_average' => [
                    'device_risk' => 0.3971,
                    'proxy' => 0,
                    'vpn' => 0,
                    'tor' => 0,
                    'spoofed_ip' => 0,
                    'datacenter' => 0,
                    'recent_fraud_ip' => 0,
                    'impossible_travel' => 0,
                    'device_network_mismatch' => 0.0001
                ]
            ],
            'linked_accounts' => []
        ], $overrides);
    }

    /**
     * Create realistic SessionResponse data based on actual Verisoul API response
     */
    public static function createSessionResponseData(array $overrides = []): array
    {
        $accountId = 'acc_' . bin2hex(random_bytes(12));
        $sessionId = 'sess_' . bin2hex(random_bytes(12));
        $requestId = 'req_' . bin2hex(random_bytes(12));
        $projectId = 'proj_' . bin2hex(random_bytes(8));
        
        return array_merge([
            'account_ids' => [$accountId],
            'request_id' => $requestId,
            'project_id' => $projectId,
            'session_id' => $sessionId,
            'start_time' => '2025-06-10T16:45:21.822Z',
            'true_country_code' => 'US',
            'network' => [
                'ip_address' => '2600:1700:261:b810:8828:f0b6:3b95:667c',
                'service_provider' => 'AT&T Enterprises, LLC',
                'connection_type' => 'isp'
            ],
            'location' => [
                'continent' => 'NA',
                'country_code' => 'US',
                'state' => 'Texas',
                'city' => 'Austin',
                'zip_code' => '78729',
                'timezone' => 'America/Chicago',
                'latitude' => 30.4521,
                'longitude' => -97.7688
            ],
            'browser' => [
                'type' => 'Chrome',
                'version' => '137.0.0.0',
                'language' => 'en-US',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36',
                'timezone' => 'America/Chicago'
            ],
            'device' => [
                'category' => 'desktop',
                'type' => 'Mac',
                'os' => 'macOS 10.15.7',
                'cpu_cores' => 16,
                'memory' => 8,
                'gpu' => 'ANGLE (Apple, ANGLE Metal Renderer: Apple M4 Max, Unspecified Version)',
                'screen_height' => 1329,
                'screen_width' => 2056
            ],
            'risk_signals' => [
                'device_risk' => false,
                'proxy' => false,
                'vpn' => false,
                'tor' => false,
                'spoofed_ip' => false,
                'datacenter' => false,
                'recent_fraud_ip' => false,
                'impossible_travel' => false,
                'device_network_mismatch' => false
            ],
            'bot' => [
                'mouse_num_events' => 84,
                'click_num_events' => 2,
                'keyboard_num_events' => 0,
                'touch_num_events' => 0,
                'clipboard_num_events' => 1
            ]
        ], $overrides);
    }

    /**
     * Create realistic AccountResponse data based on actual Verisoul API response
     */
    public static function createAccountResponseData(array $overrides = []): array
    {
        $accountId = 'acc_' . bin2hex(random_bytes(12));
        $sessionId = 'sess_' . bin2hex(random_bytes(12));
        $projectId = 'proj_' . bin2hex(random_bytes(8));
        $requestId = 'req_' . bin2hex(random_bytes(12));
        
        return array_merge([
            'project_id' => $projectId,
            'request_id' => $requestId,
            'account' => [
                'id' => $accountId,
                'email' => 'john.doe@example.com',
                'metadata' => [],
                'group' => ''
            ],
            'num_sessions' => 1,
            'first_seen' => '2025-06-10T16:47:29.661Z',
            'last_seen' => '2025-06-10T16:47:29.661Z',
            'last_session' => $sessionId,
            'country' => 'US',
            'countries' => ['US'],
            'decision' => 'Real',
            'account_score' => 0.2191,
            'bot' => 0,
            'multiple_accounts' => 0.2053,
            'risk_signals' => 0.2979,
            'accounts_linked' => 2,
            'lists' => ['us_users'],
            'unique_devices' => [
                '1_day' => 1,
                '7_day' => 1
            ],
            'unique_networks' => [
                '1_day' => 1,
                '7_day' => 1
            ],
            'email' => [
                'email' => 'john.doe@example.com',
                'disposable' => false,
                'personal' => false,
                'valid' => true
            ],
            'risk_signal_average' => [
                'device_risk' => 0.3971,
                'proxy' => 0,
                'vpn' => 0,
                'tor' => 0,
                'spoofed_ip' => 0,
                'datacenter' => 0,
                'recent_fraud_ip' => 0,
                'impossible_travel' => 0,
                'device_network_mismatch' => 0.0001
            ]
        ], $overrides);
    }

    /**
     * Create realistic VerifyPhoneResponse data based on actual Verisoul API response
     */
    public static function createVerifyPhoneResponseData(array $overrides = []): array
    {
        $projectId = 'proj_' . bin2hex(random_bytes(8));
        $requestId = 'req_' . bin2hex(random_bytes(12));
        
        return array_merge([
            'project_id' => $projectId,
            'request_id' => $requestId,
            'phone' => [
                'valid' => true,
                'phone_number' => '+1234567890',
                'calling_country_code' => '1',
                'country_code' => 'US',
                'carrier_name' => 'Verizon Wireless',
                'line_type' => 'mobile'
            ]
        ], $overrides);
    }

    /**
     * Create realistic VerifyFaceResponse data based on actual Verisoul API response
     */
    public static function createVerifyFaceResponseData(array $overrides = []): array
    {
        $sessionId = 'sess_' . bin2hex(random_bytes(12));
        $accountId = 'acc_' . bin2hex(random_bytes(12));
        $projectId = 'proj_' . bin2hex(random_bytes(8));
        $requestId = 'req_' . bin2hex(random_bytes(12));
        $referringSessionId = 'ref_sess_' . bin2hex(random_bytes(12));
        
        return array_merge([
            'metadata' => [
                'project_id' => $projectId,
                'session_id' => $sessionId,
                'account_id' => $accountId,
                'referring_session_id' => $referringSessionId,
                'request_id' => $requestId,
                'timestamp' => '2025-05-04T22:11:51.645Z'
            ],
            'decision' => 'Real',
            'risk_score' => 0.2,
            'risk_flags' => [],
            'device_network_signals' => [
                'device_risk' => 0.2429,
                'proxy' => 0,
                'vpn' => 0,
                'datacenter' => 0,
                'tor' => 0,
                'spoofed_ip' => 0,
                'recent_fraud_ip' => 0,
                'device_network_mismatch' => 0.0001,
                'location_spoofing' => 0.0001
            ],
            'referring_session_signals' => [
                'impossible_travel' => 0,
                'ip_mismatch' => 0,
                'user_agent_mismatch' => 0,
                'device_timezone_mismatch' => 0.2501,
                'ip_timezone_mismatch' => 0.0001
            ],
            'photo_urls' => [
                'face' => 'https://storage.googleapis.com/facematch-sandbox/' . $sessionId . '/face.jpg'
            ],
            'session_data' => [
                'true_country_code' => 'US',
                'network' => [
                    'ip_address' => '107.209.253.92',
                    'service_provider' => 'AT&T Internet',
                    'connection_type' => 'isp'
                ],
                'location' => [
                    'continent' => 'NA',
                    'country_code' => 'US',
                    'state' => 'Texas',
                    'city' => 'Austin',
                    'zip_code' => '78758',
                    'timezone' => 'America/Chicago',
                    'latitude' => 30.3773,
                    'longitude' => -97.71
                ],
                'browser' => [
                    'type' => 'Chrome',
                    'version' => '135.0.0.0',
                    'language' => 'en-US',
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
                    'timezone' => 'America/Chicago'
                ],
                'device' => [
                    'category' => 'desktop',
                    'type' => 'Mac',
                    'os' => 'macOS 10.15.7',
                    'cpu_cores' => 16,
                    'memory' => 8,
                    'gpu' => 'ANGLE (Apple, ANGLE Metal Renderer: Apple M4 Max, Unspecified Version)'
                ]
            ],
            'matches' => [
                'num_accounts_linked' => 0,
                'accounts_linked' => []
            ]
        ], $overrides);
    }

    /**
     * Create realistic VerifyIdResponse data based on actual Verisoul API response
     */
    public static function createVerifyIdResponseData(array $overrides = []): array
    {
        $sessionId = 'sess_' . bin2hex(random_bytes(12));
        $accountId = 'acc_' . bin2hex(random_bytes(12));
        $projectId = 'proj_' . bin2hex(random_bytes(8));
        $requestId = 'req_' . bin2hex(random_bytes(12));
        $referringSessionId = 'ref_sess_' . bin2hex(random_bytes(12));
        
        return array_merge([
            'metadata' => [
                'project_id' => $projectId,
                'session_id' => $sessionId,
                'account_id' => $accountId,
                'referring_session_id' => $referringSessionId,
                'request_id' => $requestId,
                'timestamp' => '2025-05-04T22:11:51.645Z'
            ],
            'decision' => 'Real',
            'risk_score' => 0.1,
            'risk_flags' => [],
            'document_signals' => [
                'id_age' => 25,
                'id_face_match_score' => 0.9,
                'id_barcode_status' => 'barcode_found_and_valid',
                'id_face_status' => 'likely_original_face',
                'id_text_status' => 'likely_original_text',
                'is_id_digital_spoof' => 'likely_physical_id',
                'is_full_id_captured' => 'full_id_detected',
                'id_validity' => 'likely_authentic_id'
            ],
            'document_data' => [
                'template_info' => [
                    'document_country_code' => 'US',
                    'document_state' => 'Texas',
                    'template_type' => 'Driver License'
                ],
                'user_data' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'date_of_birth' => '1997-02-26',
                    'date_of_expiration' => '2034-02-26',
                    'date_of_issue' => '2025-03-19',
                    'id_number' => '1234567890',
                    'id_number2' => '0987654321',
                    'address' => [
                        'city' => 'Austin',
                        'country' => 'US',
                        'postal_code' => '78701',
                        'state' => 'TX',
                        'street' => '123 Main St'
                    ]
                ]
            ],
            'device_network_signals' => [
                'device_risk' => 0.2429,
                'proxy' => 0,
                'vpn' => 0,
                'datacenter' => 0,
                'tor' => 0,
                'spoofed_ip' => 0,
                'recent_fraud_ip' => 0,
                'device_network_mismatch' => 0.0001,
                'location_spoofing' => 0.0001
            ],
            'referring_session_signals' => [
                'impossible_travel' => 0,
                'ip_mismatch' => 0,
                'user_agent_mismatch' => 0,
                'device_timezone_mismatch' => 0.2501,
                'ip_timezone_mismatch' => 0.0001
            ],
            'photo_urls' => [
                'face' => 'https://storage.googleapis.com/facematch-sandbox/' . $sessionId . '/face.jpg',
                'id_scan_back' => 'https://storage.googleapis.com/facematch-sandbox/' . $sessionId . '/id_scan_back.jpg',
                'id_scan_front' => 'https://storage.googleapis.com/facematch-sandbox/' . $sessionId . '/id_scan_front.jpg'
            ],
            'session_data' => [
                'true_country_code' => 'US',
                'network' => [
                    'ip_address' => '107.209.253.92',
                    'service_provider' => 'AT&T Internet',
                    'connection_type' => 'isp'
                ],
                'location' => [
                    'continent' => 'NA',
                    'country_code' => 'US',
                    'state' => 'Texas',
                    'city' => 'Austin',
                    'zip_code' => '78758',
                    'timezone' => 'America/Chicago',
                    'latitude' => 30.3773,
                    'longitude' => -97.71
                ],
                'browser' => [
                    'type' => 'Chrome',
                    'version' => '135.0.0.0',
                    'language' => 'en-US',
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
                    'timezone' => 'America/Chicago'
                ],
                'device' => [
                    'category' => 'desktop',
                    'type' => 'Mac',
                    'os' => 'macOS 10.15.7',
                    'cpu_cores' => 16,
                    'memory' => 8,
                    'gpu' => 'ANGLE (Apple, ANGLE Metal Renderer: Apple M4 Max, Unspecified Version)'
                ]
            ],
            'matches' => [
                'num_accounts_linked' => 0,
                'accounts_linked' => []
            ]
        ], $overrides);
    }

    /**
     * Create realistic LivenessSessionResponse data
     */
    public static function createLivenessSessionResponseData(array $overrides = []): array
    {
        $sessionId = 'liveness_sess_' . bin2hex(random_bytes(12));
        
        return array_merge([
            'session_id' => $sessionId,
            'session_url' => 'https://liveness.verisoul.ai/session/' . $sessionId,
            'expires_at' => '2024-01-15T13:00:00Z',
            'type' => 'face_match',
            'id_required' => false
        ], $overrides);
    }

    /**
     * Create realistic EnrollAccountResponse data
     */
    public static function createEnrollAccountResponseData(array $overrides = []): array
    {
        $sessionId = 'enroll_sess_' . bin2hex(random_bytes(12));
        $accountId = 'acc_' . bin2hex(random_bytes(12));
        
        return array_merge([
            'session_id' => $sessionId,
            'account_id' => $accountId,
            'enrolled' => true,
            'enrollment_status' => 'completed',
            'template_id' => 'template_' . bin2hex(random_bytes(8)),
            'face_verified' => true
        ], $overrides);
    }

    /**
     * Create simple AccountSessionsResponse data based on actual Verisoul API response
     */
    public static function createAccountSessionsResponseData(array $overrides = []): array
    {
        $requestId = 'req_' . bin2hex(random_bytes(12));
        
        return array_merge([
            'request_id' => $requestId,
            'sessions' => []
        ], $overrides);
    }

    /**
     * Create simple LinkedAccountsResponse data based on actual Verisoul API response
     */
    public static function createLinkedAccountsResponseData(array $overrides = []): array
    {
        $requestId = 'req_' . bin2hex(random_bytes(12));
        
        return array_merge([
            'request_id' => $requestId,
            'accounts_linked' => []
        ], $overrides);
    }

    /**
     * Create simple DeleteAccountResponse data based on actual Verisoul API response
     */
    public static function createDeleteAccountResponseData(array $overrides = []): array
    {
        $accountId = 'acc_' . bin2hex(random_bytes(12));
        $requestId = 'req_' . bin2hex(random_bytes(12));
        
        return array_merge([
            'request_id' => $requestId,
            'account_id' => $accountId,
            'success' => true
        ], $overrides);
    }

    /**
     * Load fixture data from JSON files
     */
    public static function loadFixtureData(string $fixtureName): array
    {
        $fixturePath = __DIR__ . '/../fixtures/api/responses/' . $fixtureName . '.json';
        
        if (!file_exists($fixturePath)) {
            throw new \InvalidArgumentException("Fixture file not found: {$fixturePath}");
        }
        
        $jsonContent = file_get_contents($fixturePath);
        $data = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("Invalid JSON in fixture file: {$fixturePath}");
        }
        
        return $data;
    }

    /**
     * Create HTTP client with fixture-based responses
     */
    public static function createHttpClientWithFixtures(array $fixtureMap = []): HttpClientInterface
    {
        $mock = Mockery::mock(HttpClientInterface::class);
        
        // Setup common method expectations
        $mock->shouldReceive('setTimeout')->andReturnSelf();
        $mock->shouldReceive('setConnectTimeout')->andReturnSelf();
        $mock->shouldReceive('setHeaders')->andReturnSelf();
        
        foreach (['get', 'post', 'put', 'delete'] as $method) {
            if (isset($fixtureMap[$method])) {
                $response = self::loadFixtureData($fixtureMap[$method]);
                $mock->shouldReceive($method)->andReturn($response);
            } else {
                $mock->shouldReceive($method)->andReturn([]);
            }
        }
        
        return $mock;
    }

    /**
     * Create fixture-based response data methods
     */
    public static function createAccountResponseFromFixture(array $overrides = []): array
    {
        $data = self::loadFixtureData('get-account');
        return array_merge($data, $overrides);
    }

    public static function createSessionResponseFromFixture(array $overrides = []): array
    {
        $data = self::loadFixtureData('get-session');
        return array_merge($data, $overrides);
    }

    public static function createVerifyFaceResponseFromFixture(array $overrides = []): array
    {
        $data = self::loadFixtureData('verify-face');
        return array_merge($data, $overrides);
    }

    public static function createVerifyIdResponseFromFixture(array $overrides = []): array
    {
        $data = self::loadFixtureData('verify-id');
        return array_merge($data, $overrides);
    }

    public static function createPhoneVerificationResponseFromFixture(array $overrides = []): array
    {
        $data = self::loadFixtureData('phone-verification');
        return array_merge($data, $overrides);
    }

    public static function createDeleteAccountResponseFromFixture(array $overrides = []): array
    {
        $data = self::loadFixtureData('delete-account');
        return array_merge($data, $overrides);
    }

    public static function createAccountSessionsResponseFromFixture(array $overrides = []): array
    {
        $data = self::loadFixtureData('get-account-sessions');
        return array_merge($data, $overrides);
    }

    public static function createLinkedAccountsResponseFromFixture(array $overrides = []): array
    {
        $data = self::loadFixtureData('get-linked-accounts');
        return array_merge($data, $overrides);
    }

    public static function createVerifyIdentifyResponseFromFixture(array $overrides = []): array
    {
        $data = self::loadFixtureData('verify-identify');
        return array_merge($data, $overrides);
    }

    public static function createAuthenticateSessionResponseFromFixture(array $overrides = []): array
    {
        $data = self::loadFixtureData('authenticate-session');
        return array_merge($data, $overrides);
    }

    public static function createUnauthenticatedSessionResponseFromFixture(array $overrides = []): array
    {
        $data = self::loadFixtureData('unauthenticated-session');
        return array_merge($data, $overrides);
    }

    /**
     * Create a retry strategy for tests that doesn't sleep
     */
    public static function createTestRetryStrategy(): RetryStrategy
    {
        return new RetryStrategy(
            maxAttempts: 1, // No retries in tests
            baseDelayMs: 0,
            backoffMultiplier: 1.0,
            maxDelayMs: 0
        );
    }

    /**
     * Get test-optimized client constructor parameters to avoid delays
     */
    public static function getTestClientParams(array $overrides = []): array
    {
        return array_merge([
            'retryAttempts' => 1,
            'retryDelay' => 0
        ], $overrides);
    }
}