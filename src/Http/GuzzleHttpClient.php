<?php

namespace Ninja\Verisoul\Http;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use Ninja\Verisoul\Contracts\HttpClientInterface;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Psr\Http\Message\ResponseInterface;

class GuzzleHttpClient implements HttpClientInterface
{
    private Client $client;
    private int $timeout = 30;
    private int $connectTimeout = 10;
    private array $headers = [];

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? new Client();
    }

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     * @throws GuzzleException
     */
    public function get(string $url, array $query = [], array $headers = []): array
    {
        return $this->makeRequest('GET', $url, [
            RequestOptions::QUERY => $this->mergeQueryParams($url, $query),
            RequestOptions::HEADERS => array_merge($this->headers, $headers),
        ]);
    }

    /**
     * @throws VerisoulApiException
     * @throws GuzzleException
     * @throws VerisoulConnectionException
     */
    public function post(string $url, array $data = [], array $headers = []): array
    {
        return $this->makeRequest('POST', $url, [
            RequestOptions::JSON => $data,
            RequestOptions::HEADERS => array_merge($this->headers, $headers),
        ]);
    }

    /**
     * @throws VerisoulApiException
     * @throws GuzzleException
     * @throws VerisoulConnectionException
     */
    public function put(string $url, array $data = [], array $headers = []): array
    {
        return $this->makeRequest('PUT', $url, [
            RequestOptions::JSON => $data,
            RequestOptions::HEADERS => array_merge($this->headers, $headers),
        ]);
    }

    /**
     * @throws VerisoulApiException
     * @throws GuzzleException
     * @throws VerisoulConnectionException
     */
    public function delete(string $url, array $data = [], array $headers = []): array
    {
        return $this->makeRequest('DELETE', $url, [
            RequestOptions::JSON => $data,
            RequestOptions::HEADERS => array_merge($this->headers, $headers),
        ]);
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function setConnectTimeout(int $connectTimeout): self
    {
        $this->connectTimeout = $connectTimeout;
        return $this;
    }

    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Make HTTP request with comprehensive error handling
     *
     * @throws VerisoulConnectionException
     * @throws VerisoulApiException|GuzzleException
     */
    private function makeRequest(string $method, string $url, array $options = []): array
    {
        try {
            // Add timeout options
            $options[RequestOptions::TIMEOUT] = $this->timeout;
            $options[RequestOptions::CONNECT_TIMEOUT] = $this->connectTimeout;

            $response = $this->client->request($method, $url, $options);

            return $this->handleResponse($response, $url);

        } catch (ConnectException $e) {
            throw VerisoulConnectionException::networkError($url, $e->getMessage());
        } catch (RequestException $e) {
            $response = $e->getResponse();
            if ($response) {
                throw $this->createApiExceptionFromResponse($url, $response);
            }
            throw VerisoulApiException::connectionFailed($url, $e);
        } catch (Exception $e) {
            throw VerisoulApiException::connectionFailed($url, $e);
        }
    }

    /**
     * Handle HTTP response with validation and error checking
     *
     * @throws VerisoulApiException
     */
    private function handleResponse(ResponseInterface $response, string $url): array
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode >= 300) {
            throw $this->createApiExceptionFromResponse($url, $response);
        }

        // Validate response content type
        $contentType = $response->getHeaderLine('content-type');
        if ( ! str_contains($contentType, 'application/json')) {
            throw VerisoulApiException::invalidResponse(
                $url,
                "Expected JSON response, got: {$contentType}",
            );
        }

        // Parse and validate JSON
        try {
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            throw VerisoulApiException::invalidResponse(
                $url,
                "Invalid JSON response: {$e->getMessage()}",
            );
        }

        if ( ! is_array($data)) {
            throw VerisoulApiException::invalidResponse($url, 'Response is not a JSON object');
        }

        // Check for business-level errors in successful HTTP responses
        $this->validateBusinessLogicResponse($data, $url);

        return $data;
    }

    /**
     * Validate business logic in response (even if HTTP status is 200)
     *
     * @throws VerisoulApiException
     */
    private function validateBusinessLogicResponse(array $data, string $url): void
    {
        // Check for explicit error indicators
        if (isset($data['error'])) {
            $errorMessage = is_string($data['error']) ? $data['error'] : 'Unknown error';
            throw new VerisoulApiException(
                message: "Business logic error: {$errorMessage}",
                statusCode: 200,
                response: $data,
                endpoint: $url,
            );
        }

        // Check for success flag being false
        if (isset($data['success']) && false === $data['success']) {
            $message = $data['message'] ?? 'Operation failed';
            throw new VerisoulApiException(
                message: "Operation failed: {$message}",
                statusCode: 200,
                response: $data,
                endpoint: $url,
            );
        }

        // Check for specific error status
        if (isset($data['status']) && 'error' === $data['status']) {
            $message = $data['message'] ?? $data['error_message'] ?? 'Unknown error';
            throw new VerisoulApiException(
                message: "API returned error status: {$message}",
                statusCode: 200,
                response: $data,
                endpoint: $url,
            );
        }
    }

    /**
     * Create appropriate API exception based on HTTP response
     */
    private function createApiExceptionFromResponse(string $url, ResponseInterface $response): VerisoulApiException
    {
        $statusCode = $response->getStatusCode();

        try {
            $decoded = json_decode($response->getBody()->getContents(), true);
            $responseData = is_array($decoded) ? $decoded : [];
        } catch (Exception $e) {
            $responseData = [];
        }

        return match ($statusCode) {
            401 => VerisoulApiException::authenticationFailed($url),
            400 => VerisoulApiException::badRequest($url, $responseData),
            404 => new VerisoulApiException(
                message: 'Resource not found',
                statusCode: 404,
                response: $responseData,
                endpoint: $url,
            ),
            422 => new VerisoulApiException(
                message: is_array($responseData) && isset($responseData['message']) && is_string($responseData['message']) ? $responseData['message'] : 'Validation failed',
                statusCode: 422,
                response: $responseData,
                endpoint: $url,
            ),
            429 => VerisoulApiException::rateLimitExceeded($url, $responseData),
            default => VerisoulApiException::serverError($url, $statusCode, $responseData),
        };
    }

    private function mergeQueryParams(string $url, array $queryParams): array
    {
        $uri = new Uri($url);

        $existingQuery = [];
        if ('' !== $uri->getQuery()) {
            $existingQuery = Query::parse($uri->getQuery());
        }

        return array_merge($queryParams, $existingQuery);
    }
}
