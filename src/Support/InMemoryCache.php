<?php

namespace Ninja\Verisoul\Support;

use DateInterval;
use DateTime;
use Psr\SimpleCache\CacheInterface;

final class InMemoryCache implements CacheInterface
{
    private array $cache = [];
    private array $expiration = [];

    public function get(string $key, mixed $default = null): mixed
    {
        $this->cleanExpired();

        if ( ! $this->has($key)) {
            return $default;
        }

        return $this->cache[$key];
    }

    public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool
    {
        $this->cache[$key] = $value;

        if (null !== $ttl) {
            $expiresAt = $this->calculateExpiration($ttl);
            $this->expiration[$key] = $expiresAt;
        } else {
            unset($this->expiration[$key]);
        }

        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->cache[$key], $this->expiration[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->cache = [];
        $this->expiration = [];
        return true;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    public function setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            if (!is_string($key)) {
                throw new \InvalidArgumentException('Cache key must be a string');
            }
            if ( ! $this->set($key, $value, $ttl)) {
                return false;
            }
        }
        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    public function has(string $key): bool
    {
        $this->cleanExpired();
        return array_key_exists($key, $this->cache);
    }

    /**
     * Clean expired entries
     */
    private function cleanExpired(): void
    {
        $now = time();

        foreach ($this->expiration as $key => $expiresAt) {
            if ($expiresAt <= $now) {
                unset($this->cache[$key], $this->expiration[$key]);
            }
        }
    }

    /**
     * Calculate expiration timestamp
     */
    private function calculateExpiration(DateInterval|int $ttl): int
    {
        if ($ttl instanceof DateInterval) {
            $now = new DateTime();
            $expires = $now->add($ttl);
            return $expires->getTimestamp();
        }

        return time() + $ttl;
    }
}
