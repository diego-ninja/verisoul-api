<?php

namespace Ninja\Verisoul\Responses;

final readonly class AccountSessionsResponse extends ApiResponse
{
    public function __construct(
        public string $requestId,
        public array $sessions,
    ) {}
}
