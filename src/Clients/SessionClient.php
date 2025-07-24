<?php

namespace Ninja\Verisoul\Clients;

use Ninja\Verisoul\Contracts\SessionInterface;
use Ninja\Verisoul\Responses\AuthenticateSessionResponse;
use Ninja\Verisoul\Responses\SessionResponse;
use Ninja\Verisoul\DTO\UserAccount;
use Ninja\Verisoul\Enums\VerisoulApiEndpoint;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;

final class SessionClient extends Client implements SessionInterface
{
    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function authenticate(UserAccount $account, string $sessionId, bool $accountsLinked = false): AuthenticateSessionResponse
    {
        $response = $this->call(
            VerisoulApiEndpoint::SessionAuthenticate,
            [
                'accounts_linked' => $accountsLinked,
            ],
            [
                'account' => $account->array(),
                'session_id' => $sessionId,
            ]
        );

        return AuthenticateSessionResponse::from($response);
    }

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function unauthenticated(string $sessionId, bool $accountsLinked = false): SessionResponse
    {
        $response = $this->call(
            VerisoulApiEndpoint::SessionUnauthenticated,
            [
                'accounts_linked' => $accountsLinked,
            ],
            [
                'session_id' => $sessionId,
            ]
        );

        return SessionResponse::from($response);
    }

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function getSession(string $sessionId): SessionResponse
    {
        $response = $this->call(
            VerisoulApiEndpoint::SessionGet,
            ['session_id' => $sessionId]
        );

        return SessionResponse::from($response);
    }
}
