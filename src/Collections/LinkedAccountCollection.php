<?php

namespace Ninja\Verisoul\Collections;

use Illuminate\Support\Collection;
use Ninja\Verisoul\DTO\LinkedAccount;

final class LinkedAccountCollection extends Collection
{
    public static function from(array $accounts): LinkedAccountCollection
    {
        $linkedAccountCollection = new self();

        foreach ($accounts as $account) {
            $linkedAccountCollection->push(LinkedAccount::from($account));
        }

        return $linkedAccountCollection;
    }
}
