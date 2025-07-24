<?php

use Ninja\Verisoul\Tests\TestCase;

uses(TestCase::class)->in('Unit', 'Feature', 'Integration');

// Custom expectations
expect()->extend('toBeValidDto', function () {
    return $this->toBeInstanceOf(stdClass::class)
        ->and($this->value)->toHaveMethod('toArray')
        ->and($this->value)->toHaveMethod('fromArray');
});

expect()->extend('toBeValidEnum', function () {
    return $this->toBeInstanceOf(BackedEnum::class);
});

expect()->extend('toBeValidResponse', function () {
    return $this->toHaveKeys(['success', 'data'])
        ->and($this->success)->toBeBool()
        ->and($this->data)->toBeArray();
});

expect()->extend('toBeValidScore', function () {
    return $this->toBeInstanceOf(\Ninja\Verisoul\ValueObjects\Score::class)
        ->and($this->value->value)->toBeFloat()
        ->and($this->value->value)->toBeGreaterThanOrEqual(0.0)
        ->and($this->value->value)->toBeLessThanOrEqual(1.0);
});

// Helper functions for tests
function createMockHttpClient(): \Ninja\Verisoul\Contracts\HttpClientInterface
{
    return Mockery::mock(\Ninja\Verisoul\Contracts\HttpClientInterface::class);
}

function createMockCache(): \Psr\SimpleCache\CacheInterface
{
    return Mockery::mock(\Psr\SimpleCache\CacheInterface::class);
}

function getFixture(string $name): array
{
    $path = __DIR__ . "/fixtures/api/responses/{$name}.json";
    
    if (!file_exists($path)) {
        throw new InvalidArgumentException("Fixture {$name} not found at {$path}");
    }
    
    $content = file_get_contents($path);
    $data = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new InvalidArgumentException("Invalid JSON in fixture {$name}: " . json_last_error_msg());
    }
    
    return $data;
}

function randomApiKey(): string
{
    return 'test_' . bin2hex(random_bytes(16));
}

function randomSessionId(): string
{
    return 'sess_' . bin2hex(random_bytes(12));
}

function randomAccountId(): string
{
    return 'acc_' . bin2hex(random_bytes(12));
}

function randomPhone(): string
{
    return '+1' . rand(2000000000, 9999999999);
}

function randomEmail(): string
{
    return 'test+' . bin2hex(random_bytes(4)) . '@example.com';
}

function createTestClient(string $clientClass, array $params = []): object
{
    $defaultParams = [
        'retryAttempts' => 1,
        'retryDelay' => 0
    ];
    
    $mergedParams = array_merge($defaultParams, $params);
    $apiKey = $mergedParams['apiKey'] ?? 'test_key';
    unset($mergedParams['apiKey']);
    
    return new $clientClass($apiKey, ...$mergedParams);
}

// Test data builders
function validAccountData(): array
{
    return [
        'id' => randomAccountId(),
        'email' => randomEmail(),
        'phone' => randomPhone(),
        'created_at' => date('c'),
        'updated_at' => date('c'),
    ];
}

function validSessionData(): array
{
    return [
        'session_id' => randomSessionId(),
        'account_id' => randomAccountId(), 
        'risk_level' => 'low',
        'score' => 0.95,
        'created_at' => date('c'),
    ];
}

function validScoreValue(): float
{
    return round(rand(0, 1000) / 1000, 3);
}

// Mock response builders
function mockSuccessResponse(array $data = []): array
{
    return [
        'success' => true,
        'data' => $data,
        'message' => 'Success',
        'timestamp' => date('c'),
    ];
}

function mockErrorResponse(string $message = 'Error', int $code = 400): array  
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

// Pest hooks
beforeEach(function () {
    // Reset any static state
    Mockery::resetContainer();
    
    // Set default environment variables
    $_ENV['VERISOUL_API_KEY'] = 'test_api_key_12345';
    $_ENV['VERISOUL_ENVIRONMENT'] = 'sandbox';
});

afterEach(function () {
    Mockery::close();
});