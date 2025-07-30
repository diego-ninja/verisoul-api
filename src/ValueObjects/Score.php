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

    public function value(): float
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
