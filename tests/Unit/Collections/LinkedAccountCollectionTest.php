<?php

use Ninja\Verisoul\Collections\LinkedAccountCollection;
use Ninja\Verisoul\DTO\LinkedAccount;

describe('LinkedAccountCollection', function (): void {
    describe('creation', function (): void {
        it('creates from array of linked account data', function (): void {
            $linkedAccountData = [
                [
                    'account_id' => 'linked_123',
                    'score' => 0.85,
                    'email' => 'user1@example.com',
                    'match_type' => ['email', 'device'],
                    'lists' => ['list1', 'list2'],
                    'metadata' => ['source' => 'api'],
                ],
                [
                    'account_id' => 'linked_456',
                    'score' => 0.92,
                    'email' => 'user2@example.com',
                    'match_type' => ['email'],
                    'lists' => ['list3'],
                    'metadata' => ['source' => 'import'],
                ],
            ];

            $collection = LinkedAccountCollection::from($linkedAccountData);

            expect($collection)->toHaveCount(2)
                ->and($collection->first())->toBeInstanceOf(LinkedAccount::class);
        });

        it('creates empty collection from empty array', function (): void {
            $collection = LinkedAccountCollection::from([]);

            expect($collection)->toHaveCount(0)
                ->and($collection->isEmpty())->toBeTrue();
        });
    });

    describe('array conversion', function (): void {
        beforeEach(function (): void {
            $this->linkedAccountData = [
                [
                    'account_id' => 'linked_123',
                    'score' => 0.85,
                    'email' => 'user1@example.com',
                    'match_type' => ['email', 'device'],
                    'lists' => ['list1', 'list2'],
                    'metadata' => ['source' => 'api'],
                ],
                [
                    'account_id' => 'linked_456',
                    'score' => 0.92,
                    'email' => 'user2@example.com',
                    'match_type' => ['email'],
                    'lists' => ['list3'],
                    'metadata' => ['source' => 'import'],
                ],
            ];

            $this->collection = LinkedAccountCollection::from($this->linkedAccountData);
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
            $this->linkedAccountData = [
                [
                    'account_id' => 'linked_123',
                    'score' => 0.85,
                    'email' => 'user1@example.com',
                    'match_type' => ['email', 'device'],
                    'lists' => ['list1', 'list2'],
                    'metadata' => ['source' => 'api'],
                ],
                [
                    'account_id' => 'linked_456',
                    'score' => 0.92,
                    'email' => 'user2@example.com',
                    'match_type' => ['email'],
                    'lists' => ['list3'],
                    'metadata' => ['source' => 'import'],
                ],
                [
                    'account_id' => 'linked_789',
                    'score' => 0.78,
                    'email' => 'user3@example.com',
                    'match_type' => ['device'],
                    'lists' => ['list1', 'list4'],
                    'metadata' => ['source' => 'api'],
                ],
            ];

            $this->collection = LinkedAccountCollection::from($this->linkedAccountData);
        });

        it('supports collection operations', function (): void {
            // Test filtering by score
            $highScore = $this->collection->filter(fn(LinkedAccount $account) => $account->score > 0.8);

            expect($highScore)->toHaveCount(2);

            // Test filtering by email domain
            $exampleUsers = $this->collection->filter(fn(LinkedAccount $account) => str_contains($account->email, '@example.com'));

            expect($exampleUsers)->toHaveCount(3);
        });

        it('supports mapping operations', function (): void {
            $accountIds = $this->collection->map(fn(LinkedAccount $account) => $account->accountId);

            expect($accountIds->toArray())->toContain('linked_123')
                ->and($accountIds->toArray())->toContain('linked_456')
                ->and($accountIds->toArray())->toContain('linked_789');

            $emails = $this->collection->map(fn(LinkedAccount $account) => $account->email);

            expect($emails->toArray())->toContain('user1@example.com')
                ->and($emails->toArray())->toContain('user2@example.com');
        });

        it('supports searching', function (): void {
            $found = $this->collection->first(fn(LinkedAccount $account) => 'linked_456' === $account->accountId);

            expect($found)->toBeInstanceOf(LinkedAccount::class)
                ->and($found->accountId)->toBe('linked_456')
                ->and($found->email)->toBe('user2@example.com');
        });

        it('supports sorting by score', function (): void {
            $sortedByScore = $this->collection->sortByDesc(fn(LinkedAccount $account) => $account->score);

            $first = $sortedByScore->first();
            $last = $sortedByScore->last();

            expect($first->score)->toBeGreaterThan($last->score);
        });

        it('supports grouping by source', function (): void {
            $groupedBySource = $this->collection->groupBy(fn(LinkedAccount $account) => $account->metadata['source'] ?? 'unknown');

            expect($groupedBySource)->toHaveKey('api')
                ->and($groupedBySource)->toHaveKey('import');
        });

        it('can calculate average score', function (): void {
            $averageScore = $this->collection->avg(fn(LinkedAccount $account) => $account->score);

            expect($averageScore)->toBeFloat()
                ->and($averageScore)->toBeGreaterThan(0.8);
        });
    });

    describe('empty collection', function (): void {
        beforeEach(function (): void {
            $this->emptyCollection = LinkedAccountCollection::from([]);
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

        it('handles filtering on empty collection', function (): void {
            $filtered = $this->emptyCollection->filter(fn(LinkedAccount $account) => $account->score > 0.5);

            expect($filtered)->toHaveCount(0);
        });
    });

    describe('specialized operations', function (): void {
        beforeEach(function (): void {
            $this->linkedAccountData = [
                [
                    'account_id' => 'linked_123',
                    'score' => 0.95,
                    'email' => 'high@example.com',
                    'match_type' => ['email', 'device'],
                    'lists' => ['list1', 'list2'],
                    'metadata' => ['priority' => 'high'],
                ],
                [
                    'account_id' => 'linked_456',
                    'score' => 0.85,
                    'email' => 'medium@example.com',
                    'match_type' => ['email'],
                    'lists' => ['list3'],
                    'metadata' => ['priority' => 'medium'],
                ],
                [
                    'account_id' => 'linked_789',
                    'score' => 0.65,
                    'email' => 'low@example.com',
                    'match_type' => ['device'],
                    'lists' => ['list1'],
                    'metadata' => ['priority' => 'low'],
                ],
            ];

            $this->collection = LinkedAccountCollection::from($this->linkedAccountData);
        });

        it('can find high score accounts', function (): void {
            $highScore = $this->collection->filter(fn(LinkedAccount $account) => $account->score >= 0.9);

            expect($highScore)->toHaveCount(1);
        });

        it('can get unique match types', function (): void {
            $matchTypes = collect();

            foreach ($this->collection as $account) {
                foreach ($account->matchType as $type) {
                    if ( ! $matchTypes->contains($type)) {
                        $matchTypes->add($type);
                    }
                }
            }

            expect($matchTypes->toArray())->toContain('device')
                ->and($matchTypes->toArray())->toContain('email');
        });

        it('can find highest scoring account', function (): void {
            $highest = $this->collection->sortByDesc(fn(LinkedAccount $account) => $account->score)->first();

            expect($highest->accountId)->toBe('linked_123');
        });
    });

    describe('error handling and validation', function (): void {
        it('throws exception for non-iterable data', function (): void {
            expect(fn() => LinkedAccountCollection::from('not iterable'))
                ->toThrow(Ninja\Granite\Exceptions\ReflectionException::class, 'Expected iterable data');
        });

        it('throws exception for invalid LinkedAccount in array conversion', function (): void {
            $collection = new LinkedAccountCollection();
            $collection->push('not a LinkedAccount object');

            expect(fn() => $collection->array())
                ->toThrow(InvalidArgumentException::class, 'Expected LinkedAccount instance');
        });

        it('handles edge case data types', function (): void {
            $edgeCaseData = [
                [
                    'account_id' => '',
                    'score' => 0,
                    'email' => '',
                    'match_type' => [],
                    'lists' => [],
                    'metadata' => [],
                ],
            ];

            $collection = LinkedAccountCollection::from($edgeCaseData);
            expect($collection->count())->toBe(1);

            $arrayResult = $collection->array();
            expect($arrayResult)->toBeArray();
        });
    });

    describe('JSON and serialization edge cases', function (): void {
        it('produces valid JSON for complex data', function (): void {
            $complexData = [
                [
                    'account_id' => 'complex_123',
                    'score' => 0.876543210,
                    'email' => 'complex+test@example.com',
                    'match_type' => ['email', 'device', 'phone'],
                    'lists' => ['list1', 'list2', 'list3'],
                    'metadata' => [
                        'created_at' => '2023-01-01T00:00:00Z',
                        'tags' => ['verified', 'premium', 'api'],
                        'nested' => ['deep' => ['value' => true]],
                    ],
                ],
            ];

            $collection = LinkedAccountCollection::from($complexData);
            $json = $collection->json();

            expect($json)->toBeString();

            $decoded = json_decode($json, true);
            expect($decoded)->toBeArray()
                ->and($decoded[0]['account_id'])->toBe('complex_123')
                ->and($decoded[0]['score'])->toBe(0.876543210);
        });

        it('maintains data integrity through round-trip conversion', function (): void {
            $originalData = [
                [
                    'account_id' => 'integrity_test',
                    'score' => 0.123456789,
                    'email' => 'integrity@test.com',
                    'match_type' => ['email'],
                    'lists' => ['test'],
                    'metadata' => ['key' => 'value'],
                ],
            ];

            $collection = LinkedAccountCollection::from($originalData);
            $jsonString = $collection->json();
            $arrayFromJson = json_decode($jsonString, true);
            $newCollection = LinkedAccountCollection::from($arrayFromJson);

            expect($newCollection->count())->toBe(1);
            expect($newCollection->first()->accountId)->toBe('integrity_test');
        });
    });

    describe('GraniteObject implementation', function (): void {
        it('implements GraniteObject interface', function (): void {
            $collection = new LinkedAccountCollection();

            expect($collection)->toBeInstanceOf(Ninja\Granite\Contracts\GraniteObject::class);
        });

        it('has required methods', function (): void {
            $collection = new LinkedAccountCollection();

            expect(method_exists($collection, 'from'))->toBeTrue()
                ->and(method_exists($collection, 'array'))->toBeTrue()
                ->and(method_exists($collection, 'json'))->toBeTrue();
        });

        it('static from method works correctly', function (): void {
            $data = [
                [
                    'account_id' => 'static_test',
                    'score' => 0.8,
                    'email' => 'static@test.com',
                    'match_type' => ['email'],
                    'lists' => [],
                    'metadata' => [],
                ],
            ];

            $collection = LinkedAccountCollection::from($data);
            expect($collection)->toBeInstanceOf(LinkedAccountCollection::class);
            expect($collection->count())->toBe(1);
        });
    });
});
