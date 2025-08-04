<?php

namespace Ninja\Verisoul\Contracts;

use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Ninja\Verisoul\Responses\VerifyFaceResponse;
use Ninja\Verisoul\Responses\VerifyIdentityResponse;

interface FaceMatchInterface extends BiometricInterface
{
    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function verify(string $sessionId): VerifyFaceResponse;

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function verifyIdentity(string $sessionId, string $accountId): VerifyIdentityResponse;
}
