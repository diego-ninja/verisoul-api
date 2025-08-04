<?php

namespace Ninja\Verisoul\Collections;

use DateMalformedStringException;
use Illuminate\Support\Collection;
use JsonException;
use Ninja\Granite\Contracts\GraniteObject;
use Ninja\Granite\Exceptions\ReflectionException;
use Ninja\Verisoul\DTO\AccountList;

final class AccountListCollection extends Collection implements GraniteObject
{
    /**
     * Create a new AccountListCollection instance from an array of accounts.
     *
     * @param mixed ...$args
     * @return AccountListCollection
     * @throws DateMalformedStringException
     * @throws ReflectionException
     */
    public static function from(mixed ...$args): static
    {
        $accountListCollection = new self();

        $data = $args[0] ?? [];
        
        if (!is_iterable($data)) {
            throw new ReflectionException('Expected iterable data for AccountListCollection', 'invalid_data_type');
        }
        
        foreach ($data as $account) {
            $accountListCollection->push(AccountList::from($account));
        }

        return $accountListCollection;
    }

    public function array(): array
    {
        return $this->map(function ($account) {
            if (!$account instanceof AccountList) {
                throw new \InvalidArgumentException('Expected AccountList instance');
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
