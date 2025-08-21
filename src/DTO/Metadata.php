<?php

namespace Ninja\Verisoul\DTO;

use Carbon\Carbon;
use Ninja\Granite\Granite;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class Metadata extends Granite
{
    public function __construct(
        public string $projectId,
        public string $sessionId,
        public ?string $accountId,
        public ?string $referringSessionId,
        public string $requestId,
        public Carbon $timestamp,
    ) {}
}
