<?php

namespace Ninja\Verisoul\Tests;

use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Ninja\Verisoul\Enums\VerisoulEnvironment;
use Ninja\Verisoul\Contracts\HttpClientInterface;
use Psr\SimpleCache\CacheInterface;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up default environment
        $this->setUpEnvironment();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Set up test environment variables
     */
    protected function setUpEnvironment(): void
    {
        $_ENV['VERISOUL_API_KEY'] = $_ENV['VERISOUL_API_KEY'] ?? 'test_api_key_12345';
        $_ENV['VERISOUL_ENVIRONMENT'] = $_ENV['VERISOUL_ENVIRONMENT'] ?? 'sandbox';
    }

    /**
     * Create a mock HTTP client with common expectations
     */
    protected function createMockHttpClient(array $responses = []): HttpClientInterface
    {
        $mock = Mockery::mock(HttpClientInterface::class);
        
        $mock->shouldReceive('setTimeout')->andReturnSelf();
        $mock->shouldReceive('setConnectTimeout')->andReturnSelf();
        $mock->shouldReceive('setHeaders')->andReturnSelf();
        
        foreach ($responses as $method => $response) {
            $mock->shouldReceive($method)->andReturn($response);
        }
        
        return $mock;
    }

    /**
     * Create a mock cache with common expectations
     */
    protected function createMockCache(): CacheInterface
    {
        $mock = Mockery::mock(CacheInterface::class);
        
        $mock->shouldReceive('get')->andReturn(null);
        $mock->shouldReceive('set')->andReturn(true);
        $mock->shouldReceive('delete')->andReturn(true);
        $mock->shouldReceive('clear')->andReturn(true);
        $mock->shouldReceive('has')->andReturn(false);
        
        return $mock;
    }

    /**
     * Get a test API key
     */
    protected function getTestApiKey(): string
    {
        return $_ENV['VERISOUL_API_KEY'] ?? 'test_api_key_12345';
    }

    /**
     * Get test environment
     */
    protected function getTestEnvironment(): VerisoulEnvironment
    {
        return VerisoulEnvironment::Sandbox;
    }

    /**
     * Load fixture data from JSON files
     */
    protected function getFixture(string $name): array
    {
        $path = __DIR__ . "/fixtures/api/responses/{$name}.json";
        
        if (!file_exists($path)) {
            $this->fail("Fixture {$name} not found at {$path}");
        }
        
        $content = file_get_contents($path);
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail("Invalid JSON in fixture {$name}: " . json_last_error_msg());
        }
        
        return $data;
    }

    /**
     * Assert that an array has the structure of a successful API response
     */
    protected function assertValidApiResponse(array $response): void
    {
        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('data', $response);
        $this->assertIsBool($response['success']);
        $this->assertTrue($response['success']);
        $this->assertIsArray($response['data']);
    }

    /**
     * Assert that an array has the structure of an error API response
     */
    protected function assertValidErrorResponse(array $response): void
    {
        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('error', $response);
        $this->assertIsBool($response['success']);
        $this->assertFalse($response['success']);
        $this->assertIsArray($response['error']);
        $this->assertArrayHasKey('message', $response['error']);
    }

    /**
     * Generate random test data
     */
    protected function randomString(int $length = 10): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    protected function randomEmail(): string
    {
        return 'test+' . $this->randomString(8) . '@example.com';
    }

    protected function randomPhone(): string
    {
        return '+1' . rand(2000000000, 9999999999);
    }

    protected function randomApiKey(): string
    {
        return 'test_' . $this->randomString(32);
    }

    protected function randomSessionId(): string
    {
        return 'sess_' . $this->randomString(24);
    }

    protected function randomAccountId(): string
    {
        return 'acc_' . $this->randomString(24);
    }

    protected function randomScore(): float
    {
        return round(rand(0, 1000) / 1000, 3);
    }

    /**
     * Create mock successful HTTP response
     */
    protected function mockSuccessResponse(array $data = []): array
    {
        return [
            'success' => true,
            'data' => $data,
            'message' => 'Success',
            'timestamp' => date('c'),
        ];
    }

    /**
     * Create mock error HTTP response
     */
    protected function mockErrorResponse(string $message = 'Error', int $code = 400): array
    {
        return [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
            'timestamp' => date('c'),
        ];
    }

    /**
     * Assert that a value is within a numeric range
     */
    protected function assertInRange(float $value, float $min, float $max, string $message = ''): void
    {
        $this->assertGreaterThanOrEqual($min, $value, $message);
        $this->assertLessThanOrEqual($max, $value, $message);
    }

    /**
     * Assert that an object implements specific methods
     */
    protected function assertHasMethods(object $object, array $methods): void
    {
        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists($object, $method),
                sprintf('Method %s does not exist on %s', $method, get_class($object))
            );
        }
    }
}