<?php

namespace Ninja\Verisoul\Responses;

use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class VerifyIdentityResponse extends ApiResponse
{
    public function __construct(
        public string $requestId,
        public bool $success,
        public bool $match,
    ) {}
}
