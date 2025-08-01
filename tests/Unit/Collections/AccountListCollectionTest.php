<?php

use Ninja\Verisoul\Collections\AccountListCollection;
use Ninja\Verisoul\DTO\AccountList;

describe('AccountListCollection', function (): void {
    describe('creation', function (): void {
        it('creates from array of account data', function (): void {
            $accountData = [
                [
                    'request_id' => 'req_123',
                    'name' => 'Test Account List 1',
                    'description' => 'First test account list',
                    'accounts' => ['acc_1', 'acc_2'],
                ],
                [
                    'request_id' => 'req_456',
                    'name' => 'Test Account List 2',
                    'description' => 'Second test account list',
                    'accounts' => ['acc_3', 'acc_4', 'acc_5'],
                ],
            ];

            $collection = AccountListCollection::from($accountData);

            expect($collection)->toHaveCount(2)
                ->and($collection->first())->toBeInstanceOf(AccountList::class);
        });

        it('creates empty collection from empty array', function (): void {
            $collection = AccountListCollection::from([]);

            expect($collection)->toHaveCount(0)
                ->and($collection->isEmpty())->toBeTrue();
        });
    });

    describe('array conversion', function (): void {
        beforeEach(function (): void {
            $this->accountData = [
                [
                    'request_id' => 'req_123',
                    'name' => 'Test Account List 1',
                    'description' => 'First test account list',
                    'accounts' => ['acc_1', 'acc_2'],
                ],
                [
                    'request_id' => 'req_456',
                    'name' => 'Test Account List 2',
                    'description' => 'Second test account list',
                    'accounts' => ['acc_3', 'acc_4', 'acc_5'],
                ],
            ];

            $this->collection = AccountListCollection::from($this->accountData);
        });

        it('converts to array', function (): void {
            $array = $this->collection->array();

            expect($array)->toBeArray()
                ->and($array)->toHaveCount(2);
        });

        it('converts to json', function (): void {
            $json = $this->collection->json();

            expect($json)->toBeString();

            $decoded = json_decode($json, true);
            expect($decoded)->toBeArray()
                ->and($decoded)->toHaveCount(2);
        });
    });

    describe('collection behavior', function (): void {
        beforeEach(function (): void {
            $this->accountData = [
                [
                    'request_id' => 'req_123',
                    'name' => 'Small List',
                    'description' => 'A small account list',
                    'accounts' => ['acc_1', 'acc_2'],
                ],
                [
                    'request_id' => 'req_456',
                    'name' => 'Large List',
                    'description' => 'A large account list',
                    'accounts' => ['acc_3', 'acc_4', 'acc_5', 'acc_6', 'acc_7'],
                ],
                [
                    'request_id' => 'req_789',
                    'name' => 'Medium List',
                    'description' => 'A medium account list',
                    'accounts' => ['acc_8', 'acc_9', 'acc_10'],
                ],
            ];

            $this->collection = AccountListCollection::from($this->accountData);
        });

        it('supports collection operations', function (): void {
            // Test filtering by account count
            $largeLists = $this->collection->filter(fn(AccountList $accountList) => count($accountList->accounts) > 3);

            expect($largeLists)->toHaveCount(1);

            // Test mapping
            $requestIds = $this->collection->map(fn(AccountList $accountList) => $accountList->requestId);

            expect($requestIds->toArray())->toContain('req_123')
                ->and($requestIds->toArray())->toContain('req_456')
                ->and($requestIds->toArray())->toContain('req_789');
        });

        it('supports searching', function (): void {
            $found = $this->collection->first(fn(AccountList $accountList) => 'req_456' === $accountList->requestId);

            expect($found)->toBeInstanceOf(AccountList::class)
                ->and($found->requestId)->toBe('req_456')
                ->and($found->name)->toBe('Large List');
        });

        it('supports sorting', function (): void {
            $sortedByAccountCount = $this->collection->sortBy(fn(AccountList $accountList) => count($accountList->accounts));

            $first = $sortedByAccountCount->first();
            $last = $sortedByAccountCount->last();

            expect(count($first->accounts))->toBeLessThan(count($last->accounts));
        });

        it('supports grouping', function (): void {
            $groupedBySize = $this->collection->groupBy(function (AccountList $accountList) {
                $count = count($accountList->accounts);
                if ($count <= 2) {
                    return 'small';
                }
                if ($count <= 3) {
                    return 'medium';
                }
                return 'large';
            });

            expect($groupedBySize)->toHaveKey('small')
                ->and($groupedBySize)->toHaveKey('medium')
                ->and($groupedBySize)->toHaveKey('large');
        });
    });

    describe('empty collection', function (): void {
        beforeEach(function (): void {
            $this->emptyCollection = AccountListCollection::from([]);
        });

        it('handles empty collection operations', function (): void {
            expect($this->emptyCollection->array())->toBeArray()
                ->and($this->emptyCollection->array())->toBeEmpty()
                ->and($this->emptyCollection->isEmpty())->toBeTrue()
                ->and($this->emptyCollection->count())->toBe(0);
        });

        it('returns empty json for empty collection', function (): void {
            $json = $this->emptyCollection->json();
            $decoded = json_decode($json, true);

            expect($decoded)->toBeArray()
                ->and($decoded)->toBeEmpty();
        });
    });

    describe('GraniteObject implementation', function (): void {
        it('implements GraniteObject interface', function (): void {
            $collection = new AccountListCollection();

            expect($collection)->toBeInstanceOf(Ninja\Granite\Contracts\GraniteObject::class);
        });

        it('has required methods', function (): void {
            $collection = new AccountListCollection();

            expect(method_exists($collection, 'from'))->toBeTrue()
                ->and(method_exists($collection, 'array'))->toBeTrue()
                ->and(method_exists($collection, 'json'))->toBeTrue();
        });
    });
});
