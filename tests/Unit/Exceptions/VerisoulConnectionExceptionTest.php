<?php

namespace Tests\Unit\Exceptions;

use Exception;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;

describe('VerisoulConnectionException', function (): void {
    beforeEach(function (): void {
        $this->endpoint = 'https://api.verisoul.com/test';
    });

    describe('inheritance', function (): void {
        it('extends VerisoulApiException', function (): void {
            $exception = new VerisoulConnectionException('Test message');
            expect($exception)->toBeInstanceOf(VerisoulApiException::class);
        });

        it('extends Exception', function (): void {
            $exception = new VerisoulConnectionException('Test message');
            expect($exception)->toBeInstanceOf(Exception::class);
        });

        it('can be caught as VerisoulApiException', function (): void {
            try {
                throw new VerisoulConnectionException('Test message');
            } catch (VerisoulApiException $e) {
                expect($e)->toBeInstanceOf(VerisoulConnectionException::class);
                expect($e->getMessage())->toBe('Test message');
            }
        });

        it('can be caught as Exception', function (): void {
            try {
                throw new VerisoulConnectionException('Test message');
            } catch (Exception $e) {
                expect($e)->toBeInstanceOf(VerisoulConnectionException::class);
                expect($e->getMessage())->toBe('Test message');
            }
        });
    });

    describe('construction', function (): void {
        it('can be created with basic message', function (): void {
            $message = 'Connection failed';
            $exception = new VerisoulConnectionException($message);

            expect($exception->getMessage())->toBe($message);
            expect($exception->statusCode)->toBe(0);
            expect($exception->response)->toBe([]);
            expect($exception->endpoint)->toBeNull();
        });

        it('can be created with all parameters', function (): void {
            $message = 'Connection failed';
            $statusCode = 0;
            $response = ['error' => 'connection_failed'];
            $endpoint = $this->endpoint;
            $previous = new Exception('Previous exception');

            $exception = new VerisoulConnectionException(
                message: $message,
                statusCode: $statusCode,
                response: $response,
                endpoint: $endpoint,
                previous: $previous,
            );

            expect($exception->getMessage())->toBe($message);
            expect($exception->statusCode)->toBe($statusCode);
            expect($exception->response)->toBe($response);
            expect($exception->endpoint)->toBe($endpoint);
            expect($exception->getPrevious())->toBe($previous);
        });
    });

    describe('static factory methods', function (): void {
        describe('timeout method', function (): void {
            it('creates timeout exception with correct message', function (): void {
                $timeout = 30;
                $exception = VerisoulConnectionException::timeout($this->endpoint, $timeout);

                expect($exception->getMessage())->toBe("Connection to Verisoul API timed out after {$timeout} seconds");
                expect($exception->statusCode)->toBe(0);
                expect($exception->endpoint)->toBe($this->endpoint);
                expect($exception->response)->toBe([]);
            });

            it('creates timeout exception for different timeout values', function (): void {
                $timeouts = [5, 10, 30, 60, 120];

                foreach ($timeouts as $timeout) {
                    $exception = VerisoulConnectionException::timeout($this->endpoint, $timeout);

                    expect($exception->getMessage())->toContain("timed out after {$timeout} seconds");
                    expect($exception->statusCode)->toBe(0);
                    expect($exception->endpoint)->toBe($this->endpoint);
                }
            });

            it('creates timeout exception with different endpoints', function (): void {
                $endpoints = [
                    'https://api.verisoul.com/sessions',
                    'https://api.verisoul.com/accounts',
                    'https://api.verisoul.com/phone',
                    'https://sandbox-api.verisoul.com/test',
                ];

                foreach ($endpoints as $endpoint) {
                    $exception = VerisoulConnectionException::timeout($endpoint, 30);

                    expect($exception->endpoint)->toBe($endpoint);
                    expect($exception->getMessage())->toContain('Connection to Verisoul API timed out');
                }
            });

            it('creates timeout exception with zero timeout', function (): void {
                $exception = VerisoulConnectionException::timeout($this->endpoint, 0);

                expect($exception->getMessage())->toBe('Connection to Verisoul API timed out after 0 seconds');
                expect($exception->statusCode)->toBe(0);
                expect($exception->endpoint)->toBe($this->endpoint);
            });

            it('creates timeout exception with large timeout', function (): void {
                $timeout = 3600; // 1 hour
                $exception = VerisoulConnectionException::timeout($this->endpoint, $timeout);

                expect($exception->getMessage())->toBe("Connection to Verisoul API timed out after {$timeout} seconds");
                expect($exception->statusCode)->toBe(0);
                expect($exception->endpoint)->toBe($this->endpoint);
            });
        });

        describe('networkError method', function (): void {
            it('creates network error exception with correct message', function (): void {
                $error = 'Connection refused';
                $exception = VerisoulConnectionException::networkError($this->endpoint, $error);

                expect($exception->getMessage())->toBe("Network error connecting to Verisoul API: {$error}");
                expect($exception->statusCode)->toBe(0);
                expect($exception->endpoint)->toBe($this->endpoint);
                expect($exception->response)->toBe([]);
            });

            it('creates network error exception for different error types', function (): void {
                $errors = [
                    'Connection refused',
                    'DNS resolution failed',
                    'SSL certificate verification failed',
                    'Network unreachable',
                    'Connection reset by peer',
                ];

                foreach ($errors as $error) {
                    $exception = VerisoulConnectionException::networkError($this->endpoint, $error);

                    expect($exception->getMessage())->toContain("Network error connecting to Verisoul API: {$error}");
                    expect($exception->statusCode)->toBe(0);
                    expect($exception->endpoint)->toBe($this->endpoint);
                }
            });

            it('creates network error exception with different endpoints', function (): void {
                $endpoints = [
                    'https://api.verisoul.com/sessions',
                    'https://api.verisoul.com/accounts',
                    'https://api.verisoul.com/phone',
                    'https://sandbox-api.verisoul.com/test',
                ];

                foreach ($endpoints as $endpoint) {
                    $exception = VerisoulConnectionException::networkError($endpoint, 'Connection failed');

                    expect($exception->endpoint)->toBe($endpoint);
                    expect($exception->getMessage())->toContain('Network error connecting to Verisoul API');
                }
            });

            it('creates network error exception with empty error message', function (): void {
                $exception = VerisoulConnectionException::networkError($this->endpoint, '');

                expect($exception->getMessage())->toBe('Network error connecting to Verisoul API: ');
                expect($exception->statusCode)->toBe(0);
                expect($exception->endpoint)->toBe($this->endpoint);
            });

            it('creates network error exception with complex error message', function (): void {
                $error = 'cURL error 7: Failed to connect to api.verisoul.com port 443: Connection refused';
                $exception = VerisoulConnectionException::networkError($this->endpoint, $error);

                expect($exception->getMessage())->toBe("Network error connecting to Verisoul API: {$error}");
                expect($exception->statusCode)->toBe(0);
                expect($exception->endpoint)->toBe($this->endpoint);
            });
        });
    });

    describe('error details and logging', function (): void {
        it('provides detailed error information for timeout', function (): void {
            $exception = VerisoulConnectionException::timeout($this->endpoint, 30);
            $details = $exception->getErrorDetails();

            expect($details)->toBe([
                'message' => 'Connection to Verisoul API timed out after 30 seconds',
                'status_code' => 0,
                'endpoint' => $this->endpoint,
                'response' => [],
            ]);
        });

        it('provides detailed error information for network error', function (): void {
            $error = 'Connection refused';
            $exception = VerisoulConnectionException::networkError($this->endpoint, $error);
            $details = $exception->getErrorDetails();

            expect($details)->toBe([
                'message' => "Network error connecting to Verisoul API: {$error}",
                'status_code' => 0,
                'endpoint' => $this->endpoint,
                'response' => [],
            ]);
        });
    });

    describe('practical usage scenarios', function (): void {
        it('handles timeout scenarios in API calls', function (): void {
            $scenarios = [
                ['endpoint' => 'https://api.verisoul.com/sessions', 'timeout' => 5],
                ['endpoint' => 'https://api.verisoul.com/accounts/123', 'timeout' => 10],
                ['endpoint' => 'https://api.verisoul.com/phone/verify', 'timeout' => 15],
            ];

            foreach ($scenarios as $scenario) {
                $exception = VerisoulConnectionException::timeout(
                    $scenario['endpoint'],
                    $scenario['timeout'],
                );

                expect($exception)->toBeInstanceOf(VerisoulConnectionException::class);
                expect($exception->getMessage())->toContain("timed out after {$scenario['timeout']} seconds");
                expect($exception->endpoint)->toBe($scenario['endpoint']);
            }
        });

        it('handles network error scenarios in API calls', function (): void {
            $scenarios = [
                ['endpoint' => 'https://api.verisoul.com/sessions', 'error' => 'DNS lookup failed'],
                ['endpoint' => 'https://api.verisoul.com/accounts', 'error' => 'SSL handshake failed'],
                ['endpoint' => 'https://api.verisoul.com/phone', 'error' => 'Connection refused'],
            ];

            foreach ($scenarios as $scenario) {
                $exception = VerisoulConnectionException::networkError(
                    $scenario['endpoint'],
                    $scenario['error'],
                );

                expect($exception)->toBeInstanceOf(VerisoulConnectionException::class);
                expect($exception->getMessage())->toContain($scenario['error']);
                expect($exception->endpoint)->toBe($scenario['endpoint']);
            }
        });

        it('can be used in try-catch blocks for specific handling', function (): void {
            $exceptionThrown = false;
            $caughtException = null;

            try {
                throw VerisoulConnectionException::timeout($this->endpoint, 30);
            } catch (VerisoulConnectionException $e) {
                $exceptionThrown = true;
                $caughtException = $e;
            }

            expect($exceptionThrown)->toBe(true);
            expect($caughtException)->toBeInstanceOf(VerisoulConnectionException::class);
            expect($caughtException->getMessage())->toContain('timed out after 30 seconds');
        });

        it('can be used in generic exception handling', function (): void {
            $exceptionThrown = false;
            $caughtException = null;

            try {
                throw VerisoulConnectionException::networkError($this->endpoint, 'Connection failed');
            } catch (Exception $e) {
                $exceptionThrown = true;
                $caughtException = $e;
            }

            expect($exceptionThrown)->toBe(true);
            expect($caughtException)->toBeInstanceOf(VerisoulConnectionException::class);
            expect($caughtException->getMessage())->toContain('Network error connecting to Verisoul API');
        });
    });

    describe('edge cases', function (): void {
        it('handles empty endpoint in timeout', function (): void {
            $exception = VerisoulConnectionException::timeout('', 30);

            expect($exception->endpoint)->toBe('');
            expect($exception->getMessage())->toBe('Connection to Verisoul API timed out after 30 seconds');
        });

        it('handles empty endpoint in networkError', function (): void {
            $exception = VerisoulConnectionException::networkError('', 'Connection failed');

            expect($exception->endpoint)->toBe('');
            expect($exception->getMessage())->toBe('Network error connecting to Verisoul API: Connection failed');
        });

        it('handles negative timeout values', function (): void {
            $exception = VerisoulConnectionException::timeout($this->endpoint, -1);

            expect($exception->getMessage())->toBe('Connection to Verisoul API timed out after -1 seconds');
            expect($exception->statusCode)->toBe(0);
            expect($exception->endpoint)->toBe($this->endpoint);
        });

        it('handles special characters in error messages', function (): void {
            $specialErrors = [
                'Error with "quotes"',
                'Error with \backslashes',
                'Error with unicode: ñáéíóú',
                'Error with symbols: @#$%^&*()',
            ];

            foreach ($specialErrors as $error) {
                $exception = VerisoulConnectionException::networkError($this->endpoint, $error);
                expect($exception->getMessage())->toContain($error);
            }
        });

        it('maintains status code as 0 for all connection exceptions', function (): void {
            $timeoutException = VerisoulConnectionException::timeout($this->endpoint, 30);
            $networkException = VerisoulConnectionException::networkError($this->endpoint, 'Connection failed');

            expect($timeoutException->statusCode)->toBe(0);
            expect($networkException->statusCode)->toBe(0);
        });
    });
});
