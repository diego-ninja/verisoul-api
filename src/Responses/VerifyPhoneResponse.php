<?php

namespace Ninja\Verisoul\Responses;

use Ninja\Verisoul\DTO\Phone;

final readonly class VerifyPhoneResponse extends ApiResponse
{
    public function __construct(
        public string $projectId,
        public string $requestId,
        public Phone $phone,
    ) {}
}
