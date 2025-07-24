<?php

namespace Ninja\Verisoul\Collections;

use DateMalformedStringException;
use Illuminate\Support\Collection;
use Ninja\Granite\Exceptions\ReflectionException;
use Ninja\Verisoul\DTO\AccountList;

final class AccountListCollection extends Collection
{
    /**
     * Create a new AccountListCollection instance from an array of accounts.
     *
     * @param array<string, mixed> $accounts
     * @return AccountListCollection
     * @throws DateMalformedStringException
     * @throws ReflectionException
     */
    public static function from(array $accounts): AccountListCollection
    {
        $accountListCollection = new self();

        foreach ($accounts as $account) {
            $accountListCollection->push(AccountList::from($account));
        }

        return $accountListCollection;
    }
}
