<?php

namespace Ninja\Verisoul\Responses;

use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class AccountSessionsResponse extends ApiResponse
{
    public function __construct(
        public string $requestId,
        public array $sessions,
    ) {}
}
