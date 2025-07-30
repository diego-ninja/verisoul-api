<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\Granite;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;
use Ninja\Granite\Serialization\Attributes\SerializedName;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class UniqueValues extends Granite
{
    public function __construct(
        #[SerializedName('1_day')]
        public int $lastDay,
        #[SerializedName('7_day')]
        public int $lastWeek,
    ) {}
}
