<?php

namespace Ninja\Verisoul\Clients;

use Ninja\Verisoul\Collections\AccountListCollection;
use Ninja\Verisoul\Contracts\ListInterface;
use Ninja\Verisoul\DTO\AccountList;
use Ninja\Verisoul\Enums\VerisoulApiEndpoint;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Ninja\Verisoul\Responses\ListOperationResponse;

final class ListClient extends Client implements ListInterface
{
    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function createList(string $name, string $description): ListOperationResponse
    {
        $response = $this->call(
            VerisoulApiEndpoint::ListCreate,
            ['list_name' => $name],
            ['list_description' => $description],
        );

        return ListOperationResponse::from($response);
    }

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function getAllLists(): AccountListCollection
    {
        $response = $this->call(VerisoulApiEndpoint::ListGetAll);
        return new AccountListCollection($response['lists']);
    }

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function getList(string $listName): AccountList
    {
        $response = $this->call(
            VerisoulApiEndpoint::ListGet,
            ['list_name' => $listName],
        );

        return AccountList::from($response);
    }

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function addAccountToList(string $listName, string $accountId): ListOperationResponse
    {
        $response = $this->call(
            VerisoulApiEndpoint::ListAddAccount,
            ['list_name' => $listName, 'account_id' => $accountId],
        );

        return ListOperationResponse::from($response);
    }

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function deleteList(string $listName): ListOperationResponse
    {
        $response = $this->call(
            VerisoulApiEndpoint::ListDelete,
            ['list_name' => $listName],
        );

        return ListOperationResponse::from($response);
    }

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function removeAccountFromList(string $listName, string $accountId): ListOperationResponse
    {
        $response = $this->call(
            VerisoulApiEndpoint::ListRemoveAccount,
            ['list_name' => $listName, 'account_id' => $accountId],
        );

        return ListOperationResponse::from($response);
    }
}
