<?php

namespace Ninja\Verisoul\Exceptions;

class CircuitBreakerOpenException extends VerisoulApiException
{
    public function __construct(string $message = 'Circuit breaker is open', int $statusCode = 503, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, [], null, $previous);
    }
}