<?php

namespace Ninja\Verisoul\Clients;

use Exception;
use InvalidArgumentException;
use Ninja\Verisoul\Contracts\HttpClientInterface;
use Ninja\Verisoul\Contracts\VerisoulApi;
use Ninja\Verisoul\Enums\VerisoulApiEndpoint;
use Ninja\Verisoul\Enums\VerisoulEnvironment;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Ninja\Verisoul\Http\GuzzleHttpClient;
use Ninja\Verisoul\Support\CircuitBreaker;
use Ninja\Verisoul\Support\InMemoryCache;
use Ninja\Verisoul\Support\RetryStrategy;
use Psr\SimpleCache\CacheInterface;

abstract class Client implements VerisoulApi
{
    protected string $apiKey;

    protected int $timeout;

    protected int $connectTimeout;

    protected array $headers;

    private VerisoulEnvironment $environment;

    private RetryStrategy $retryStrategy;

    private CircuitBreaker $circuitBreaker;

    private HttpClientInterface $httpClient;

    public function __construct(
        string $apiKey,
        VerisoulEnvironment $environment = VerisoulEnvironment::Sandbox,
        int $timeout = 30,
        int $connectTimeout = 10,
        int $retryAttempts = 3,
        int $retryDelay = 1000,
        ?HttpClientInterface $httpClient = null,
        ?CacheInterface $cache = null,
    ) {
        $this->validateConstructorParams($apiKey, $timeout, $connectTimeout);

        $this->apiKey = $apiKey;
        $this->environment = $environment;
        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;

        $this->retryStrategy = new RetryStrategy(
            maxAttempts: $retryAttempts,
            baseDelayMs: $retryDelay,
        );

        $cache ??= new InMemoryCache();

        $this->circuitBreaker = new CircuitBreaker(
            service: static::class,
            cache: $cache,
            failureThreshold: 5,
            timeoutSeconds: $timeout,
            recoveryTime: 300,
        );

        $this->headers = $this->buildDefaultHeaders();

        $this->httpClient = $httpClient ?? new GuzzleHttpClient();
        $this->httpClient
            ->setTimeout($timeout)
            ->setConnectTimeout($connectTimeout)
            ->setHeaders($this->headers);
    }

    /**
     * @return static
     */
    public static function create(string $apiKey, VerisoulEnvironment $environment = VerisoulEnvironment::Sandbox): static
    {
        // @phpstan-ignore-next-line
        return new static($apiKey, $environment);
    }

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     * @throws Exception
     */
    public function get(string $endpoint, array $query = [], array $headers = []): array
    {
        $result = $this->retryStrategy->execute(function () use ($endpoint, $query, $headers) {
            $url = $this->getBaseUrl() . $endpoint;
            return $this->httpClient->get($url, $query, $headers);
        });

        if ( ! is_array($result)) {
            throw new VerisoulApiException('Expected array response from API');
        }

        return $result;
    }

    /**
     * @throws VerisoulConnectionException
     * @throws VerisoulApiException
     * @throws Exception
     */
    public function post(string $endpoint, array $data = [], array $headers = []): array
    {
        $result = $this->retryStrategy->execute(function () use ($endpoint, $data, $headers) {
            $url = $this->getBaseUrl() . $endpoint;
            return $this->httpClient->post($url, $data, $headers);
        });

        if ( ! is_array($result)) {
            throw new VerisoulApiException('Expected array response from API');
        }

        return $result;
    }

    /**
     * @throws VerisoulConnectionException
     * @throws VerisoulApiException
     * @throws Exception
     */
    public function put(string $endpoint, array $data = [], array $headers = []): array
    {
        $result = $this->retryStrategy->execute(function () use ($endpoint, $data, $headers) {
            $url = $this->getBaseUrl() . $endpoint;
            return $this->httpClient->put($url, $data, $headers);
        });

        if ( ! is_array($result)) {
            throw new VerisoulApiException('Expected array response from API');
        }

        return $result;
    }

    /**
     * @throws VerisoulConnectionException
     * @throws VerisoulApiException
     * @throws Exception
     */
    public function delete(string $endpoint, array $data = [], array $headers = []): array
    {
        $result = $this->retryStrategy->execute(function () use ($endpoint, $data, $headers) {
            $url = $this->getBaseUrl() . $endpoint;
            return $this->httpClient->delete($url, $data, $headers);
        });

        if ( ! is_array($result)) {
            throw new VerisoulApiException('Expected array response from API');
        }

        return $result;
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function setEnvironment(VerisoulEnvironment $environment): self
    {
        $this->environment = $environment;

        return $this;
    }

    public function getEnvironment(): VerisoulEnvironment
    {
        return $this->environment;
    }

    public function getBaseUrl(): string
    {
        return $this->environment->getBaseUrl();
    }

    /**
     * @throws VerisoulConnectionException
     * @throws VerisoulApiException
     */
    protected function call(VerisoulApiEndpoint $endpoint, array $parameters = [], array $data = []): array
    {
        $endpointPath = $endpoint->withParameters($parameters);
        $method = $endpoint->getMethod();


        $operation = function () use ($method, $endpointPath, $data) {
            return $this->retryStrategy->execute(function () use ($method, $endpointPath, $data) {
                $url = $this->getBaseUrl() . $endpointPath;

                return match (strtoupper($method)) {
                    'GET' => $this->httpClient->get($url, $data),
                    'POST' => $this->httpClient->post($url, $data),
                    'PUT' => $this->httpClient->put($url, $data),
                    'DELETE' => $this->httpClient->delete($url, $data),
                    default => throw new InvalidArgumentException("Unsupported HTTP method: {$method}"),
                };
            });
        };

        $result = $this->circuitBreaker->call($operation);

        if ( ! is_array($result)) {
            throw new VerisoulApiException('Expected array response from API');
        }

        return $result;
    }

    /**
     * Validate constructor parameters
     */
    private function validateConstructorParams(string $apiKey, int $timeout, int $connectTimeout): void
    {
        if (empty($apiKey)) {
            throw new InvalidArgumentException('API key is required');
        }

        if ($timeout <= 0 || $timeout > 300) {
            throw new InvalidArgumentException('Timeout must be between 1 and 300 seconds');
        }

        if ($connectTimeout <= 0 || $connectTimeout > $timeout) {
            throw new InvalidArgumentException('Connect timeout must be positive and <= timeout');
        }
    }


    /**
     * Build default headers
     */
    private function buildDefaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'x-api-key' => $this->apiKey,
            'User-Agent' => 'Verisoul-PHP/1.0 (PHP SDK)',
            'X-Client-Version' => '1.0.0',
        ];
    }

}
