<?php

namespace Tests\Unit\Support;

use DateInterval;
use InvalidArgumentException;
use Ninja\Verisoul\Support\InMemoryCache;
use Psr\SimpleCache\CacheInterface;

describe('InMemoryCache', function (): void {
    beforeEach(function (): void {
        $this->cache = new InMemoryCache();
    });

    describe('construction', function (): void {
        it('implements CacheInterface', function (): void {
            expect($this->cache)->toBeInstanceOf(CacheInterface::class);
        });

        it('starts empty', function (): void {
            expect($this->cache->has('any_key'))->toBeFalse();
        });
    });

    describe('basic cache operations', function (): void {
        describe('get method', function (): void {
            it('returns default value for non-existent key', function (): void {
                expect($this->cache->get('nonexistent'))->toBeNull();
                expect($this->cache->get('nonexistent', 'default'))->toBe('default');
                expect($this->cache->get('nonexistent', 42))->toBe(42);
            });

            it('returns stored value', function (): void {
                $this->cache->set('key', 'value');
                expect($this->cache->get('key'))->toBe('value');
            });

            it('handles different data types', function (): void {
                $this->cache->set('string', 'test');
                $this->cache->set('integer', 123);
                $this->cache->set('float', 12.34);
                $this->cache->set('boolean', true);
                $this->cache->set('array', ['a', 'b', 'c']);
                $this->cache->set('object', (object) ['prop' => 'value']);

                expect($this->cache->get('string'))->toBe('test');
                expect($this->cache->get('integer'))->toBe(123);
                expect($this->cache->get('float'))->toBe(12.34);
                expect($this->cache->get('boolean'))->toBe(true);
                expect($this->cache->get('array'))->toBe(['a', 'b', 'c']);
                expect($this->cache->get('object'))->toEqual((object) ['prop' => 'value']);
            });
        });

        describe('set method', function (): void {
            it('returns true on successful set', function (): void {
                expect($this->cache->set('key', 'value'))->toBe(true);
            });

            it('stores value without TTL', function (): void {
                $this->cache->set('key', 'value');
                expect($this->cache->get('key'))->toBe('value');
                expect($this->cache->has('key'))->toBe(true);
            });

            it('overwrites existing value', function (): void {
                $this->cache->set('key', 'old_value');
                $this->cache->set('key', 'new_value');
                expect($this->cache->get('key'))->toBe('new_value');
            });

            it('stores value with integer TTL', function (): void {
                $this->cache->set('key', 'value', 60);
                expect($this->cache->get('key'))->toBe('value');
                expect($this->cache->has('key'))->toBe(true);
            });

            it('stores value with DateInterval TTL', function (): void {
                $ttl = new DateInterval('PT1H'); // 1 hour
                $this->cache->set('key', 'value', $ttl);
                expect($this->cache->get('key'))->toBe('value');
                expect($this->cache->has('key'))->toBe(true);
            });
        });

        describe('delete method', function (): void {
            it('returns true when deleting', function (): void {
                $this->cache->set('key', 'value');
                expect($this->cache->delete('key'))->toBe(true);
            });

            it('removes existing key', function (): void {
                $this->cache->set('key', 'value');
                $this->cache->delete('key');
                expect($this->cache->has('key'))->toBe(false);
                expect($this->cache->get('key'))->toBeNull();
            });

            it('handles non-existent key gracefully', function (): void {
                expect($this->cache->delete('nonexistent'))->toBe(true);
            });

            it('removes key with expiration', function (): void {
                $this->cache->set('key', 'value', 60);
                $this->cache->delete('key');
                expect($this->cache->has('key'))->toBe(false);
            });
        });

        describe('has method', function (): void {
            it('returns false for non-existent key', function (): void {
                expect($this->cache->has('nonexistent'))->toBe(false);
            });

            it('returns true for existing key', function (): void {
                $this->cache->set('key', 'value');
                expect($this->cache->has('key'))->toBe(true);
            });

            it('returns false for expired key', function (): void {
                $this->cache->set('key', 'value', -1); // Already expired
                expect($this->cache->has('key'))->toBe(false);
            });
        });

        describe('clear method', function (): void {
            it('returns true when clearing', function (): void {
                expect($this->cache->clear())->toBe(true);
            });

            it('removes all keys', function (): void {
                $this->cache->set('key1', 'value1');
                $this->cache->set('key2', 'value2');
                $this->cache->set('key3', 'value3', 60);

                $this->cache->clear();

                expect($this->cache->has('key1'))->toBe(false);
                expect($this->cache->has('key2'))->toBe(false);
                expect($this->cache->has('key3'))->toBe(false);
            });

            it('handles empty cache gracefully', function (): void {
                expect($this->cache->clear())->toBe(true);
            });
        });
    });

    describe('multiple operations', function (): void {
        describe('getMultiple method', function (): void {
            it('returns array of values', function (): void {
                $this->cache->set('key1', 'value1');
                $this->cache->set('key2', 'value2');

                $result = $this->cache->getMultiple(['key1', 'key2', 'key3']);

                expect($result)->toBe([
                    'key1' => 'value1',
                    'key2' => 'value2',
                    'key3' => null,
                ]);
            });

            it('uses default value for non-existent keys', function (): void {
                $this->cache->set('existing', 'value');

                $result = $this->cache->getMultiple(['existing', 'missing'], 'default');

                expect($result)->toBe([
                    'existing' => 'value',
                    'missing' => 'default',
                ]);
            });

            it('handles empty keys array', function (): void {
                $result = $this->cache->getMultiple([]);
                expect($result)->toBe([]);
            });
        });

        describe('setMultiple method', function (): void {
            it('returns true on successful set', function (): void {
                $values = ['key1' => 'value1', 'key2' => 'value2'];
                expect($this->cache->setMultiple($values))->toBe(true);
            });

            it('stores multiple values without TTL', function (): void {
                $values = ['key1' => 'value1', 'key2' => 'value2'];
                $this->cache->setMultiple($values);

                expect($this->cache->get('key1'))->toBe('value1');
                expect($this->cache->get('key2'))->toBe('value2');
            });

            it('stores multiple values with TTL', function (): void {
                $values = ['key1' => 'value1', 'key2' => 'value2'];
                $this->cache->setMultiple($values, 60);

                expect($this->cache->get('key1'))->toBe('value1');
                expect($this->cache->get('key2'))->toBe('value2');
                expect($this->cache->has('key1'))->toBe(true);
                expect($this->cache->has('key2'))->toBe(true);
            });

            it('stores multiple values with DateInterval TTL', function (): void {
                $values = ['key1' => 'value1', 'key2' => 'value2'];
                $ttl = new DateInterval('PT30M'); // 30 minutes
                $this->cache->setMultiple($values, $ttl);

                expect($this->cache->get('key1'))->toBe('value1');
                expect($this->cache->get('key2'))->toBe('value2');
            });

            it('throws exception for non-string keys', function (): void {
                $values = [123 => 'value1', 'key2' => 'value2'];

                expect(fn() => $this->cache->setMultiple($values))
                    ->toThrow(InvalidArgumentException::class, 'Cache key must be a string');
            });

            it('handles empty values array', function (): void {
                expect($this->cache->setMultiple([]))->toBe(true);
            });
        });

        describe('deleteMultiple method', function (): void {
            it('returns true when deleting', function (): void {
                $this->cache->set('key1', 'value1');
                $this->cache->set('key2', 'value2');

                expect($this->cache->deleteMultiple(['key1', 'key2']))->toBe(true);
            });

            it('removes multiple keys', function (): void {
                $this->cache->set('key1', 'value1');
                $this->cache->set('key2', 'value2');
                $this->cache->set('key3', 'value3');

                $this->cache->deleteMultiple(['key1', 'key3']);

                expect($this->cache->has('key1'))->toBe(false);
                expect($this->cache->has('key2'))->toBe(true);
                expect($this->cache->has('key3'))->toBe(false);
            });

            it('handles non-existent keys gracefully', function (): void {
                expect($this->cache->deleteMultiple(['nonexistent1', 'nonexistent2']))->toBe(true);
            });

            it('handles empty keys array', function (): void {
                expect($this->cache->deleteMultiple([]))->toBe(true);
            });
        });
    });

    describe('TTL expiration', function (): void {
        it('expires keys with integer TTL', function (): void {
            $this->cache->set('key', 'value', 0); // Expires immediately
            expect($this->cache->has('key'))->toBe(false);
            expect($this->cache->get('key'))->toBeNull();
        });

        it('does not expire keys with future TTL', function (): void {
            $this->cache->set('key', 'value', 3600); // 1 hour
            expect($this->cache->has('key'))->toBe(true);
            expect($this->cache->get('key'))->toBe('value');
        });

        it('expires keys with DateInterval TTL', function (): void {
            $ttl = DateInterval::createFromDateString('-1 second'); // Already expired
            $this->cache->set('key', 'value', $ttl);
            expect($this->cache->has('key'))->toBe(false);
        });

        it('cleans expired keys automatically during operations', function (): void {
            // Set a key that expires immediately
            $this->cache->set('expired_key', 'value', -1);
            $this->cache->set('valid_key', 'value', 3600);

            // Accessing any key should trigger cleanup
            $this->cache->get('valid_key');

            // Expired key should be cleaned up
            expect($this->cache->has('expired_key'))->toBe(false);
            expect($this->cache->has('valid_key'))->toBe(true);
        });

        it('handles TTL of null correctly', function (): void {
            $this->cache->set('key', 'value', null);
            expect($this->cache->has('key'))->toBe(true);
            expect($this->cache->get('key'))->toBe('value');
        });
    });

    describe('edge cases and data integrity', function (): void {
        it('handles null values', function (): void {
            $this->cache->set('null_key', null);
            expect($this->cache->has('null_key'))->toBe(true);
            expect($this->cache->get('null_key'))->toBeNull();
        });

        it('handles empty string values', function (): void {
            $this->cache->set('empty_key', '');
            expect($this->cache->has('empty_key'))->toBe(true);
            expect($this->cache->get('empty_key'))->toBe('');
        });

        it('handles zero values', function (): void {
            $this->cache->set('zero_int', 0);
            $this->cache->set('zero_float', 0.0);
            $this->cache->set('false_bool', false);

            expect($this->cache->has('zero_int'))->toBe(true);
            expect($this->cache->has('zero_float'))->toBe(true);
            expect($this->cache->has('false_bool'))->toBe(true);

            expect($this->cache->get('zero_int'))->toBe(0);
            expect($this->cache->get('zero_float'))->toBe(0.0);
            expect($this->cache->get('false_bool'))->toBe(false);
        });

        it('handles special characters in keys', function (): void {
            $specialKeys = ['key with spaces', 'key-with-dashes', 'key_with_underscores', 'key.with.dots'];

            foreach ($specialKeys as $key) {
                $this->cache->set($key, "value for {$key}");
                expect($this->cache->has($key))->toBe(true);
                expect($this->cache->get($key))->toBe("value for {$key}");
            }
        });

        it('maintains data integrity after multiple operations', function (): void {
            // Set initial data
            $this->cache->set('key1', 'value1');
            $this->cache->set('key2', 'value2', 60);

            // Perform various operations
            $this->cache->delete('key1');
            $this->cache->set('key3', 'value3');

            // Verify final state
            expect($this->cache->has('key1'))->toBe(false);
            expect($this->cache->has('key2'))->toBe(true);
            expect($this->cache->has('key3'))->toBe(true);
            expect($this->cache->get('key2'))->toBe('value2');
            expect($this->cache->get('key3'))->toBe('value3');
        });
    });

    describe('performance considerations', function (): void {
        it('handles large number of entries', function (): void {
            $entries = 1000;

            // Set many entries
            for ($i = 0; $i < $entries; $i++) {
                $this->cache->set("key_{$i}", "value_{$i}");
            }

            // Verify all entries exist
            for ($i = 0; $i < $entries; $i++) {
                expect($this->cache->has("key_{$i}"))->toBe(true);
                expect($this->cache->get("key_{$i}"))->toBe("value_{$i}");
            }

            // Clear all
            $this->cache->clear();

            // Verify all entries are gone
            for ($i = 0; $i < $entries; $i++) {
                expect($this->cache->has("key_{$i}"))->toBe(false);
            }
        });

        it('cleans up expired entries efficiently', function (): void {
            // Set some entries with immediate expiration
            for ($i = 0; $i < 10; $i++) {
                $this->cache->set("expired_{$i}", "value_{$i}", -1);
            }

            // Set some valid entries
            for ($i = 0; $i < 10; $i++) {
                $this->cache->set("valid_{$i}", "value_{$i}", 3600);
            }

            // Trigger cleanup by checking any key
            $this->cache->has('valid_0');

            // Verify expired entries are cleaned up
            for ($i = 0; $i < 10; $i++) {
                expect($this->cache->has("expired_{$i}"))->toBe(false);
                expect($this->cache->has("valid_{$i}"))->toBe(true);
            }
        });
    });

    describe('PSR-16 compliance', function (): void {
        it('implements all required methods', function (): void {
            $methods = [
                'get', 'set', 'delete', 'clear',
                'getMultiple', 'setMultiple', 'deleteMultiple', 'has',
            ];

            foreach ($methods as $method) {
                expect(method_exists($this->cache, $method))->toBe(true);
            }
        });

        it('returns correct types from methods', function (): void {
            // set, delete, clear should return bool
            expect($this->cache->set('key', 'value'))->toBeTrue();
            expect($this->cache->delete('key'))->toBeTrue();
            expect($this->cache->clear())->toBeTrue();

            // has should return bool
            $this->cache->set('key', 'value');
            expect($this->cache->has('key'))->toBeTrue();

            // setMultiple, deleteMultiple should return bool
            expect($this->cache->setMultiple(['key' => 'value']))->toBeTrue();
            expect($this->cache->deleteMultiple(['key']))->toBeTrue();

            // getMultiple should return iterable
            $result = $this->cache->getMultiple(['key']);
            expect(is_iterable($result))->toBe(true);
        });
    });
});
