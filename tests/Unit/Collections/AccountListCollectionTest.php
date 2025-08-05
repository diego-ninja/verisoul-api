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

    describe('error handling and validation', function (): void {
        it('throws exception for non-iterable data', function (): void {
            expect(fn() => AccountListCollection::from('not iterable'))
                ->toThrow(\Ninja\Granite\Exceptions\ReflectionException::class, 'Expected iterable data');
        });

        it('throws exception for invalid AccountList in array conversion', function (): void {
            $collection = new AccountListCollection();
            $collection->push('not an AccountList object');

            expect(fn() => $collection->array())
                ->toThrow(\InvalidArgumentException::class, 'Expected AccountList instance');
        });

        it('handles malformed account list data', function (): void {
            $edgeCaseData = [
                [
                    'request_id' => '',
                    'name' => '',
                    'description' => '',
                    'accounts' => []
                ]
            ];

            $collection = AccountListCollection::from($edgeCaseData);
            expect($collection->count())->toBe(1);
            
            $arrayResult = $collection->array();
            expect($arrayResult)->toBeArray();
        });
    });

    describe('JSON serialization and edge cases', function (): void {
        it('produces valid JSON for complex data', function (): void {
            $complexData = [
                [
                    'request_id' => 'complex_req_123',
                    'name' => 'Complex List with Special Characters: áéíóú @#$%',
                    'description' => 'A list with "quotes" and \backslashes and unicode: 中文',
                    'accounts' => ['acc_1', 'acc_2', 'acc_3']
                ]
            ];

            $collection = AccountListCollection::from($complexData);
            $json = $collection->json();
            
            expect($json)->toBeString();
            
            $decoded = json_decode($json, true);
            expect($decoded)->toBeArray()
                ->and($decoded[0]['request_id'])->toBe('complex_req_123');
        });

        it('maintains data integrity through round-trip conversion', function (): void {
            $originalData = [
                [
                    'request_id' => 'integrity_test',
                    'name' => 'Integrity Test List',
                    'description' => 'Testing data integrity',
                    'accounts' => ['test_acc_1', 'test_acc_2']
                ]
            ];

            $collection = AccountListCollection::from($originalData);
            $jsonString = $collection->json();
            $arrayFromJson = json_decode($jsonString, true);
            $newCollection = AccountListCollection::from($arrayFromJson);

            expect($newCollection->count())->toBe(1);
            expect($newCollection->first()->requestId)->toBe('integrity_test');
            expect($newCollection->first()->name)->toBe('Integrity Test List');
        });
    });

    describe('Collection inheritance and advanced operations', function (): void {
        it('inherits from Illuminate Collection', function (): void {
            $collection = AccountListCollection::from([]);
            expect($collection)->toBeInstanceOf(\Illuminate\Support\Collection::class);
        });

        it('supports advanced Collection methods', function (): void {
            $accountData = [
                [
                    'request_id' => 'adv_123',
                    'name' => 'Advanced Test 1',
                    'description' => 'First advanced test',
                    'accounts' => ['acc_1', 'acc_2']
                ],
                [
                    'request_id' => 'adv_456',
                    'name' => 'Advanced Test 2',
                    'description' => 'Second advanced test',
                    'accounts' => ['acc_3', 'acc_4', 'acc_5']
                ]
            ];

            $collection = AccountListCollection::from($accountData);

            // Test various Collection methods
            expect($collection->count())->toBe(2);
            expect($collection->first())->toBeInstanceOf(AccountList::class);
            expect($collection->last())->toBeInstanceOf(AccountList::class);
            expect($collection->isEmpty())->toBeFalse();
            
            // Test pluck-like operation
            $names = $collection->map(fn($list) => $list->name);
            expect($names->toArray())->toContain('Advanced Test 1');
            
            // Test filtering with complex conditions
            $filtered = $collection->filter(function ($list) {
                return count($list->accounts) > 2 && str_contains($list->name, 'Advanced');
            });
            expect($filtered->count())->toBe(1);
        });

        it('supports method chaining', function (): void {
            $accountData = [];
            for ($i = 1; $i <= 5; $i++) {
                $accountData[] = [
                    'request_id' => "chain_req_{$i}",
                    'name' => "Chain Test {$i}",
                    'description' => "Chain test description {$i}",
                    'accounts' => array_fill(0, $i, "acc_{$i}")
                ];
            }

            $collection = AccountListCollection::from($accountData);

            $result = $collection
                ->filter(fn($list) => count($list->accounts) >= 3)
                ->sortByDesc(fn($list) => count($list->accounts))
                ->take(2);

            expect($result->count())->toBe(2);
            expect(count($result->first()->accounts))->toBeGreaterThanOrEqual(3);
        });
    });

    describe('performance and edge cases', function (): void {
        it('handles large collections efficiently', function (): void {
            $largeData = [];
            for ($i = 0; $i < 100; $i++) {
                $largeData[] = [
                    'request_id' => "large_req_{$i}",
                    'name' => "Large Test List {$i}",
                    'description' => "Generated test list {$i}",
                    'accounts' => array_fill(0, rand(1, 10), "acc_{$i}")
                ];
            }

            $startTime = microtime(true);
            $collection = AccountListCollection::from($largeData);
            $endTime = microtime(true);

            expect($collection->count())->toBe(100);
            expect($endTime - $startTime)->toBeLessThan(1.0); // Should complete in under 1 second
        });

        it('handles empty and minimal data appropriately', function (): void {
            $minimalData = [
                [
                    'request_id' => '',
                    'name' => '',
                    'description' => '',
                    'accounts' => []
                ]
            ];

            $collection = AccountListCollection::from($minimalData);
            expect($collection->count())->toBe(1);
            
            $arrayResult = $collection->array();
            expect($arrayResult)->toBeArray();
            expect($arrayResult)->toHaveCount(1);
            
            $jsonResult = $collection->json();
            expect($jsonResult)->toBeString();
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

        it('static from method creates proper instance', function (): void {
            $data = [
                [
                    'request_id' => 'static_test_req',
                    'name' => 'Static Test',
                    'description' => 'Test static from method',
                    'accounts' => ['static_acc_1']
                ]
            ];

            $collection = AccountListCollection::from($data);
            expect($collection)->toBeInstanceOf(AccountListCollection::class);
            expect($collection->count())->toBe(1);
            expect($collection->first()->requestId)->toBe('static_test_req');
        });
    });
});
