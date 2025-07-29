<?php

namespace Ninja\Verisoul\Responses;

use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;
use Ninja\Verisoul\DTO\Phone;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class VerifyPhoneResponse extends ApiResponse
{
    public function __construct(
        public string $projectId,
        public string $requestId,
        public Phone $phone,
    ) {}
}
