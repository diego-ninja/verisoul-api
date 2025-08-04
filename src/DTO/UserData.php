<?php

namespace Ninja\Verisoul\DTO;

use Carbon\Carbon;
use Ninja\Granite\GraniteDTO;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
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
