<?php

namespace Tests\Unit\Exceptions;

use Exception;
use Ninja\Verisoul\Exceptions\VerisoulApiException;

describe('VerisoulApiException', function (): void {
    beforeEach(function (): void {
        $this->endpoint = 'https://api.verisoul.com/test';
        $this->statusCode = 400;
        $this->response = ['error' => 'Test error'];
        $this->message = 'Test exception message';
    });

    describe('construction', function (): void {
        it('can be created with all parameters', function (): void {
            $previous = new Exception('Previous exception');

            $exception = new VerisoulApiException(
                message: $this->message,
                statusCode: $this->statusCode,
                response: $this->response,
                endpoint: $this->endpoint,
                previous: $previous,
            );

            expect($exception->getMessage())->toBe($this->message);
            expect($exception->statusCode)->toBe($this->statusCode);
            expect($exception->response)->toBe($this->response);
            expect($exception->endpoint)->toBe($this->endpoint);
            expect($exception->getPrevious())->toBe($previous);
        });

        it('can be created with minimal parameters', function (): void {
            $exception = new VerisoulApiException($this->message);

            expect($exception->getMessage())->toBe($this->message);
            expect($exception->statusCode)->toBe(0);
            expect($exception->response)->toBe([]);
            expect($exception->endpoint)->toBeNull();
            expect($exception->getPrevious())->toBeNull();
        });

        it('can be created with custom status code', function (): void {
            $exception = new VerisoulApiException(
                message: $this->message,
                statusCode: 404,
            );

            expect($exception->statusCode)->toBe(404);
            expect($exception->getCode())->toBe(404);
        });
    });

    describe('static factory methods', function (): void {
        describe('connectionFailed', function (): void {
            it('creates connection failed exception', function (): void {
                $previous = new Exception('Network error');
                $exception = VerisoulApiException::connectionFailed($this->endpoint, $previous);

                expect($exception->getMessage())->toContain('Failed to connect to Verisoul API');
                expect($exception->getMessage())->toContain($this->endpoint);
                expect($exception->statusCode)->toBe(0);
                expect($exception->endpoint)->toBe($this->endpoint);
                expect($exception->getPrevious())->toBe($previous);
            });
        });

        describe('authenticationFailed', function (): void {
            it('creates authentication failed exception', function (): void {
                $exception = VerisoulApiException::authenticationFailed($this->endpoint);

                expect($exception->getMessage())->toBe('Authentication failed for Verisoul API');
                expect($exception->statusCode)->toBe(401);
                expect($exception->endpoint)->toBe($this->endpoint);
                expect($exception->response)->toBe([]);
            });
        });

        describe('badRequest', function (): void {
            it('creates bad request exception with error message from response', function (): void {
                $response = ['error' => ['message' => 'Invalid input data']];
                $exception = VerisoulApiException::badRequest($this->endpoint, $response);

                expect($exception->getMessage())->toBe('Invalid input data');
                expect($exception->statusCode)->toBe(400);
                expect($exception->endpoint)->toBe($this->endpoint);
                expect($exception->response)->toBe($response);
            });

            it('creates bad request exception with default message when no error message', function (): void {
                $response = ['data' => 'some data'];
                $exception = VerisoulApiException::badRequest($this->endpoint, $response);

                expect($exception->getMessage())->toBe('Bad request to Verisoul API');
                expect($exception->statusCode)->toBe(400);
                expect($exception->endpoint)->toBe($this->endpoint);
                expect($exception->response)->toBe($response);
            });

            it('creates bad request exception with default message when error message is not string', function (): void {
                $response = ['error' => ['message' => 123]];
                $exception = VerisoulApiException::badRequest($this->endpoint, $response);

                expect($exception->getMessage())->toBe('Bad request to Verisoul API');
                expect($exception->statusCode)->toBe(400);
                expect($exception->endpoint)->toBe($this->endpoint);
                expect($exception->response)->toBe($response);
            });
        });

        describe('serverError', function (): void {
            it('creates server error exception', function (): void {
                $statusCode = 500;
                $response = ['error' => 'Internal server error'];
                $exception = VerisoulApiException::serverError($this->endpoint, $statusCode, $response);

                expect($exception->getMessage())->toBe('Verisoul API server error');
                expect($exception->statusCode)->toBe($statusCode);
                expect($exception->endpoint)->toBe($this->endpoint);
                expect($exception->response)->toBe($response);
            });

            it('creates server error exception with different status codes', function (): void {
                $statusCodes = [500, 502, 503, 504];

                foreach ($statusCodes as $statusCode) {
                    $exception = VerisoulApiException::serverError($this->endpoint, $statusCode, []);
                    expect($exception->statusCode)->toBe($statusCode);
                    expect($exception->getMessage())->toBe('Verisoul API server error');
                }
            });
        });

        describe('rateLimitExceeded', function (): void {
            it('creates rate limit exceeded exception', function (): void {
                $response = ['error' => 'Rate limit exceeded'];
                $exception = VerisoulApiException::rateLimitExceeded($this->endpoint, $response);

                expect($exception->getMessage())->toBe('Rate limit exceeded for Verisoul API');
                expect($exception->statusCode)->toBe(429);
                expect($exception->endpoint)->toBe($this->endpoint);
                expect($exception->response)->toBe($response);
            });
        });

        describe('invalidResponse', function (): void {
            it('creates invalid response exception', function (): void {
                $reason = 'Invalid JSON format';
                $exception = VerisoulApiException::invalidResponse($this->endpoint, $reason);

                expect($exception->getMessage())->toBe("Invalid response from Verisoul API: {$reason}");
                expect($exception->statusCode)->toBe(0);
                expect($exception->endpoint)->toBe($this->endpoint);
                expect($exception->response)->toBe([]);
            });

            it('creates invalid response exception with different reasons', function (): void {
                $reasons = [
                    'Expected JSON response, got HTML',
                    'Malformed JSON',
                    'Missing required fields',
                    'Unexpected response format',
                ];

                foreach ($reasons as $reason) {
                    $exception = VerisoulApiException::invalidResponse($this->endpoint, $reason);
                    expect($exception->getMessage())->toContain($reason);
                    expect($exception->statusCode)->toBe(0);
                }
            });
        });
    });

    describe('getErrorDetails method', function (): void {
        it('returns complete error details', function (): void {
            $exception = new VerisoulApiException(
                message: $this->message,
                statusCode: $this->statusCode,
                response: $this->response,
                endpoint: $this->endpoint,
            );

            $details = $exception->getErrorDetails();

            expect($details)->toBe([
                'message' => $this->message,
                'status_code' => $this->statusCode,
                'endpoint' => $this->endpoint,
                'response' => $this->response,
            ]);
        });

        it('returns error details with default values', function (): void {
            $exception = new VerisoulApiException($this->message);
            $details = $exception->getErrorDetails();

            expect($details)->toBe([
                'message' => $this->message,
                'status_code' => 0,
                'endpoint' => null,
                'response' => [],
            ]);
        });

        it('returns error details for different factory methods', function (): void {
            $previous = new Exception('Network error');
            $exception = VerisoulApiException::connectionFailed($this->endpoint, $previous);
            $details = $exception->getErrorDetails();

            expect($details['message'])->toContain('Failed to connect to Verisoul API');
            expect($details['status_code'])->toBe(0);
            expect($details['endpoint'])->toBe($this->endpoint);
            expect($details['response'])->toBe([]);
        });
    });

    describe('inheritance behavior', function (): void {
        it('extends Exception class', function (): void {
            $exception = new VerisoulApiException($this->message);
            expect($exception)->toBeInstanceOf(Exception::class);
        });

        it('preserves Exception methods', function (): void {
            $line = __LINE__ + 1;
            $exception = new VerisoulApiException($this->message, $this->statusCode);

            expect($exception->getMessage())->toBe($this->message);
            expect($exception->getCode())->toBe($this->statusCode);
            expect($exception->getFile())->toBe(__FILE__);
            expect($exception->getLine())->toBe($line);
        });

        it('can be caught as Exception', function (): void {
            try {
                throw new VerisoulApiException($this->message);
            } catch (Exception $e) {
                expect($e)->toBeInstanceOf(VerisoulApiException::class);
                expect($e->getMessage())->toBe($this->message);
            }
        });
    });

    describe('edge cases', function (): void {
        it('handles empty endpoint', function (): void {
            $exception = VerisoulApiException::connectionFailed('', new Exception());
            expect($exception->endpoint)->toBe('');
            expect($exception->getMessage())->toContain('Failed to connect to Verisoul API at endpoint:');
        });

        it('handles empty response array', function (): void {
            $exception = VerisoulApiException::badRequest($this->endpoint, []);
            expect($exception->response)->toBe([]);
            expect($exception->getMessage())->toBe('Bad request to Verisoul API');
        });

        it('handles complex response data', function (): void {
            $complexResponse = [
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid field',
                    'details' => [
                        'field' => 'email',
                        'reason' => 'Invalid format',
                    ],
                ],
                'timestamp' => '2023-01-01T00:00:00Z',
                'request_id' => 'req_12345',
            ];

            $exception = VerisoulApiException::badRequest($this->endpoint, $complexResponse);
            expect($exception->response)->toBe($complexResponse);
            expect($exception->getMessage())->toBe('Invalid field');
        });

        it('handles null values gracefully', function (): void {
            $exception = new VerisoulApiException(
                message: $this->message,
                statusCode: $this->statusCode,
                response: [],
                endpoint: null,
            );

            expect($exception->endpoint)->toBeNull();
            $details = $exception->getErrorDetails();
            expect($details['endpoint'])->toBeNull();
        });
    });
});
