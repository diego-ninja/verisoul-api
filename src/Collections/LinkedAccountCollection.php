<?php

namespace Ninja\Verisoul\Collections;

use DateMalformedStringException;
use Illuminate\Support\Collection;
use JsonException;
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

        $data = $args[0] ?? [];
        
        if (!is_iterable($data)) {
            throw new ReflectionException('Expected iterable data for LinkedAccountCollection', 'invalid_data_type');
        }
        
        foreach ($data as $account) {
            $linkedAccountCollection->push(LinkedAccount::from($account));
        }

        return $linkedAccountCollection;
    }

    public function array(): array
    {
        return $this->map(function ($account) {
            if (!$account instanceof LinkedAccount) {
                throw new \InvalidArgumentException('Expected LinkedAccount instance');
            }
            return $account->array();
        })->toArray();
    }

    /**
     * @throws JsonException
     */
    public function json(): string
    {
        return json_encode($this->array(), JSON_THROW_ON_ERROR);
    }
}
