<?php

namespace Ninja\Verisoul\Clients;

use Ninja\Verisoul\Responses\AccountResponse;
use Ninja\Verisoul\Responses\AccountSessionsResponse;
use Ninja\Verisoul\Responses\DeleteAccountResponse;
use Ninja\Verisoul\Responses\LinkedAccountsResponse;
use Ninja\Verisoul\Contracts\AccountInterface;
use Ninja\Verisoul\Enums\VerisoulApiEndpoint;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;

final class AccountClient extends Client implements AccountInterface
{
    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function getAccount(string $accountId): AccountResponse
    {
        $response = $this->call(
            VerisoulApiEndpoint::AccountGet,
            ['account_id' => $accountId]
        );

        return AccountResponse::from($response);
    }

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function getAccountSessions(string $accountId): AccountSessionsResponse
    {
        $response = $this->call(
            VerisoulApiEndpoint::AccountSessions,
            ['account_id' => $accountId]
        );

        return AccountSessionsResponse::from($response);
    }

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function getLinkedAccounts(string $accountId): LinkedAccountsResponse
    {
        $response = $this->call(
            VerisoulApiEndpoint::AccountLinked,
            ['account_id' => $accountId]
        );

        return LinkedAccountsResponse::from($response);
    }

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function updateAccount(string $accountId, array $data): AccountResponse
    {
        $response = $this->call(
            VerisoulApiEndpoint::AccountUpdate,
            ['account_id' => $accountId],
            $data
        );

        return AccountResponse::from($response);
    }

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function deleteAccount(string $accountId): DeleteAccountResponse
    {
        $response = $this->call(
            VerisoulApiEndpoint::AccountDelete,
            ['account_id' => $accountId]
        );

        return DeleteAccountResponse::from($response);
    }
}
