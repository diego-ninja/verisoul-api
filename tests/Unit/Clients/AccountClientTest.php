<?php

use Ninja\Verisoul\Clients\AccountClient;
use Ninja\Verisoul\Contracts\HttpClientInterface;
use Ninja\Verisoul\Enums\VerisoulEnvironment;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Ninja\Verisoul\Responses\AccountResponse;
use Ninja\Verisoul\Responses\AccountSessionsResponse;
use Ninja\Verisoul\Responses\DeleteAccountResponse;
use Ninja\Verisoul\Responses\LinkedAccountsResponse;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('AccountClient', function (): void {
    describe('construction', function (): void {
        it('can be created with default parameters', function (): void {
            $client = new AccountClient('test_api_key');

            expect($client)->toBeInstanceOf(AccountClient::class)
                ->and($client->getEnvironment())->toBe(VerisoulEnvironment::Sandbox);
        });

        it('can be created with custom environment', function (): void {
            $client = new AccountClient('prod_key', VerisoulEnvironment::Production);

            expect($client->getEnvironment())->toBe(VerisoulEnvironment::Production);
        });

        it('inherits from Client base class', function (): void {
            $client = new AccountClient('test_api_key');

            expect($client)->toBeInstanceOf(Ninja\Verisoul\Clients\Client::class);
        });
    });

    describe('getAccount method', function (): void {
        it('creates AccountResponse object', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => MockFactory::createAccountResponseData([
                    'account' => ['id' => 'acc_123'],
                ]),
            ]);

            $client = new AccountClient('test_api_key', httpClient: $mockHttpClient);

            $response = $client->getAccount('acc_123');

            expect($response)->toBeInstanceOf(AccountResponse::class);
        });

        it('passes account ID correctly in URL path', function (): void {
            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('get')
                ->once()
                ->withArgs(fn($url, $params) => str_contains($url, '/account/test_account_123'))
                ->andReturn(MockFactory::createAccountResponseData([
                    'account' => ['id' => 'test_account_123'],
                ]));

            $client = new AccountClient('test_key', httpClient: $mockHttpClient);
            $response = $client->getAccount('test_account_123');

            expect($response)->toBeInstanceOf(AccountResponse::class);
        });

        it('handles different account ID formats', function (): void {
            $accountIds = [
                'simple123',
                'acc_with_underscores_456',
                'acc-with-hyphens-789',
                'VeryLongAccountIdWith123Numbers',
            ];

            foreach ($accountIds as $accountId) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient(['get' => [
                    'account_id' => $accountId,
                    'status' => 'active',
                ]]);

                $client = new AccountClient('test_key', httpClient: $mockHttpClient);
                $response = $client->getAccount($accountId);

                expect($response)->toBeInstanceOf(AccountResponse::class);
            }
        });

        it('throws VerisoulConnectionException on connection failure', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(AccountClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->getAccount('acc_123'))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(AccountClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->getAccount('acc_123'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('getAccountSessions method', function (): void {
        it('creates AccountSessionsResponse object', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => [
                    'account_id' => 'acc_123',
                    'sessions' => [
                        ['session_id' => 'sess_1', 'status' => 'completed'],
                        ['session_id' => 'sess_2', 'status' => 'pending'],
                    ],
                ],
            ]);

            $client = new AccountClient('test_api_key', httpClient: $mockHttpClient);

            $response = $client->getAccountSessions('acc_123');

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);
        });

        it('constructs correct URL with account ID', function (): void {
            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('get')
                ->once()
                ->withArgs(fn($url, $params) => str_contains($url, '/account/sessions_account_456/sessions'))
                ->andReturn([
                    'account_id' => 'sessions_account_456',
                    'sessions' => [],
                ]);

            $client = new AccountClient('test_key', httpClient: $mockHttpClient);
            $response = $client->getAccountSessions('sessions_account_456');

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);
        });

        it('throws VerisoulConnectionException on connection failure', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(AccountClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->getAccountSessions('acc_123'))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(AccountClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->getAccountSessions('acc_123'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('getLinkedAccounts method', function (): void {
        it('creates LinkedAccountsResponse object', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => [
                    'account_id' => 'acc_123',
                    'linked_accounts' => [
                        ['account_id' => 'linked_acc_1', 'relationship' => 'family'],
                        ['account_id' => 'linked_acc_2', 'relationship' => 'shared_device'],
                    ],
                ],
            ]);

            $client = new AccountClient('test_api_key', httpClient: $mockHttpClient);

            $response = $client->getLinkedAccounts('acc_123');

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });

        it('constructs correct URL for linked accounts', function (): void {
            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('get')
                ->once()
                ->withArgs(fn($url, $params) => str_contains($url, '/account/linked_test_789/accounts-linked'))
                ->andReturn([
                    'account_id' => 'linked_test_789',
                    'linked_accounts' => [],
                ]);

            $client = new AccountClient('test_key', httpClient: $mockHttpClient);
            $response = $client->getLinkedAccounts('linked_test_789');

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });

        it('throws VerisoulConnectionException on connection failure', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(AccountClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->getLinkedAccounts('acc_123'))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(AccountClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->getLinkedAccounts('acc_123'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('updateAccount method', function (): void {
        it('creates AccountResponse object', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'put' => MockFactory::createAccountResponseFromFixture(),
            ]);

            $client = new AccountClient('test_api_key', httpClient: $mockHttpClient);

            $updateData = ['email' => 'updated@example.com', 'status' => 'active'];
            $response = $client->updateAccount('acc_123', $updateData);

            expect($response)->toBeInstanceOf(AccountResponse::class);
        });

        it('passes update data correctly', function (): void {
            $updateData = [
                'email' => 'newemail@test.com',
                'phone' => '+1234567890',
                'metadata' => ['updated_by' => 'admin'],
            ];

            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('put')
                ->once()
                ->withArgs(fn($url, $data) => str_contains($url, '/account/update_test_456') &&
                           $data === $updateData)
                ->andReturn(MockFactory::createAccountResponseFromFixture([
                    'account' => ['id' => 'update_test_456', 'email' => 'newemail@test.com'],
                ]));

            $client = new AccountClient('test_key', httpClient: $mockHttpClient);
            $response = $client->updateAccount('update_test_456', $updateData);

            expect($response)->toBeInstanceOf(AccountResponse::class);
        });

        it('handles empty update data', function (): void {
            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('put')
                ->once()
                ->withArgs(fn($url, $data) => str_contains($url, '/account/empty_update_test') &&
                           [] === $data)
                ->andReturn([
                    'account_id' => 'empty_update_test',
                    'status' => 'unchanged',
                ]);

            $client = new AccountClient('test_key', httpClient: $mockHttpClient);
            $response = $client->updateAccount('empty_update_test', []);

            expect($response)->toBeInstanceOf(AccountResponse::class);
        });

        it('throws VerisoulConnectionException on connection failure', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(AccountClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->updateAccount('acc_123', ['status' => 'inactive']))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(AccountClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->updateAccount('acc_123', ['status' => 'invalid']))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('deleteAccount method', function (): void {
        it('creates DeleteAccountResponse object', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'delete' => [
                    'account_id' => 'acc_123',
                    'deleted' => true,
                    'deleted_at' => '2024-01-15T12:00:00Z',
                ],
            ]);

            $client = new AccountClient('test_api_key', httpClient: $mockHttpClient);

            $response = $client->deleteAccount('acc_123');

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);
        });

        it('constructs correct DELETE URL', function (): void {
            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('delete')
                ->once()
                ->withArgs(fn($url, $data) => str_contains($url, '/account/delete_test_999'))
                ->andReturn([
                    'account_id' => 'delete_test_999',
                    'deleted' => true,
                ]);

            $client = new AccountClient('test_key', httpClient: $mockHttpClient);
            $response = $client->deleteAccount('delete_test_999');

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);
        });

        it('throws VerisoulConnectionException on connection failure', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(AccountClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->deleteAccount('acc_123'))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(AccountClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->deleteAccount('acc_123'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('environment integration', function (): void {
        it('uses sandbox URLs in sandbox environment', function (): void {
            $client = new AccountClient('sandbox_key', VerisoulEnvironment::Sandbox);

            expect($client->getBaseUrl())->toBe('https://api.sandbox.verisoul.ai');
        });

        it('uses production URLs in production environment', function (): void {
            $client = new AccountClient('prod_key', VerisoulEnvironment::Production);

            expect($client->getBaseUrl())->toBe('https://api.prod.verisoul.ai');
        });

        it('makes requests to correct environment', function (): void {
            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('get')
                ->once()
                ->withArgs(fn($url, $params) => str_contains($url, 'https://api.prod.verisoul.ai'))
                ->andReturn(['account_id' => 'prod_account', 'status' => 'active']);

            $prodClient = new AccountClient(
                'prod_key',
                VerisoulEnvironment::Production,
                httpClient: $mockHttpClient,
            );

            $prodClient->getAccount('prod_account');
        });
    });

    describe('response object handling', function (): void {
        it('correctly creates AccountResponse from getAccount', function (): void {
            $apiResponse = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'response_test_123',
                    'email' => 'response@test.com',
                ],
            ]);

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['get' => $apiResponse]);
            $client = new AccountClient('test_key', httpClient: $mockHttpClient);

            $response = $client->getAccount('response_test_123');

            expect($response)->toBeInstanceOf(AccountResponse::class);
        });

        it('correctly creates AccountSessionsResponse from getAccountSessions', function (): void {
            $apiResponse = [
                'account_id' => 'sessions_test_456',
                'sessions' => [
                    [
                        'session_id' => 'sess_1',
                        'status' => 'completed',
                        'risk_score' => 0.2,
                        'created_at' => '2024-01-15T09:00:00Z',
                    ],
                    [
                        'session_id' => 'sess_2',
                        'status' => 'pending',
                        'risk_score' => 0.5,
                        'created_at' => '2024-01-15T10:00:00Z',
                    ],
                ],
                'total_sessions' => 2,
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['get' => $apiResponse]);
            $client = new AccountClient('test_key', httpClient: $mockHttpClient);

            $response = $client->getAccountSessions('sessions_test_456');

            expect($response)->toBeInstanceOf(AccountSessionsResponse::class);
        });

        it('correctly creates LinkedAccountsResponse from getLinkedAccounts', function (): void {
            $apiResponse = [
                'account_id' => 'linked_test_789',
                'linked_accounts' => [
                    [
                        'account_id' => 'linked_1',
                        'relationship_type' => 'family',
                        'confidence_score' => 0.95,
                        'linked_at' => '2024-01-10T08:00:00Z',
                    ],
                    [
                        'account_id' => 'linked_2',
                        'relationship_type' => 'shared_device',
                        'confidence_score' => 0.87,
                        'linked_at' => '2024-01-12T14:30:00Z',
                    ],
                ],
                'total_linked' => 2,
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['get' => $apiResponse]);
            $client = new AccountClient('test_key', httpClient: $mockHttpClient);

            $response = $client->getLinkedAccounts('linked_test_789');

            expect($response)->toBeInstanceOf(LinkedAccountsResponse::class);
        });

        it('correctly creates DeleteAccountResponse from deleteAccount', function (): void {
            $apiResponse = [
                'account_id' => 'delete_test_321',
                'deleted' => true,
                'deleted_at' => '2024-01-15T15:00:00Z',
                'cleanup_status' => 'completed',
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['delete' => $apiResponse]);
            $client = new AccountClient('test_key', httpClient: $mockHttpClient);

            $response = $client->deleteAccount('delete_test_321');

            expect($response)->toBeInstanceOf(DeleteAccountResponse::class);
        });
    });

    describe('real-world usage scenarios', function (): void {
        it('handles complete account management workflow', function (): void {
            // First get account
            $getResponse = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'workflow_acc_456',
                    'email' => 'workflow@test.com',
                ],
            ]);

            // Then update account
            $updateResponse = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'workflow_acc_456',
                    'email' => 'updated_workflow@test.com',
                ],
            ]);

            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => $getResponse,
                'put' => $updateResponse,
            ]);

            $client = new AccountClient('test_key', httpClient: $mockHttpClient);

            $getResult = $client->getAccount('workflow_acc_456');
            $updateResult = $client->updateAccount('workflow_acc_456', [
                'email' => 'updated_workflow@test.com',
                'status' => 'verified',
            ]);

            expect($getResult)->toBeInstanceOf(AccountResponse::class)
                ->and($updateResult)->toBeInstanceOf(AccountResponse::class);
        });

        it('handles account investigation workflow', function (): void {
            // Get account details
            $accountResponse = MockFactory::createAccountResponseFromFixture([
                'account' => [
                    'id' => 'investigate_acc_789',
                    'email' => 'suspicious@test.com',
                ],
            ]);

            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => $accountResponse,
            ]);

            $client = new AccountClient('test_key', httpClient: $mockHttpClient);

            $accountResult = $client->getAccount('investigate_acc_789');

            expect($accountResult)->toBeInstanceOf(AccountResponse::class);
        });

        it('handles account cleanup workflow', function (): void {
            // Get account sessions first
            $sessionsResponse = [
                'account_id' => 'cleanup_acc_321',
                'sessions' => [
                    ['session_id' => 'old_sess_1', 'status' => 'expired'],
                    ['session_id' => 'old_sess_2', 'status' => 'expired'],
                ],
            ];

            // Then delete the account
            $deleteResponse = [
                'account_id' => 'cleanup_acc_321',
                'deleted' => true,
                'deleted_at' => '2024-01-15T16:00:00Z',
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => $sessionsResponse,
                'delete' => $deleteResponse,
            ]);

            $client = new AccountClient('test_key', httpClient: $mockHttpClient);

            $sessionsResult = $client->getAccountSessions('cleanup_acc_321');
            $deleteResult = $client->deleteAccount('cleanup_acc_321');

            expect($sessionsResult)->toBeInstanceOf(AccountSessionsResponse::class)
                ->and($deleteResult)->toBeInstanceOf(DeleteAccountResponse::class);
        });
    });

    describe('parameter validation scenarios', function (): void {
        it('handles various account ID formats in all methods', function (): void {
            $accountIds = [
                'uuid_12345678-1234-1234-1234-123456789012',
                'simple_id_123',
                'id-with-hyphens-456',
                'IdWithMixedCase789',
            ];

            $mockResponse = MockFactory::createAccountResponseData(['account' => ['id' => 'test']]);

            foreach ($accountIds as $accountId) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'get' => $mockResponse,
                    'put' => $mockResponse,
                    'delete' => ['account_id' => $accountId, 'deleted' => true],
                ]);

                $client = new AccountClient('test_key', httpClient: $mockHttpClient);

                // Test all methods with the account ID
                $getResult = $client->getAccount($accountId);
                $sessionsResult = $client->getAccountSessions($accountId);
                $updateResult = $client->updateAccount($accountId, ['status' => 'updated']);
                $deleteResult = $client->deleteAccount($accountId);

                expect($getResult)->toBeInstanceOf(AccountResponse::class)
                    ->and($sessionsResult)->toBeInstanceOf(AccountSessionsResponse::class)
                    ->and($updateResult)->toBeInstanceOf(AccountResponse::class)
                    ->and($deleteResult)->toBeInstanceOf(DeleteAccountResponse::class);
            }
        });

        it('handles complex update data structures', function (): void {
            $complexUpdateData = [
                'email' => 'complex@example.com',
                'phone' => '+1234567890',
                'profile' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'date_of_birth' => '1990-01-01',
                    'address' => [
                        'street' => '123 Main St',
                        'city' => 'San Francisco',
                        'state' => 'CA',
                        'postal_code' => '94105',
                        'country' => 'US',
                    ],
                ],
                'preferences' => [
                    'notifications' => true,
                    'marketing' => false,
                    'data_retention' => '5_years',
                ],
                'metadata' => [
                    'source' => 'mobile_app',
                    'referrer' => 'organic',
                    'utm_campaign' => 'winter_2024',
                ],
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'put' => ['account_id' => 'complex_test', 'status' => 'updated'],
            ]);

            $client = new AccountClient('test_key', httpClient: $mockHttpClient);
            $result = $client->updateAccount('complex_test', $complexUpdateData);

            expect($result)->toBeInstanceOf(AccountResponse::class);
        });
    });
});
