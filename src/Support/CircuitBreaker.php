<?php

namespace Ninja\Verisoul\Support;

use Exception;
use Ninja\Verisoul\Exceptions\CircuitBreakerOpenException;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Psr\SimpleCache\CacheInterface;

final readonly class CircuitBreaker
{
    private const string STATE_CLOSED = 'closed';

    private const string STATE_OPEN = 'open';

    private const string STATE_HALF_OPEN = 'half_open';

    public function __construct(
        private string $service,
        private CacheInterface $cache,
        private int $failureThreshold = 5,
        private int $timeoutSeconds = 60,
        private int $recoveryTime = 300, // 5 minutes
    ) {}

    /**
     * @throws VerisoulApiException
     */
    public function call(callable $callback): mixed
    {
        $state = $this->getState();

        switch ($state) {
            case self::STATE_OPEN:
                if ($this->shouldAttemptRecovery()) {
                    $this->setState(self::STATE_HALF_OPEN);

                    return $this->executeCall($callback);
                }
                throw new CircuitBreakerOpenException(
                    "Circuit breaker is OPEN for service: {$this->service}",
                    503,
                );

            case self::STATE_HALF_OPEN:
                try {
                    $result = $this->executeCall($callback);
                    $this->setState(self::STATE_CLOSED);
                    $this->resetFailureCount();

                    return $result;
                } catch (Exception $e) {
                    $this->setState(self::STATE_OPEN);
                    $this->recordFailure();
                    throw $e;
                }

            case self::STATE_CLOSED:
            default:
                try {
                    return $this->executeCall($callback);
                } catch (Exception $e) {
                    $this->recordFailure();
                    if ($this->getFailureCount() >= $this->failureThreshold) {
                        $this->setState(self::STATE_OPEN);
                    }
                    throw $e;
                }
        }
    }

    /**
     * @throws VerisoulApiException
     */
    private function executeCall(callable $callback): mixed
    {
        $startTime = microtime(true);

        try {
            $result = $callback();
            $this->recordSuccess();

            return $result;
        } catch (Exception $e) {
            $duration = microtime(true) - $startTime;

            // Consider timeout as failure
            if ($duration >= $this->timeoutSeconds) {
                throw new VerisoulApiException(
                    message: "Operation timed out after {$duration} seconds",
                    statusCode: 504,
                    previous: $e,
                );
            }

            throw $e;
        }
    }

    private function getState(): string
    {
        try {
            $state = $this->cache->get($this->getStateKey(), self::STATE_CLOSED);
            return is_string($state) ? $state : self::STATE_CLOSED;
        } catch (Exception $e) {
            return self::STATE_CLOSED;
        }
    }

    private function setState(string $state): void
    {
        try {
            $this->cache->set($this->getStateKey(), $state, $this->recoveryTime);
        } catch (Exception $e) {
            // Silently ignore cache failures
        }
    }

    private function getFailureCount(): int
    {
        try {
            $count = $this->cache->get($this->getFailureCountKey(), 0);
            return is_numeric($count) ? (int) $count : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    private function recordFailure(): void
    {
        try {
            $count = $this->getFailureCount() + 1;
            $this->cache->set($this->getFailureCountKey(), $count, 600); // 10 minutes
            $this->cache->set($this->getLastFailureKey(), time(), 600);
        } catch (Exception $e) {
            // Silently ignore cache failures
        }
    }

    private function recordSuccess(): void
    {
        try {
            // Optionally reduce failure count on success
            $count = max(0, $this->getFailureCount() - 1);
            if ($count > 0) {
                $this->cache->set($this->getFailureCountKey(), $count, 600); // 10 minutes
            } else {
                $this->cache->delete($this->getFailureCountKey());
            }
        } catch (Exception $e) {
            // Silently ignore cache failures
        }
    }

    private function resetFailureCount(): void
    {
        try {
            $this->cache->delete($this->getFailureCountKey());
            $this->cache->delete($this->getLastFailureKey());
        } catch (Exception $e) {
            // Silently ignore cache failures
        }
    }

    private function shouldAttemptRecovery(): bool
    {
        try {
            $lastFailure = $this->cache->get($this->getLastFailureKey());
            return null === $lastFailure ||
                (time() - (is_numeric($lastFailure) ? (int) $lastFailure : 0)) >= $this->recoveryTime;
        } catch (Exception $e) {
            return true; // Allow recovery if cache fails
        }
    }

    private function getStateKey(): string
    {
        return "circuit_breaker:{$this->service}:state";
    }

    private function getFailureCountKey(): string
    {
        return "circuit_breaker:{$this->service}:failures";
    }

    private function getLastFailureKey(): string
    {
        return "circuit_breaker:{$this->service}:last_failure";
    }
}
