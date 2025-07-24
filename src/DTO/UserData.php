<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;
use Carbon\Carbon;

final readonly class UserData extends GraniteDTO
{
    public function __construct(
        public ?string $firstName,
        public ?string $lastName,
        public ?Carbon $dateOfBirth,
        public ?Carbon $dateOfExpiration,
        public ?Carbon $dateOfIssue,
        public ?string $idNumber,
        public ?string $idNumber2,
        public Address $address,
    ) {}
}
