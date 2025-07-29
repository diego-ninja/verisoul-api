<?php

namespace Ninja\Verisoul\Responses;

use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;
use Ninja\Verisoul\Collections\LinkedAccountCollection;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class LinkedAccountsResponse extends ApiResponse
{
    public function __construct(
        public string $requestId,
        public LinkedAccountCollection $accountsLinked,
    ) {}
}
