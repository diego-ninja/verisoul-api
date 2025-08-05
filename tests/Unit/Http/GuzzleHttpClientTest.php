<?php

namespace Tests\Unit\Http;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Ninja\Verisoul\Http\GuzzleHttpClient;
use Tests\TestCase;

describe('GuzzleHttpClient', function () {
    beforeEach(function () {
        $this->mockClient = mock(Client::class);
        $this->httpClient = new GuzzleHttpClient($this->mockClient);
        $this->testUrl = 'https://api.example.com/test';
    });

    describe('construction', function () {
        it('can be created with default Guzzle client', function () {
            $client = new GuzzleHttpClient();
            expect($client)->toBeInstanceOf(GuzzleHttpClient::class);
        });

        it('can be created with custom Guzzle client', function () {
            $customClient = mock(Client::class);
            $client = new GuzzleHttpClient($customClient);
            expect($client)->toBeInstanceOf(GuzzleHttpClient::class);
        });
    });

    describe('configuration methods', function () {
        it('can set timeout', function () {
            $result = $this->httpClient->setTimeout(60);
            expect($result)->toBe($this->httpClient);
        });

        it('can set connect timeout', function () {
            $result = $this->httpClient->setConnectTimeout(20);
            expect($result)->toBe($this->httpClient);
        });

        it('can set headers', function () {
            $headers = ['Authorization' => 'Bearer token'];
            $result = $this->httpClient->setHeaders($headers);
            expect($result)->toBe($this->httpClient);
        });
    });

    describe('GET requests', function () {
        it('makes successful GET request', function () {
            $responseData = ['success' => true, 'data' => 'test'];
            $response = new Response(200, ['content-type' => 'application/json'], json_encode($responseData));
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->with('GET', $this->testUrl, \Mockery::type('array'))
                ->andReturn($response);

            $result = $this->httpClient->get($this->testUrl);
            expect($result)->toBe($responseData);
        });

        it('includes query parameters in GET request', function () {
            $queryParams = ['page' => 1, 'limit' => 10];
            $responseData = ['success' => true];
            $response = new Response(200, ['content-type' => 'application/json'], json_encode($responseData));
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->with('GET', $this->testUrl, \Mockery::on(function ($options) use ($queryParams) {
                    return isset($options['query']) && $options['query'] === $queryParams;
                }))
                ->andReturn($response);

            $result = $this->httpClient->get($this->testUrl, $queryParams);
            expect($result)->toBe($responseData);
        });

        it('includes custom headers in GET request', function () {
            $headers = ['X-Custom-Header' => 'custom-value'];
            $responseData = ['success' => true];
            $response = new Response(200, ['content-type' => 'application/json'], json_encode($responseData));
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->with('GET', $this->testUrl, \Mockery::on(function ($options) use ($headers) {
                    return isset($options['headers']) && $options['headers'] === $headers;
                }))
                ->andReturn($response);

            $result = $this->httpClient->get($this->testUrl, [], $headers);
            expect($result)->toBe($responseData);
        });
    });

    describe('POST requests', function () {
        it('makes successful POST request', function () {
            $postData = ['name' => 'John', 'email' => 'john@example.com'];
            $responseData = ['success' => true, 'id' => 123];
            $response = new Response(200, ['content-type' => 'application/json'], json_encode($responseData));
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->with('POST', $this->testUrl, \Mockery::on(function ($options) use ($postData) {
                    return isset($options['json']) && $options['json'] === $postData;
                }))
                ->andReturn($response);

            $result = $this->httpClient->post($this->testUrl, $postData);
            expect($result)->toBe($responseData);
        });

        it('includes custom headers in POST request', function () {
            $postData = ['test' => 'data'];
            $headers = ['X-Custom-Header' => 'custom-value'];
            $responseData = ['success' => true];
            $response = new Response(200, ['content-type' => 'application/json'], json_encode($responseData));
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->with('POST', $this->testUrl, \Mockery::on(function ($options) {
                    return isset($options['headers']) && isset($options['json']);
                }))
                ->andReturn($response);

            $result = $this->httpClient->post($this->testUrl, $postData, $headers);
            expect($result)->toBe($responseData);
        });
    });

    describe('PUT requests', function () {
        it('makes successful PUT request', function () {
            $putData = ['name' => 'John Updated', 'email' => 'john.updated@example.com'];
            $responseData = ['success' => true, 'updated' => true];
            $response = new Response(200, ['content-type' => 'application/json'], json_encode($responseData));
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->with('PUT', $this->testUrl, \Mockery::on(function ($options) use ($putData) {
                    return isset($options['json']) && $options['json'] === $putData;
                }))
                ->andReturn($response);

            $result = $this->httpClient->put($this->testUrl, $putData);
            expect($result)->toBe($responseData);
        });
    });

    describe('DELETE requests', function () {
        it('makes successful DELETE request', function () {
            $deleteData = ['confirm' => true];
            $responseData = ['success' => true, 'deleted' => true];
            $response = new Response(200, ['content-type' => 'application/json'], json_encode($responseData));
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->with('DELETE', $this->testUrl, \Mockery::on(function ($options) use ($deleteData) {
                    return isset($options['json']) && $options['json'] === $deleteData;
                }))
                ->andReturn($response);

            $result = $this->httpClient->delete($this->testUrl, $deleteData);
            expect($result)->toBe($responseData);
        });
    });

    describe('timeout configuration', function () {
        it('includes timeout in request options', function () {
            $this->httpClient->setTimeout(45)->setConnectTimeout(15);
            $responseData = ['success' => true];
            $response = new Response(200, ['content-type' => 'application/json'], json_encode($responseData));
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->with('GET', $this->testUrl, \Mockery::on(function ($options) {
                    return $options['timeout'] === 45 && $options['connect_timeout'] === 15;
                }))
                ->andReturn($response);

            $this->httpClient->get($this->testUrl);
        });
    });

    describe('error handling', function () {
        it('throws VerisoulConnectionException on connect exception', function () {
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andThrow(new ConnectException('Connection failed', new Request('GET', $this->testUrl)));

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulConnectionException::class);
        });

        it('throws VerisoulApiException on request exception with response', function () {
            $response = new Response(400, ['content-type' => 'application/json'], '{"error": "Bad request"}');
            $exception = new RequestException('Bad request', new Request('GET', $this->testUrl), $response);
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andThrow($exception);

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });

        it('throws VerisoulApiException on request exception without response', function () {
            $exception = new RequestException('Request failed', new Request('GET', $this->testUrl));
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andThrow($exception);

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });

        it('throws VerisoulApiException on general exception', function () {
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andThrow(new Exception('Unexpected error'));

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('response handling', function () {
        it('throws VerisoulApiException for non-2xx status codes', function () {
            $response = new Response(404, ['content-type' => 'application/json'], '{"error": "Not found"}');
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andReturn($response);

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });

        it('throws VerisoulApiException for non-JSON content type', function () {
            $response = new Response(200, ['content-type' => 'text/html'], '<html>Not JSON</html>');
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andReturn($response);

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });

        it('throws VerisoulApiException for invalid JSON', function () {
            $response = new Response(200, ['content-type' => 'application/json'], 'invalid json');
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andReturn($response);

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });

        it('throws VerisoulApiException for non-array JSON', function () {
            $response = new Response(200, ['content-type' => 'application/json'], '"string response"');
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andReturn($response);

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('business logic validation', function () {
        it('throws VerisoulApiException when response contains error field', function () {
            $responseData = ['error' => 'Business logic error'];
            $response = new Response(200, ['content-type' => 'application/json'], json_encode($responseData));
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andReturn($response);

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });

        it('throws VerisoulApiException when success is false', function () {
            $responseData = ['success' => false, 'message' => 'Operation failed'];
            $response = new Response(200, ['content-type' => 'application/json'], json_encode($responseData));
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andReturn($response);

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });

        it('throws VerisoulApiException when status is error', function () {
            $responseData = ['status' => 'error', 'message' => 'API error'];
            $response = new Response(200, ['content-type' => 'application/json'], json_encode($responseData));
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andReturn($response);

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });

        it('handles error field as non-string', function () {
            $responseData = ['error' => ['code' => 123, 'message' => 'Error object']];
            $response = new Response(200, ['content-type' => 'application/json'], json_encode($responseData));
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andReturn($response);

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('HTTP status code handling', function () {
        it('creates authentication exception for 401', function () {
            $response = new Response(401, ['content-type' => 'application/json'], '{"error": "Unauthorized"}');
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andReturn($response);

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });

        it('creates bad request exception for 400', function () {
            $response = new Response(400, ['content-type' => 'application/json'], '{"error": "Bad request"}');
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andReturn($response);

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });

        it('creates not found exception for 404', function () {
            $response = new Response(404, ['content-type' => 'application/json'], '{"error": "Not found"}');
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andReturn($response);

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });

        it('creates validation exception for 422', function () {
            $response = new Response(422, ['content-type' => 'application/json'], '{"message": "Validation failed"}');
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andReturn($response);

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });

        it('creates rate limit exception for 429', function () {
            $response = new Response(429, ['content-type' => 'application/json'], '{"error": "Rate limit exceeded"}');
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andReturn($response);

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });

        it('creates server error exception for 500', function () {
            $response = new Response(500, ['content-type' => 'application/json'], '{"error": "Internal server error"}');
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andReturn($response);

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });

        it('handles non-JSON error responses gracefully', function () {
            $response = new Response(500, ['content-type' => 'text/html'], '<html>Server Error</html>');
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->andReturn($response);

            expect(fn() => $this->httpClient->get($this->testUrl))
                ->toThrow(VerisoulApiException::class);
        });
    });

    describe('header merging', function () {
        it('merges default headers with request headers', function () {
            $defaultHeaders = ['Authorization' => 'Bearer token', 'User-Agent' => 'Test'];
            $requestHeaders = ['X-Custom' => 'value'];
            $expectedHeaders = array_merge($defaultHeaders, $requestHeaders);
            
            $this->httpClient->setHeaders($defaultHeaders);
            
            $responseData = ['success' => true];
            $response = new Response(200, ['content-type' => 'application/json'], json_encode($responseData));
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->with('GET', $this->testUrl, \Mockery::on(function ($options) use ($expectedHeaders) {
                    return isset($options['headers']) && $options['headers'] === $expectedHeaders;
                }))
                ->andReturn($response);

            $this->httpClient->get($this->testUrl, [], $requestHeaders);
        });

        it('allows request headers to override default headers', function () {
            $defaultHeaders = ['Authorization' => 'Bearer old-token'];
            $requestHeaders = ['Authorization' => 'Bearer new-token'];
            $expectedHeaders = ['Authorization' => 'Bearer new-token'];
            
            $this->httpClient->setHeaders($defaultHeaders);
            
            $responseData = ['success' => true];
            $response = new Response(200, ['content-type' => 'application/json'], json_encode($responseData));
            
            $this->mockClient->shouldReceive('request')
                ->once()
                ->with('GET', $this->testUrl, \Mockery::on(function ($options) use ($expectedHeaders) {
                    return isset($options['headers']) && $options['headers'] === $expectedHeaders;
                }))
                ->andReturn($response);

            $this->httpClient->get($this->testUrl, [], $requestHeaders);
        });
    });
});