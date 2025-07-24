<?php

namespace Ninja\Verisoul\Contracts;

use Ninja\Verisoul\Responses\AccountResponse;
use Ninja\Verisoul\Responses\AccountSessionsResponse;
use Ninja\Verisoul\Responses\DeleteAccountResponse;
use Ninja\Verisoul\Responses\LinkedAccountsResponse;

interface AccountInterface
{
    /**
     * Get account details
     */
    public function getAccount(string $accountId): AccountResponse;

    /**
     * Get account sessions
     */
    public function getAccountSessions(string $accountId): AccountSessionsResponse;

    /**
     * Get linked accounts
     */
    public function getLinkedAccounts(string $accountId): LinkedAccountsResponse;

    /**
     * Update account
     */
    public function updateAccount(string $accountId, array $data): AccountResponse;

    /**
     * Delete account
     */
    public function deleteAccount(string $accountId): DeleteAccountResponse;
}
