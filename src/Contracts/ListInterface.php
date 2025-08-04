<?php

namespace Ninja\Verisoul\Contracts;

use Ninja\Verisoul\Collections\AccountListCollection;
use Ninja\Verisoul\DTO\AccountList;
use Ninja\Verisoul\Responses\ListOperationResponse;

interface ListInterface
{
    /**
     * Create new list
     */
    public function createList(string $name, string $description): ListOperationResponse;

    /**
     * Get all lists
     */
    public function getAllLists(): AccountListCollection;

    /**
     * Get accounts in list
     */
    public function getList(string $listName): AccountList;

    /**
     * Add account to list
     */
    public function addAccountToList(string $listName, string $accountId): ListOperationResponse;

    /**
     * Delete list
     */
    public function deleteList(string $listName): ListOperationResponse;

    /**
     * Remove account from list
     */
    public function removeAccountFromList(string $listName, string $accountId): ListOperationResponse;
}
