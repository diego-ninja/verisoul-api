<?php

use Ninja\Verisoul\Clients\SessionClient;
use Ninja\Verisoul\Contracts\HttpClientInterface;
use Ninja\Verisoul\DTO\UserAccount;
use Ninja\Verisoul\Enums\VerisoulEnvironment;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Ninja\Verisoul\Responses\AuthenticateSessionResponse;
use Ninja\Verisoul\Responses\SessionResponse;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('SessionClient', function (): void {
    describe('construction', function (): void {
        it('can be created with default parameters', function (): void {
            $client = new SessionClient('test_api_key');

            expect($client)->toBeInstanceOf(SessionClient::class)
                ->and($client->getEnvironment())->toBe(VerisoulEnvironment::Sandbox);
        });

        it('can be created with custom environment', function (): void {
            $client = new SessionClient('prod_key', VerisoulEnvironment::Production);

            expect($client->getEnvironment())->toBe(VerisoulEnvironment::Production);
        });

        it('inherits from Client base class', function (): void {
            $client = new SessionClient('test_api_key');

            expect($client)->toBeInstanceOf(Ninja\Verisoul\Clients\Client::class);
        });
    });

    describe('authenticate method', function (): void {
        it('creates AuthenticateSessionResponse object', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'post' => MockFactory::createAuthenticateSessionResponseData([
                    'sessionId' => 'session_123',
                    'accountId' => 'user_456',
                ]),
            ]);

            $client = new SessionClient('test_api_key', httpClient: $mockHttpClient);
            $userAccount = UserAccount::from(['id' => 'test_user']);

            $response = $client->authenticate($userAccount, 'session_123');

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('passes user account data correctly', function (): void {
            $userAccount = UserAccount::from([
                'id' => 'user_123',
                'email' => 'test@example.com',
                'metadata' => ['source' => 'test'],
                'group' => 'standard',
            ]);

            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('post')
                ->once()
                ->withArgs(fn($url, $data) => str_contains($url, '/session/authenticate') &&
                           isset($data['account'], $data['session_id'])
                            &&
                           'test_session' === $data['session_id'])
                ->andReturn(MockFactory::createAuthenticateSessionResponseData([
                    'session_id' => 'test_session',
                ]));

            $client = new SessionClient('test_key', httpClient: $mockHttpClient);
            $response = $client->authenticate($userAccount, 'test_session');

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('handles accounts_linked parameter', function (): void {
            $userAccount = UserAccount::from(['id' => 'user_123']);

            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('post')
                ->once()
                ->withArgs(fn($url, $data) => str_contains($url, '/session/authenticate') &&
                           isset($data['account'], $data['session_id']))
                ->andReturn(MockFactory::createAuthenticateSessionResponseFromFixture([
                    'session_id' => 'test',
                ]));

            $client = new SessionClient('test_key', httpClient: $mockHttpClient);
            $response = $client->authenticate($userAccount, 'test_session', true);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('throws VerisoulConnectionException on connection failure', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(SessionClient::class, ['httpClient' => $failingClient]);
            $userAccount = UserAccount::from(['id' => 'test_user']);

            expect(fn() => $client->authenticate($userAccount, 'session_123'))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(SessionClient::class, ['httpClient' => $failingClient]);
            $userAccount = UserAccount::from(['id' => 'test_user']);

            expect(fn() => $client->authenticate($userAccount, 'session_123'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('unauthenticated method', function (): void {
        it('creates SessionResponse object', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'post' => MockFactory::createSessionResponseData([
                    'session_id' => 'unauth_session_123',
                ]),
            ]);

            $client = new SessionClient('test_api_key', httpClient: $mockHttpClient);

            $response = $client->unauthenticated('unauth_session_123');

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });

        it('passes session ID correctly', function (): void {
            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('post')
                ->once()
                ->withArgs(fn($url, $data) => str_contains($url, '/session/unauthenticated') &&
                           isset($data['session_id']) &&
                           'test_session' === $data['session_id'])
                ->andReturn(MockFactory::createSessionResponseData([
                    'session_id' => 'test_session',
                ]));

            $client = new SessionClient('test_key', httpClient: $mockHttpClient);
            $response = $client->unauthenticated('test_session');

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });

        it('handles accounts_linked parameter', function (): void {
            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('post')
                ->once()
                ->withArgs(fn($url, $data) => str_contains($url, '/session/unauthenticated') &&
                           isset($data['session_id']))
                ->andReturn(MockFactory::createSessionResponseFromFixture([
                    'session_id' => 'test',
                ]));

            $client = new SessionClient('test_key', httpClient: $mockHttpClient);
            $response = $client->unauthenticated('test_session', true);

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });

        it('throws VerisoulConnectionException on connection failure', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(SessionClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->unauthenticated('session_123'))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(SessionClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->unauthenticated('session_123'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('getSession method', function (): void {
        it('creates SessionResponse object', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => MockFactory::createSessionResponseData([
                    'session_id' => 'get_session_123',
                ]),
            ]);

            $client = new SessionClient('test_api_key', httpClient: $mockHttpClient);

            $response = $client->getSession('get_session_123');

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });

        it('constructs correct URL with session ID', function (): void {
            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('get')
                ->once()
                ->withArgs(fn($url, $params) => str_contains($url, '/session/test_session_123'))
                ->andReturn(MockFactory::createSessionResponseData([
                    'session_id' => 'test_session_123',
                ]));

            $client = new SessionClient('test_key', httpClient: $mockHttpClient);
            $response = $client->getSession('test_session_123');

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });

        it('throws VerisoulConnectionException on connection failure', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(SessionClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->getSession('session_123'))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(SessionClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->getSession('session_123'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('environment integration', function (): void {
        it('uses sandbox URLs in sandbox environment', function (): void {
            $client = new SessionClient('sandbox_key', VerisoulEnvironment::Sandbox);

            expect($client->getBaseUrl())->toBe('https://api.sandbox.verisoul.ai');
        });

        it('uses production URLs in production environment', function (): void {
            $client = new SessionClient('prod_key', VerisoulEnvironment::Production);

            expect($client->getBaseUrl())->toBe('https://api.prod.verisoul.ai');
        });

        it('makes requests to correct environment', function (): void {
            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('post')
                ->once()
                ->withArgs(fn($url, $data) => str_contains($url, 'https://api.prod.verisoul.ai'))
                ->andReturn(MockFactory::createAuthenticateSessionResponseData());

            $prodClient = new SessionClient(
                'prod_key',
                VerisoulEnvironment::Production,
                httpClient: $mockHttpClient,
            );

            $userAccount = UserAccount::from(['id' => 'prod_user']);
            $prodClient->authenticate($userAccount, 'prod_session');
        });
    });

    describe('response object handling', function (): void {
        it('correctly creates AuthenticateSessionResponse from API response', function (): void {
            $apiResponse = MockFactory::createAuthenticateSessionResponseData([
                'session_id' => 'auth_test_123',
            ]);

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $apiResponse]);
            $client = new SessionClient('test_key', httpClient: $mockHttpClient);

            $userAccount = UserAccount::from(['id' => 'test_user']);
            $response = $client->authenticate($userAccount, 'test_session');

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('correctly creates SessionResponse from unauthenticated', function (): void {
            $apiResponse = MockFactory::createSessionResponseData([
                'session_id' => 'session_test_456',
            ]);

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $apiResponse]);
            $client = new SessionClient('test_key', httpClient: $mockHttpClient);

            $response = $client->unauthenticated('test_session');

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });

        it('handles SessionResponse from getSession method', function (): void {
            $apiResponse = MockFactory::createSessionResponseData([
                'session_id' => 'get_test_789',
            ]);

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['get' => $apiResponse]);
            $client = new SessionClient('test_key', httpClient: $mockHttpClient);

            $response = $client->getSession('test_session');

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });
    });

    describe('real-world usage scenarios', function (): void {
        it('handles complete authentication workflow', function (): void {
            $userAccount = UserAccount::from([
                'id' => 'real_user_123',
                'email' => 'user@company.com',
                'metadata' => ['signup_source' => 'mobile_app', 'device_id' => 'device_456'],
                'group' => 'premium',
            ]);

            $authResponse = MockFactory::createAuthenticateSessionResponseData([
                'session_id' => 'authenticated_session_456',
                'account_id' => 'real_user_123',
            ]);

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $authResponse]);
            $client = new SessionClient('test_key', httpClient: $mockHttpClient);

            $response = $client->authenticate($userAccount, 'session_456', true);

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });

        it('handles session retrieval after authentication', function (): void {
            $sessionResponse = MockFactory::createSessionResponseData([
                'session_id' => 'retrieved_session_789',
            ]);

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['get' => $sessionResponse]);
            $client = new SessionClient('test_key', httpClient: $mockHttpClient);

            $response = $client->getSession('retrieved_session_789');

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });

        it('handles unauthenticated session for anonymous users', function (): void {
            $unauthResponse = MockFactory::createSessionResponseData([
                'session_id' => 'anonymous_session_321',
            ]);

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['post' => $unauthResponse]);
            $client = new SessionClient('test_key', httpClient: $mockHttpClient);

            $response = $client->unauthenticated('anonymous_session_321', false);

            expect($response)->toBeInstanceOf(SessionResponse::class);
        });
    });

    describe('parameter validation scenarios', function (): void {
        it('handles various session ID formats', function (): void {
            $sessionIds = [
                'simple123',
                'session_with_underscores_456',
                'session-with-hyphens-789',
            ];

            $mockResponse = MockFactory::createSessionResponseData(['session_id' => 'test']);

            foreach ($sessionIds as $sessionId) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient(['get' => $mockResponse]);
                $client = new SessionClient('test_key', httpClient: $mockHttpClient);

                $response = $client->getSession($sessionId);
                expect($response)->toBeInstanceOf(SessionResponse::class);
            }
        });

        it('properly serializes complex UserAccount objects', function (): void {
            $complexAccount = UserAccount::from([
                'id' => 'complex_user_789',
                'email' => 'complex@example.com',
                'group' => 'enterprise',
                'metadata' => [
                    'signup_date' => '2024-01-01',
                    'source' => 'web',
                    'preferences' => ['newsletter' => true, 'sms' => false],
                    'verification_status' => 'verified',
                ],
            ]);

            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'post' => MockFactory::createAuthenticateSessionResponseData([
                    'session_id' => 'complex_session',
                ]),
            ]);
            $client = new SessionClient('test_key', httpClient: $mockHttpClient);

            $response = $client->authenticate($complexAccount, 'complex_session');

            expect($response)->toBeInstanceOf(AuthenticateSessionResponse::class);
        });
    });
});
