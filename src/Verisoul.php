<?php

namespace Ninja\Verisoul;

use Ninja\Verisoul\Clients\AccountClient;
use Ninja\Verisoul\Clients\ListClient;
use Ninja\Verisoul\Clients\PhoneClient;
use Ninja\Verisoul\Clients\SessionClient;
use Ninja\Verisoul\Clients\Liveness\FaceMatchClient;
use Ninja\Verisoul\Clients\Liveness\IDCheckClient;
use Ninja\Verisoul\Clients\Liveness\LivenessApiClient;
use Ninja\Verisoul\Contracts\AccountInterface;
use Ninja\Verisoul\Contracts\FaceMatchInterface;
use Ninja\Verisoul\Contracts\HttpClientInterface;
use Ninja\Verisoul\Contracts\IDCheckInterface;
use Ninja\Verisoul\Contracts\ListInterface;
use Ninja\Verisoul\Contracts\PhoneInterface;
use Ninja\Verisoul\Contracts\SessionInterface;
use Ninja\Verisoul\Enums\VerisoulEnvironment;
use Ninja\Verisoul\Http\GuzzleHttpClient;
use InvalidArgumentException;
use Ninja\Verisoul\Support\InMemoryCache;
use Psr\SimpleCache\CacheInterface;

final class Verisoul
{
    private string $apiKey;
    private VerisoulEnvironment $environment;
    private int $timeout;
    private int $connectTimeout;
    private int $retryAttempts;
    private int $retryDelay;
    private ?HttpClientInterface $httpClient;
    private ?CacheInterface $cache = null;

    // Lazy-loaded clients
    private ?PhoneClient $phoneClient = null;
    private ?SessionClient $sessionClient = null;
    private ?AccountClient $accountClient = null;
    private ?ListClient $listClient = null;
    private ?IDCheckClient $idCheckClient = null;
    private ?FaceMatchClient $faceMatchClient = null;

    public function __construct(
        string $apiKey,
        VerisoulEnvironment $environment = VerisoulEnvironment::Sandbox,
        int $timeout = 30,
        int $connectTimeout = 10,
        int $retryAttempts = 3,
        int $retryDelay = 1000,
        ?HttpClientInterface $httpClient = null,
        ?CacheInterface $cache = null
    ) {
        if (empty($apiKey)) {
            throw new InvalidArgumentException('API key is required');
        }

        $this->apiKey = $apiKey;
        $this->environment = $environment;
        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;
        $this->retryAttempts = $retryAttempts;
        $this->retryDelay = $retryDelay;
        $this->httpClient = $httpClient;
        $this->cache = $cache ?? new InMemoryCache();
    }

    /**
     * Create a new Verisoul instance
     */
    public static function create(
        string $apiKey,
        VerisoulEnvironment $environment = VerisoulEnvironment::Sandbox
    ): self {
        return new self($apiKey, $environment);
    }

    /**
     * Get Phone verification client
     */
    public function phone(): PhoneInterface
    {
        if ($this->phoneClient === null) {
            $this->phoneClient = new PhoneClient(
                $this->apiKey,
                $this->environment,
                $this->timeout,
                $this->connectTimeout,
                $this->retryAttempts,
                $this->retryDelay,
                $this->httpClient,
                $this->cache
            );
        }

        return $this->phoneClient;
    }

    /**
     * Get Session management client
     */
    public function sessions(): SessionInterface
    {
        if ($this->sessionClient === null) {
            $this->sessionClient = new SessionClient(
                $this->apiKey,
                $this->environment,
                $this->timeout,
                $this->connectTimeout,
                $this->retryAttempts,
                $this->retryDelay,
                $this->httpClient,
                $this->cache
            );
        }

        return $this->sessionClient;
    }

    /**
     * Get Account management client
     */
    public function accounts(): AccountInterface
    {
        if ($this->accountClient === null) {
            $this->accountClient = new AccountClient(
                $this->apiKey,
                $this->environment,
                $this->timeout,
                $this->connectTimeout,
                $this->retryAttempts,
                $this->retryDelay,
                $this->httpClient,
                $this->cache
            );
        }

        return $this->accountClient;
    }

    /**
     * Get List operations client
     */
    public function lists(): ListInterface
    {
        if ($this->listClient === null) {
            $this->listClient = new ListClient(
                $this->apiKey,
                $this->environment,
                $this->timeout,
                $this->connectTimeout,
                $this->retryAttempts,
                $this->retryDelay,
                $this->httpClient,
                $this->cache
            );
        }

        return $this->listClient;
    }


    /**
     * Get ID verification client
     */
    public function idCheck(): IDCheckInterface
    {
        if ($this->idCheckClient === null) {
            $this->idCheckClient = new IDCheckClient(
                $this->apiKey,
                $this->environment,
                $this->timeout,
                $this->connectTimeout,
                $this->retryAttempts,
                $this->retryDelay,
                $this->httpClient,
                $this->cache
            );
        }

        return $this->idCheckClient;
    }

    /**
     * Get Face matching client
     */
    public function faceMatch(): FaceMatchInterface
    {
        if ($this->faceMatchClient === null) {
            $this->faceMatchClient = new FaceMatchClient(
                $this->apiKey,
                $this->environment,
                $this->timeout,
                $this->connectTimeout,
                $this->retryAttempts,
                $this->retryDelay,
                $this->httpClient,
                $this->cache
            );
        }

        return $this->faceMatchClient;
    }

    /**
     * Get current environment
     */
    public function getEnvironment(): VerisoulEnvironment
    {
        return $this->environment;
    }

    /**
     * Switch environment
     */
    public function setEnvironment(VerisoulEnvironment $environment): self
    {
        $this->environment = $environment;
        
        // Reset all clients to pick up new environment
        $this->resetClients();
        
        return $this;
    }

    /**
     * Update API key
     */
    public function setApiKey(string $apiKey): self
    {
        if (empty($apiKey)) {
            throw new InvalidArgumentException('API key is required');
        }

        $this->apiKey = $apiKey;
        
        // Reset all clients to pick up new API key
        $this->resetClients();
        
        return $this;
    }

    /**
     * Set custom HTTP client for all clients
     */
    public function setHttpClient(HttpClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;
        
        // Reset all clients to pick up new HTTP client
        $this->resetClients();
        
        return $this;
    }

    /**
     * Configure timeout settings
     */
    public function setTimeout(int $timeout, ?int $connectTimeout = null): self
    {
        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout ?? $this->connectTimeout;
        
        // Reset all clients to pick up new timeout settings
        $this->resetClients();
        
        return $this;
    }

    /**
     * Configure retry settings
     */
    public function setRetrySettings(int $attempts, int $delayMs): self
    {
        $this->retryAttempts = $attempts;
        $this->retryDelay = $delayMs;
        
        // Reset all clients to pick up new retry settings
        $this->resetClients();
        
        return $this;
    }

    /**
     * Reset all lazy-loaded clients (forces recreation with new settings)
     */
    private function resetClients(): void
    {
        $this->phoneClient = null;
        $this->sessionClient = null;
        $this->accountClient = null;
        $this->listClient = null;
        $this->idCheckClient = null;
        $this->faceMatchClient = null;
    }


    /**
     * Check if service is healthy (simple ping to sandbox)
     */
    public function healthCheck(): bool
    {
        try {
            // Use a simple operation to test connectivity
            $this->sessions();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get base URL for current environment
     */
    public function getBaseUrl(): string
    {
        return $this->environment->getBaseUrl();
    }

    /**
     * Check if we're in production environment
     */
    public function isProduction(): bool
    {
        return $this->environment === VerisoulEnvironment::Production;
    }

    /**
     * Check if we're in sandbox environment
     */
    public function isSandbox(): bool
    {
        return $this->environment === VerisoulEnvironment::Sandbox;
    }
}