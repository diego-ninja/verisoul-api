<?php

namespace Ninja\Verisoul\Contracts;

use Ninja\Verisoul\Responses\EnrollAccountResponse;
use Ninja\Verisoul\Responses\LivenessSessionResponse;
use Ninja\Verisoul\DTO\UserAccount;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;

interface BiometricInterface
{
    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function session(?string $referringSessionId = null): ?LivenessSessionResponse;

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function enroll(string $sessionId, UserAccount $account): EnrollAccountResponse;
}
