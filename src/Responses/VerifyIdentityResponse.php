<?php

namespace Ninja\Verisoul\Responses;

final readonly class VerifyIdentityResponse extends ApiResponse
{
    public function __construct(
        public string $requestId,
        public bool $success,
        public bool $match,
    ) {}
}
