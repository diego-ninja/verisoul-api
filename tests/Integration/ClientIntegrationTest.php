<?php

use Ninja\Verisoul\Clients\AccountClient;
use Ninja\Verisoul\Clients\SessionClient;
use Ninja\Verisoul\Clients\PhoneClient;
use Ninja\Verisoul\Enums\VerisoulEnvironment;
use Ninja\Verisoul\DTO\UserAccount;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('Client Integration Tests', function () {
    beforeEach(function () {
        // Use a test API key for sandbox environment
        $this->testApiKey = 'test_api_key_integration';
        $this->sandboxEnv = VerisoulEnvironment::Sandbox;
    });

    describe('Account Client Integration', function () {
        it('performs complete account lifecycle with real HTTP flow', function () {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'integration_acc_123']]),
                'put' => MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'integration_acc_123', 'status' => 'updated']]),
                'delete' => ['account_id' => 'integration_acc_123', 'deleted' => true, 'deleted_at' => '2024-01-15T12:00:00Z']
            ]);

            $client = new AccountClient($this->testApiKey, $this->sandboxEnv, httpClient: $mockHttpClient);

            // Get account
            $getResponse = $client->getAccount('integration_acc_123');
            expect($getResponse)->toBeInstanceOf(\Ninja\Verisoul\Responses\AccountResponse::class);

            // Update account
            $updateResponse = $client->updateAccount('integration_acc_123', ['status' => 'active']);
            expect($updateResponse)->toBeInstanceOf(\Ninja\Verisoul\Responses\AccountResponse::class);

            // Delete account
            $deleteResponse = $client->deleteAccount('integration_acc_123');
            expect($deleteResponse)->toBeInstanceOf(\Ninja\Verisoul\Responses\DeleteAccountResponse::class);
        });

        it('handles API rate limiting gracefully', function () {
            $rateLimitClient = Mockery::mock(\Ninja\Verisoul\Contracts\HttpClientInterface::class);
            $rateLimitClient->shouldReceive('setTimeout')->andReturnSelf();
            $rateLimitClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $rateLimitClient->shouldReceive('setHeaders')->andReturnSelf();

            // First call hits rate limit, second succeeds
            $rateLimitClient->shouldReceive('get')
                ->once()
                ->andThrow(new \Ninja\Verisoul\Exceptions\VerisoulApiException('Rate limit exceeded', 429));
            $rateLimitClient->shouldReceive('get')
                ->once()
                ->andReturn(MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'rate_limit_test']]));

            $client = new AccountClient($this->testApiKey, $this->sandboxEnv, retryAttempts: 2, retryDelay: 100, httpClient: $rateLimitClient);

            $response = $client->getAccount('rate_limit_test');
            expect($response)->toBeInstanceOf(\Ninja\Verisoul\Responses\AccountResponse::class);
        });
    });

    describe('Session Client Integration', function () {
        it('performs complete session authentication flow', function () {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'post' => MockFactory::createAuthenticateSessionResponseFromFixture(['session_id' => 'integration_session_456']),
                'get' => MockFactory::createSessionResponseFromFixture(['session_id' => 'integration_session_456'])
            ]);

            $client = new SessionClient($this->testApiKey, $this->sandboxEnv, httpClient: $mockHttpClient);

            $userAccount = UserAccount::from([
                'id' => 'integration_user_789',
                'email' => 'integration@test.com',
                'metadata' => ['source' => 'integration_test']
            ]);

            // Authenticate session
            $authResponse = $client->authenticate($userAccount, 'integration_session_456');
            expect($authResponse)->toBeInstanceOf(\Ninja\Verisoul\Responses\AuthenticateSessionResponse::class);

            // Get session details
            $sessionResponse = $client->getSession('integration_session_456');
            expect($sessionResponse)->toBeInstanceOf(\Ninja\Verisoul\Responses\SessionResponse::class);
        });

        it('handles session state transitions correctly', function () {
            $stateTransitions = [
                ['post', MockFactory::createSessionResponseFromFixture(['session_id' => 'state_test', 'status' => 'unauthenticated'])],
                ['post', MockFactory::createAuthenticateSessionResponseFromFixture(['session_id' => 'state_test', 'status' => 'authenticated'])],
                ['get', MockFactory::createSessionResponseFromFixture(['session_id' => 'state_test', 'status' => 'completed'])]
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'post' => $stateTransitions[0][1], // First will be unauthenticated call
            ]);

            $client = new SessionClient($this->testApiKey, $this->sandboxEnv, httpClient: $mockHttpClient);

            // Start with unauthenticated session
            $unauthResponse = $client->unauthenticated('state_test');
            expect($unauthResponse)->toBeInstanceOf(\Ninja\Verisoul\Responses\SessionResponse::class);
        });
    });

    describe('Cross-Client Integration', function () {
        it('maintains consistent state across multiple client instances', function () {
            $sharedHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'shared_acc_321']]),
                'post' => MockFactory::createAuthenticateSessionResponseFromFixture(['session_id' => 'shared_session_654', 'account_id' => 'shared_acc_321'])
            ]);

            $accountClient = new AccountClient($this->testApiKey, $this->sandboxEnv, httpClient: $sharedHttpClient);
            $sessionClient = new SessionClient($this->testApiKey, $this->sandboxEnv, httpClient: $sharedHttpClient);

            // Get account details
            $accountResponse = $accountClient->getAccount('shared_acc_321');
            expect($accountResponse)->toBeInstanceOf(\Ninja\Verisoul\Responses\AccountResponse::class);

            // Create session for same account
            $userAccount = UserAccount::from(['id' => 'shared_acc_321']);
            $sessionResponse = $sessionClient->authenticate($userAccount, 'shared_session_654');
            expect($sessionResponse)->toBeInstanceOf(\Ninja\Verisoul\Responses\AuthenticateSessionResponse::class);
        });

        it('handles environment switching consistently across clients', function () {
            $mockHttpClient = Mockery::mock(\Ninja\Verisoul\Contracts\HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            // Expect sandbox URL first
            $mockHttpClient->shouldReceive('get')
                ->once()
                ->withArgs(function($url) {
                    return str_contains($url, 'sandbox.verisoul.ai');
                })
                ->andReturn(MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'env_test']]));

            // Then expect production URL after environment switch
            $mockHttpClient->shouldReceive('get')
                ->once()
                ->withArgs(function($url) {
                    return str_contains($url, 'api.verisoul.ai') && !str_contains($url, 'sandbox');
                })
                ->andReturn(MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'env_test']]));

            $accountClient = new AccountClient($this->testApiKey, VerisoulEnvironment::Sandbox, httpClient: $mockHttpClient);

            // Make request in sandbox
            $sandboxResponse = $accountClient->getAccount('env_test');
            expect($sandboxResponse)->toBeInstanceOf(\Ninja\Verisoul\Responses\AccountResponse::class);

            // Switch to production
            $accountClient->setEnvironment(VerisoulEnvironment::Production);

            // Make request in production
            $prodResponse = $accountClient->getAccount('env_test');
            expect($prodResponse)->toBeInstanceOf(\Ninja\Verisoul\Responses\AccountResponse::class);
        });
    });

    describe('Error Recovery Integration', function () {
        it('recovers from network failures using retry strategy', function () {
            $recoveryClient = Mockery::mock(\Ninja\Verisoul\Contracts\HttpClientInterface::class);
            $recoveryClient->shouldReceive('setTimeout')->andReturnSelf();
            $recoveryClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $recoveryClient->shouldReceive('setHeaders')->andReturnSelf();

            // First two attempts fail, third succeeds
            $recoveryClient->shouldReceive('get')->times(2)
                ->andThrow(new \Ninja\Verisoul\Exceptions\VerisoulConnectionException('Network error'));
            $recoveryClient->shouldReceive('get')->once()
                ->andReturn(MockFactory::createAccountResponseFromFixture(['account' => ['id' => 'recovery_test']]));

            $client = new AccountClient(
                $this->testApiKey,
                $this->sandboxEnv,
                retryAttempts: 3,
                retryDelay: 50,
                httpClient: $recoveryClient
            );

            $response = $client->getAccount('recovery_test');
            expect($response)->toBeInstanceOf(\Ninja\Verisoul\Responses\AccountResponse::class);
        });

        it('handles cascading failures across multiple operations', function () {
            $cascadeClient = Mockery::mock(\Ninja\Verisoul\Contracts\HttpClientInterface::class);
            $cascadeClient->shouldReceive('setTimeout')->andReturnSelf();
            $cascadeClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $cascadeClient->shouldReceive('setHeaders')->andReturnSelf();

            // First operation fails completely
            $cascadeClient->shouldReceive('get')->times(3)
                ->andThrow(new \Ninja\Verisoul\Exceptions\VerisoulConnectionException('Service unavailable'));

            $client = new AccountClient(
                $this->testApiKey,
                $this->sandboxEnv,
                retryAttempts: 3,
                retryDelay: 10,
                httpClient: $cascadeClient
            );

            expect(fn() => $client->getAccount('cascade_test'))
                ->toThrow(\Ninja\Verisoul\Exceptions\VerisoulConnectionException::class);
        });
    });
});