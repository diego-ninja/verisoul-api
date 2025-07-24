<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;
use Carbon\Carbon;

final readonly class Metadata extends GraniteDTO
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
