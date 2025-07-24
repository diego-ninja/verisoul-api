<?php

namespace Ninja\Verisoul\ValueObjects;

use Ninja\Granite\GraniteVO;

final readonly class Score extends GraniteVO
{
    public function __construct(
        public float $value
    ) {}

    public static function rules(): array
    {
        return [
            'value' => 'required|numeric|min:0|max:1',
        ];
    }

    public static function from(mixed ...$values): static
    {
        // Handle single value case (most common)
        if (count($values) === 1) {
            $value = $values[0];

            if ($value instanceof self) {
                return $value;
            }

            if (is_float($value) || is_int($value)) {
                return new self((float) $value);
            }

            // Handle Bag format (array with 'value' key)
            if (is_array($value) && isset($value['value'])) {
                return new self((float) $value['value']);
            }
        }

        // Fallback to parent Bag::from() for other cases
        return parent::from(...$values);
    }

    public function value(): float
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
