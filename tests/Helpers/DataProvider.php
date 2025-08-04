<?php

namespace Ninja\Verisoul\Tests\Helpers;

use Ninja\Verisoul\Enums\RiskLevel;
use Ninja\Verisoul\Enums\VerificationStatus;
use Ninja\Verisoul\Enums\VerisoulDecision;
use Ninja\Verisoul\Enums\VerisoulEnvironment;

class DataProvider
{
    /**
     * Provide valid API keys for testing
     */
    public static function validApiKeys(): array
    {
        return [
            ['test_api_key_12345'],
            ['sk_test_' . bin2hex(random_bytes(16))],
            ['pk_sandbox_' . bin2hex(random_bytes(16))],
        ];
    }

    /**
     * Provide invalid API keys for testing
     */
    public static function invalidApiKeys(): array
    {
        return [
            [''],
            [null],
            ['   '],
            ['invalid'],
            [123],
            [[]],
        ];
    }

    /**
     * Provide valid score values
     */
    public static function validScores(): array
    {
        return [
            [0.0],
            [0.5],
            [0.95],
            [1.0],
            [0.123],
            [0.999],
        ];
    }

    /**
     * Provide invalid score values
     */
    public static function invalidScores(): array
    {
        return [
            [-0.1],
            [1.1],
            [-1.0],
            [2.0],
            [null],
            ['0.5'],
            [[]],
        ];
    }

    /**
     * Provide valid timeout values
     */
    public static function validTimeouts(): array
    {
        return [
            [1, 1],
            [30, 10],
            [60, 30],
            [300, 300],
        ];
    }

    /**
     * Provide invalid timeout combinations
     */
    public static function invalidTimeouts(): array
    {
        return [
            [0, 1],      // timeout <= 0
            [-1, 1],     // negative timeout
            [301, 10],   // timeout > 300
            [30, 0],     // connect timeout <= 0
            [30, -1],    // negative connect timeout
            [10, 30],    // connect timeout > timeout
        ];
    }

    /**
     * Provide valid phone numbers
     */
    public static function validPhoneNumbers(): array
    {
        return [
            ['+14155552671'],
            ['+442071234567'],
            ['+33123456789'],
            ['+81312345678'],
            ['+12125551234'],
        ];
    }

    /**
     * Provide invalid phone numbers
     */
    public static function invalidPhoneNumbers(): array
    {
        return [
            [''],
            ['123'],
            ['invalid'],
            ['+1'],
            ['14155552671'], // missing +
            [null],
            [123],
        ];
    }

    /**
     * Provide valid email addresses
     */
    public static function validEmails(): array
    {
        return [
            ['test@example.com'],
            ['user+tag@domain.co.uk'],
            ['firstname.lastname@company.com'],
            ['email@subdomain.example.com'],
        ];
    }

    /**
     * Provide invalid email addresses
     */
    public static function invalidEmails(): array
    {
        return [
            [''],
            ['invalid'],
            ['@example.com'],
            ['test@'],
            ['test@.com'],
            [null],
            [123],
        ];
    }

    /**
     * Provide all risk levels
     */
    public static function riskLevels(): array
    {
        return array_map(fn($level) => [$level], RiskLevel::cases());
    }

    /**
     * Provide all verification statuses
     */
    public static function verificationStatuses(): array
    {
        return array_map(fn($status) => [$status], VerificationStatus::cases());
    }

    /**
     * Provide all Verisoul decisions
     */
    public static function verisoulDecisions(): array
    {
        return array_map(fn($decision) => [$decision], VerisoulDecision::cases());
    }

    /**
     * Provide all environments
     */
    public static function environments(): array
    {
        return array_map(fn($env) => [$env], VerisoulEnvironment::cases());
    }

    /**
     * Provide valid retry configurations
     */
    public static function validRetryConfigs(): array
    {
        return [
            [1, 1000],
            [3, 1000],
            [5, 2000],
            [10, 5000],
        ];
    }

    /**
     * Provide invalid retry configurations
     */
    public static function invalidRetryConfigs(): array
    {
        return [
            [0, 1000],   // attempts <= 0
            [-1, 1000],  // negative attempts
            [1, 0],      // delay <= 0
            [1, -1000],  // negative delay
        ];
    }

    /**
     * Provide HTTP methods
     */
    public static function httpMethods(): array
    {
        return [
            ['GET'],
            ['POST'],
            ['PUT'],
            ['DELETE'],
        ];
    }

    /**
     * Provide HTTP status codes
     */
    public static function httpStatusCodes(): array
    {
        return [
            [200, 'OK'],
            [201, 'Created'],
            [400, 'Bad Request'],
            [401, 'Unauthorized'],
            [403, 'Forbidden'],
            [404, 'Not Found'],
            [422, 'Unprocessable Entity'],
            [429, 'Too Many Requests'],
            [500, 'Internal Server Error'],
            [502, 'Bad Gateway'],
            [503, 'Service Unavailable'],
        ];
    }

    /**
     * Provide malformed JSON responses
     */
    public static function malformedJsonResponses(): array
    {
        return [
            [''],
            ['{'],
            ['{"invalid": json}'],
            ['{"missing": "comma" "error": true}'],
            ['null'],
            ['undefined'],
        ];
    }

    /**
     * Provide valid JSON but invalid API responses
     */
    public static function invalidApiResponses(): array
    {
        return [
            ['{}'],
            ['{"data": "no success field"}'],
            ['{"success": "not boolean"}'],
            ['{"success": true}'], // missing data
            ['{"success": false}'], // missing error
        ];
    }

    /**
     * Provide edge case data for testing boundaries
     */
    public static function edgeCaseData(): array
    {
        return [
            'empty_string' => [''],
            'whitespace_only' => ['   '],
            'null_value' => [null],
            'zero' => [0],
            'negative_number' => [-1],
            'very_large_number' => [PHP_INT_MAX],
            'float_precision' => [0.12345678901234567890],
            'unicode_string' => ['🔒 Tëst Dàtà 测试数据'],
            'long_string' => [str_repeat('a', 1000)],
        ];
    }
}
