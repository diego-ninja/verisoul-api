<?php

namespace Ninja\Verisoul\Responses;

use Ninja\Verisoul\Collections\LinkedAccountCollection;

final readonly class LinkedAccountsResponse extends ApiResponse
{
    public function __construct(
        public string $requestId,
        public LinkedAccountCollection $accountsLinked,
    ) {}
}
