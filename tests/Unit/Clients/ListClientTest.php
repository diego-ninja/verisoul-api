<?php

use Ninja\Verisoul\Clients\ListClient;
use Ninja\Verisoul\Collections\AccountListCollection;
use Ninja\Verisoul\Contracts\HttpClientInterface;
use Ninja\Verisoul\DTO\AccountList;
use Ninja\Verisoul\Enums\VerisoulEnvironment;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Ninja\Verisoul\Responses\ListOperationResponse;
use Ninja\Verisoul\Tests\Helpers\MockFactory;

describe('ListClient', function (): void {
    describe('construction', function (): void {
        it('can be created with default parameters', function (): void {
            $client = new ListClient('test_api_key');

            expect($client)->toBeInstanceOf(ListClient::class)
                ->and($client->getEnvironment())->toBe(VerisoulEnvironment::Sandbox);
        });

        it('can be created with custom environment', function (): void {
            $client = new ListClient('prod_key', VerisoulEnvironment::Production);

            expect($client->getEnvironment())->toBe(VerisoulEnvironment::Production);
        });

        it('inherits from Client base class', function (): void {
            $client = new ListClient('test_api_key');

            expect($client)->toBeInstanceOf(Ninja\Verisoul\Clients\Client::class);
        });

        it('implements ListInterface', function (): void {
            $client = new ListClient('test_api_key');

            expect($client)->toBeInstanceOf(Ninja\Verisoul\Contracts\ListInterface::class);
        });
    });

    describe('createList method', function (): void {
        it('creates ListOperationResponse object', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'post' => [
                    'list_name' => 'test_list',
                    'description' => 'Test list description',
                    'created' => true,
                    'created_at' => '2024-01-15T12:00:00Z',
                ],
            ]);

            $client = new ListClient('test_api_key', httpClient: $mockHttpClient);

            $response = $client->createList('test_list', 'Test list description');

            expect($response)->toBeInstanceOf(ListOperationResponse::class);
        });

        it('passes list name and description correctly', function (): void {
            $listName = 'fraud_suspects';
            $description = 'List of accounts flagged for fraud investigation';

            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('post')
                ->once()
                ->withArgs(fn($url, $data) => str_contains($url, "/list/{$listName}") &&
                           isset($data['list_description']) &&
                           $data['list_description'] === $description)
                ->andReturn([
                    'list_name' => $listName,
                    'description' => $description,
                    'created' => true,
                ]);

            $client = new ListClient('test_key', httpClient: $mockHttpClient);
            $response = $client->createList($listName, $description);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);
        });

        it('handles various list names and descriptions', function (): void {
            $listScenarios = [
                ['name' => 'whitelist', 'desc' => 'Trusted accounts'],
                ['name' => 'blacklist', 'desc' => 'Blocked accounts'],
                ['name' => 'vip_customers', 'desc' => 'VIP customer accounts'],
                ['name' => 'test_accounts', 'desc' => 'Internal testing accounts'],
                ['name' => 'high_risk_users', 'desc' => 'High risk score users'],
            ];

            foreach ($listScenarios as $scenario) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'post' => [
                        'list_name' => $scenario['name'],
                        'description' => $scenario['desc'],
                        'created' => true,
                    ],
                ]);

                $client = new ListClient('test_key', httpClient: $mockHttpClient);
                $response = $client->createList($scenario['name'], $scenario['desc']);

                expect($response)->toBeInstanceOf(ListOperationResponse::class);
            }
        });

        it('throws VerisoulConnectionException on connection failure', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(ListClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->createList('test_list', 'description'))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(ListClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->createList('duplicate_list', 'description'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('getAllLists method', function (): void {
        it('creates AccountListCollection object', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => [
                    'lists' => [
                        [
                            'name' => 'whitelist',
                            'description' => 'Trusted accounts',
                            'accounts' => ['acc_1', 'acc_2'],
                        ],
                        [
                            'name' => 'blacklist',
                            'description' => 'Blocked accounts',
                            'accounts' => ['acc_3'],
                        ],
                    ],
                ],
            ]);

            $client = new ListClient('test_api_key', httpClient: $mockHttpClient);

            $response = $client->getAllLists();

            expect($response)->toBeInstanceOf(AccountListCollection::class);
        });

        it('handles empty lists response', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => ['lists' => []],
            ]);

            $client = new ListClient('test_api_key', httpClient: $mockHttpClient);

            $response = $client->getAllLists();

            expect($response)->toBeInstanceOf(AccountListCollection::class)
                ->and($response->count())->toBe(0);
        });

        it('handles multiple lists with various account counts', function (): void {
            $listsData = [
                'lists' => [
                    [
                        'name' => 'small_list',
                        'description' => 'Small list',
                        'accounts' => ['acc_1'],
                    ],
                    [
                        'name' => 'medium_list',
                        'description' => 'Medium list',
                        'accounts' => ['acc_2', 'acc_3', 'acc_4'],
                    ],
                    [
                        'name' => 'large_list',
                        'description' => 'Large list',
                        'accounts' => array_map(fn($i) => "acc_{$i}", range(5, 15)),
                    ],
                    [
                        'name' => 'empty_list',
                        'description' => 'Empty list',
                        'accounts' => [],
                    ],
                ],
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient(['get' => $listsData]);
            $client = new ListClient('test_key', httpClient: $mockHttpClient);

            $response = $client->getAllLists();

            expect($response)->toBeInstanceOf(AccountListCollection::class)
                ->and($response->count())->toBe(4);
        });

        it('throws VerisoulConnectionException on connection failure', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(ListClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->getAllLists())
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(ListClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->getAllLists())
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('getList method', function (): void {
        it('creates AccountList object', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => [
                    'name' => 'test_list',
                    'description' => 'Test list',
                    'accounts' => ['acc_1', 'acc_2', 'acc_3'],
                    'created_at' => '2024-01-15T10:00:00Z',
                ],
            ]);

            $client = new ListClient('test_api_key', httpClient: $mockHttpClient);

            $response = $client->getList('test_list');

            expect($response)->toBeInstanceOf(AccountList::class);
        });

        it('constructs correct URL with list name', function (): void {
            $listName = 'specific_test_list';

            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('get')
                ->once()
                ->withArgs(fn($url, $params) => str_contains($url, "/list/{$listName}"))
                ->andReturn([
                    'name' => $listName,
                    'description' => 'Specific test list',
                    'accounts' => [],
                ]);

            $client = new ListClient('test_key', httpClient: $mockHttpClient);
            $response = $client->getList($listName);

            expect($response)->toBeInstanceOf(AccountList::class);
        });

        it('handles various list names', function (): void {
            $listNames = [
                'whitelist',
                'blacklist',
                'vip-customers',
                'test_accounts_2024',
                'fraud_suspects_v2',
            ];

            foreach ($listNames as $listName) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'get' => [
                        'name' => $listName,
                        'description' => "Description for {$listName}",
                        'accounts' => ['acc_1', 'acc_2'],
                    ],
                ]);

                $client = new ListClient('test_key', httpClient: $mockHttpClient);
                $response = $client->getList($listName);

                expect($response)->toBeInstanceOf(AccountList::class);
            }
        });

        it('throws VerisoulConnectionException on connection failure', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(ListClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->getList('test_list'))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(ListClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->getList('nonexistent_list'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('addAccountToList method', function (): void {
        it('creates ListOperationResponse object', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'post' => [
                    'list_name' => 'test_list',
                    'account_id' => 'acc_123',
                    'added' => true,
                    'operation' => 'add_account',
                ],
            ]);

            $client = new ListClient('test_api_key', httpClient: $mockHttpClient);

            $response = $client->addAccountToList('test_list', 'acc_123');

            expect($response)->toBeInstanceOf(ListOperationResponse::class);
        });

        it('passes list name and account ID correctly', function (): void {
            $listName = 'fraud_list';
            $accountId = 'suspicious_acc_456';

            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('post')
                ->once()
                ->withArgs(fn($url, $data) => str_contains($url, "/list/{$listName}/account/{$accountId}"))
                ->andReturn([
                    'list_name' => $listName,
                    'account_id' => $accountId,
                    'added' => true,
                ]);

            $client = new ListClient('test_key', httpClient: $mockHttpClient);
            $response = $client->addAccountToList($listName, $accountId);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);
        });

        it('handles bulk account additions', function (): void {
            $listName = 'bulk_test_list';
            $accountIds = ['acc_1', 'acc_2', 'acc_3', 'acc_4', 'acc_5'];

            foreach ($accountIds as $accountId) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'post' => [
                        'list_name' => $listName,
                        'account_id' => $accountId,
                        'added' => true,
                    ],
                ]);

                $client = new ListClient('test_key', httpClient: $mockHttpClient);
                $response = $client->addAccountToList($listName, $accountId);

                expect($response)->toBeInstanceOf(ListOperationResponse::class);
            }
        });

        it('throws VerisoulConnectionException on connection failure', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(ListClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->addAccountToList('test_list', 'acc_123'))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(ListClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->addAccountToList('nonexistent_list', 'acc_123'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('deleteList method', function (): void {
        it('creates ListOperationResponse object', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'delete' => [
                    'list_name' => 'test_list',
                    'deleted' => true,
                    'deleted_at' => '2024-01-15T15:00:00Z',
                ],
            ]);

            $client = new ListClient('test_api_key', httpClient: $mockHttpClient);

            $response = $client->deleteList('test_list');

            expect($response)->toBeInstanceOf(ListOperationResponse::class);
        });

        it('constructs correct DELETE URL', function (): void {
            $listName = 'delete_test_list';

            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('delete')
                ->once()
                ->withArgs(fn($url, $data) => str_contains($url, "/list/{$listName}"))
                ->andReturn([
                    'list_name' => $listName,
                    'deleted' => true,
                ]);

            $client = new ListClient('test_key', httpClient: $mockHttpClient);
            $response = $client->deleteList($listName);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);
        });

        it('handles various list names for deletion', function (): void {
            $listNames = [
                'old_whitelist',
                'deprecated_blacklist',
                'temporary_test_list',
                'migration_list_v1',
            ];

            foreach ($listNames as $listName) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'delete' => [
                        'list_name' => $listName,
                        'deleted' => true,
                    ],
                ]);

                $client = new ListClient('test_key', httpClient: $mockHttpClient);
                $response = $client->deleteList($listName);

                expect($response)->toBeInstanceOf(ListOperationResponse::class);
            }
        });

        it('throws VerisoulConnectionException on connection failure', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(ListClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->deleteList('test_list'))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(ListClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->deleteList('protected_list'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('removeAccountFromList method', function (): void {
        it('creates ListOperationResponse object', function (): void {
            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'delete' => [
                    'list_name' => 'test_list',
                    'account_id' => 'acc_123',
                    'removed' => true,
                    'operation' => 'remove_account',
                ],
            ]);

            $client = new ListClient('test_api_key', httpClient: $mockHttpClient);

            $response = $client->removeAccountFromList('test_list', 'acc_123');

            expect($response)->toBeInstanceOf(ListOperationResponse::class);
        });

        it('constructs correct URL with list name and account ID', function (): void {
            $listName = 'cleanup_list';
            $accountId = 'remove_acc_789';

            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('delete')
                ->once()
                ->withArgs(fn($url, $data) => str_contains($url, "/list/{$listName}/account/{$accountId}"))
                ->andReturn([
                    'list_name' => $listName,
                    'account_id' => $accountId,
                    'removed' => true,
                ]);

            $client = new ListClient('test_key', httpClient: $mockHttpClient);
            $response = $client->removeAccountFromList($listName, $accountId);

            expect($response)->toBeInstanceOf(ListOperationResponse::class);
        });

        it('handles bulk account removals', function (): void {
            $listName = 'bulk_remove_list';
            $accountsToRemove = ['old_acc_1', 'old_acc_2', 'old_acc_3'];

            foreach ($accountsToRemove as $accountId) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'delete' => [
                        'list_name' => $listName,
                        'account_id' => $accountId,
                        'removed' => true,
                    ],
                ]);

                $client = new ListClient('test_key', httpClient: $mockHttpClient);
                $response = $client->removeAccountFromList($listName, $accountId);

                expect($response)->toBeInstanceOf(ListOperationResponse::class);
            }
        });

        it('throws VerisoulConnectionException on connection failure', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulConnectionException::class);
            $client = createTestClient(ListClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->removeAccountFromList('test_list', 'acc_123'))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on API error', function (): void {
            $failingClient = MockFactory::createFailingHttpClient(VerisoulApiException::class);
            $client = createTestClient(ListClient::class, ['httpClient' => $failingClient]);

            expect(fn() => $client->removeAccountFromList('readonly_list', 'acc_123'))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('environment integration', function (): void {
        it('uses sandbox URLs in sandbox environment', function (): void {
            $client = new ListClient('sandbox_key', VerisoulEnvironment::Sandbox);

            expect($client->getBaseUrl())->toBe('https://api.sandbox.verisoul.ai');
        });

        it('uses production URLs in production environment', function (): void {
            $client = new ListClient('prod_key', VerisoulEnvironment::Production);

            expect($client->getBaseUrl())->toBe('https://api.verisoul.ai');
        });

        it('makes requests to correct environment', function (): void {
            $mockHttpClient = Mockery::mock(HttpClientInterface::class);
            $mockHttpClient->shouldReceive('setTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setConnectTimeout')->andReturnSelf();
            $mockHttpClient->shouldReceive('setHeaders')->andReturnSelf();

            $mockHttpClient->shouldReceive('post')
                ->once()
                ->withArgs(fn($url, $data) => str_contains($url, 'https://api.verisoul.ai'))
                ->andReturn(['list_name' => 'prod_list', 'created' => true]);

            $prodClient = new ListClient(
                'prod_key',
                VerisoulEnvironment::Production,
                httpClient: $mockHttpClient,
            );

            $prodClient->createList('prod_list', 'Production list');
        });
    });

    describe('real-world usage scenarios', function (): void {
        it('handles complete list management workflow', function (): void {
            // Create list
            $createResponse = [
                'list_name' => 'workflow_list',
                'description' => 'Complete workflow test',
                'created' => true,
            ];

            // Add accounts
            $addResponse = [
                'list_name' => 'workflow_list',
                'account_id' => 'acc_workflow_1',
                'added' => true,
            ];

            // Get list
            $getResponse = [
                'name' => 'workflow_list',
                'description' => 'Complete workflow test',
                'accounts' => ['acc_workflow_1'],
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'post' => $createResponse,
                'get' => $getResponse,
            ]);

            $client = new ListClient('test_key', httpClient: $mockHttpClient);

            $createResult = $client->createList('workflow_list', 'Complete workflow test');
            $getResult = $client->getList('workflow_list');

            expect($createResult)->toBeInstanceOf(ListOperationResponse::class)
                ->and($getResult)->toBeInstanceOf(AccountList::class);
        });

        it('handles fraud investigation list workflow', function (): void {
            // Create fraud suspects list
            $fraudListResponse = [
                'list_name' => 'fraud_suspects',
                'description' => 'Accounts under fraud investigation',
                'created' => true,
            ];

            // Add multiple suspects
            $suspectAccounts = ['fraud_acc_1', 'fraud_acc_2', 'fraud_acc_3'];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'post' => $fraudListResponse,
            ]);

            $client = new ListClient('test_key', httpClient: $mockHttpClient);

            $createResult = $client->createList('fraud_suspects', 'Accounts under fraud investigation');

            expect($createResult)->toBeInstanceOf(ListOperationResponse::class);
        });

        it('handles VIP customer list management', function (): void {
            // Get all lists first
            $allListsResponse = [
                'lists' => [
                    [
                        'name' => 'vip_customers',
                        'description' => 'VIP customer accounts',
                        'accounts' => ['vip_1', 'vip_2'],
                    ],
                    [
                        'name' => 'regular_customers',
                        'description' => 'Regular customer accounts',
                        'accounts' => ['reg_1', 'reg_2', 'reg_3'],
                    ],
                ],
            ];

            // Add new VIP customer
            $addVipResponse = [
                'list_name' => 'vip_customers',
                'account_id' => 'new_vip_3',
                'added' => true,
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'get' => $allListsResponse,
                'post' => $addVipResponse,
            ]);

            $client = new ListClient('test_key', httpClient: $mockHttpClient);

            $allLists = $client->getAllLists();
            $addResult = $client->addAccountToList('vip_customers', 'new_vip_3');

            expect($allLists)->toBeInstanceOf(AccountListCollection::class)
                ->and($addResult)->toBeInstanceOf(ListOperationResponse::class);
        });

        it('handles list cleanup and migration workflow', function (): void {
            // Remove accounts from old list
            $removeResponse = [
                'list_name' => 'old_list',
                'account_id' => 'migrate_acc_1',
                'removed' => true,
            ];

            // Add accounts to new list
            $addResponse = [
                'list_name' => 'new_list',
                'account_id' => 'migrate_acc_1',
                'added' => true,
            ];

            // Delete old list
            $deleteResponse = [
                'list_name' => 'old_list',
                'deleted' => true,
            ];

            $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                'delete' => $removeResponse,
                'post' => $addResponse,
                'delete' => $deleteResponse,
            ]);

            $client = new ListClient('test_key', httpClient: $mockHttpClient);

            $removeResult = $client->removeAccountFromList('old_list', 'migrate_acc_1');
            $addResult = $client->addAccountToList('new_list', 'migrate_acc_1');
            $deleteResult = $client->deleteList('old_list');

            expect($removeResult)->toBeInstanceOf(ListOperationResponse::class)
                ->and($addResult)->toBeInstanceOf(ListOperationResponse::class)
                ->and($deleteResult)->toBeInstanceOf(ListOperationResponse::class);
        });
    });

    describe('parameter validation scenarios', function (): void {
        it('handles various list name formats', function (): void {
            $listNames = [
                'simple_list',
                'list-with-hyphens',
                'ListWithCamelCase',
                'list_with_123_numbers',
                'UPPERCASE_LIST',
                'mixed_Case_List_456',
            ];

            foreach ($listNames as $listName) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'post' => ['list_name' => $listName, 'created' => true],
                    'get' => ['name' => $listName, 'accounts' => []],
                    'delete' => ['list_name' => $listName, 'deleted' => true],
                ]);

                $client = new ListClient('test_key', httpClient: $mockHttpClient);

                // Test all methods with the list name
                $createResult = $client->createList($listName, 'Test description');
                $getResult = $client->getList($listName);
                $deleteResult = $client->deleteList($listName);

                expect($createResult)->toBeInstanceOf(ListOperationResponse::class)
                    ->and($getResult)->toBeInstanceOf(AccountList::class)
                    ->and($deleteResult)->toBeInstanceOf(ListOperationResponse::class);
            }
        });

        it('handles various account ID formats', function (): void {
            $accountIds = [
                'simple_account',
                'acc-with-hyphens-123',
                'AccountWithCamelCase',
                'acc_123_456_789',
                'uuid-12345678-1234-1234-1234-123456789012',
                'very_long_account_id_with_many_characters_123456',
            ];

            foreach ($accountIds as $accountId) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'post' => ['account_id' => $accountId, 'added' => true],
                    'delete' => ['account_id' => $accountId, 'removed' => true],
                ]);

                $client = new ListClient('test_key', httpClient: $mockHttpClient);

                $addResult = $client->addAccountToList('test_list', $accountId);
                $removeResult = $client->removeAccountFromList('test_list', $accountId);

                expect($addResult)->toBeInstanceOf(ListOperationResponse::class)
                    ->and($removeResult)->toBeInstanceOf(ListOperationResponse::class);
            }
        });

        it('handles complex list descriptions', function (): void {
            $complexDescriptions = [
                'Simple description',
                'Description with special chars: !@#$%^&*()',
                'Multi-line description\nwith line breaks\nand more text',
                'Description with émojis and ünicøde ñ characters',
                'Very long description that contains multiple sentences and explains the purpose of this list in great detail, including various use cases and scenarios where this list might be applicable in different business contexts.',
                'JSON-like description: {"purpose": "testing", "environment": "staging"}',
                'Description with HTML tags: <strong>Important</strong> list for <em>testing</em>',
                '',  // Empty description
            ];

            foreach ($complexDescriptions as $description) {
                $mockHttpClient = MockFactory::createSuccessfulHttpClient([
                    'post' => [
                        'list_name' => 'complex_desc_test',
                        'description' => $description,
                        'created' => true,
                    ],
                ]);

                $client = new ListClient('test_key', httpClient: $mockHttpClient);
                $result = $client->createList('complex_desc_test', $description);

                expect($result)->toBeInstanceOf(ListOperationResponse::class);
            }
        });
    });
});
