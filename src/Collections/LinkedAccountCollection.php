<?php

namespace Ninja\Verisoul\Collections;

use DateMalformedStringException;
use Illuminate\Support\Collection;
use Ninja\Granite\Contracts\GraniteObject;
use Ninja\Granite\Exceptions\ReflectionException;
use Ninja\Verisoul\DTO\LinkedAccount;

final class LinkedAccountCollection extends Collection implements GraniteObject
{
    /**
     * @throws DateMalformedStringException
     * @throws ReflectionException
     */
    public static function from(mixed ...$args): static
    {
        $linkedAccountCollection = new self();

        foreach ($args[0] as $account) {
            $linkedAccountCollection->push(LinkedAccount::from($account));
        }

        return $linkedAccountCollection;
    }

    public function array(): array
    {
        return $this->map(fn(LinkedAccount $account) => $account->array())->toArray();
    }

    /**
     * @throws \JsonException
     */
    public function json(): string
    {
        return json_encode($this->array(), JSON_THROW_ON_ERROR);
    }
}
