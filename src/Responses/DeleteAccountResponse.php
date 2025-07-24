<?php

namespace Ninja\Verisoul\Responses;

final readonly class DeleteAccountResponse extends ApiResponse
{
    public function __construct(
        public string $requestId,
        public string $accountId,
        public bool $success
    ) {}
}
