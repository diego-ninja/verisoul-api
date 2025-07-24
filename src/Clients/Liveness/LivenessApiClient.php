<?php

namespace Ninja\Verisoul\Clients\Liveness;

use Ninja\Verisoul\Clients\Client;
use Ninja\Verisoul\Responses\EnrollAccountResponse;
use Ninja\Verisoul\Contracts\BiometricInterface;
use Ninja\Verisoul\DTO\UserAccount;
use Ninja\Verisoul\Enums\VerisoulApiEndpoint;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;

abstract class LivenessApiClient extends Client implements BiometricInterface
{
    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function enroll(string $sessionId, UserAccount $account): EnrollAccountResponse
    {
        $response = $this->call(
            endpoint: VerisoulApiEndpoint::Enroll,
            data: ['session_id' => $sessionId, 'account_id' => $account->id],
        );

        return EnrollAccountResponse::from($response);
    }
}
