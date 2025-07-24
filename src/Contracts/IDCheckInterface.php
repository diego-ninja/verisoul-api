<?php

namespace Ninja\Verisoul\Contracts;

use Ninja\Verisoul\Responses\VerifyIdResponse;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;

interface IDCheckInterface extends BiometricInterface
{
    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function verify(string $sessionId): VerifyIdResponse;
}
