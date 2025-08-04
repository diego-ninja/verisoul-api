<?php

namespace Ninja\Verisoul\DTO;

use Ninja\Granite\GraniteDTO;
use Ninja\Granite\Mapping\Conventions\SnakeCaseConvention;
use Ninja\Granite\Serialization\Attributes\SerializationConvention;

#[SerializationConvention(SnakeCaseConvention::class)]
final readonly class Address extends GraniteDTO
{
    public function __construct(
        public ?string $city,
        public ?string $country,
        public ?string $postalCode,
        public ?string $state,
        public ?string $street,
    ) {}

    /**
     * Check if address has any data
     */
    public function hasData(): bool
    {
        return ! empty($this->city) || ! empty($this->country) || ! empty($this->postalCode) ||
               ! empty($this->state) || ! empty($this->street);
    }

    /**
     * Check if address is complete
     */
    public function isComplete(): bool
    {
        return ! empty($this->street) && ! empty($this->city) &&
               ! empty($this->state) && ! empty($this->postalCode) && ! empty($this->country);
    }

    /**
     * Get formatted address string
     */
    public function getFormattedAddress(): string
    {
        $parts = array_filter([
            $this->street,
            $this->city,
            $this->state,
            $this->postalCode,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentage(): float
    {
        $fields = [$this->street, $this->city, $this->state, $this->postalCode, $this->country];
        $filledFields = count(array_filter($fields, fn(mixed $field) => ! empty($field)));

        return round(($filledFields / count($fields)) * 100, 1);
    }
}
