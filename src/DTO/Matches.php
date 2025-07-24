<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;

final readonly class Matches extends GraniteDTO
{
    public function __construct(
        public int $numAccountsLinked,
        public array $accountsLinked,
    ) {}
}
