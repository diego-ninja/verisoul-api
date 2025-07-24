<?php

namespace Ninja\Verisoul\Responses;

final readonly class EnrollAccountResponse extends ApiResponse
{
    public function __construct(
        public string $requestId,
        public bool $success,
    ) {}
}
