<?php

namespace Ninja\Verisoul\Responses;

final readonly class ListOperationResponse extends ApiResponse
{
    public function __construct(
        public string $requestId,
        public string $message,
        public bool $success,
    ) {}
}
