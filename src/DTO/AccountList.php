<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;
use Ninja\Verisoul\Collections\AccountListCollection;

final readonly class AccountList extends GraniteDTO
{
    public function __construct(
        public ?string $requestId,
        public ?string $name,
        public ?string $description,
        public array $accounts,
    ) {}
}
