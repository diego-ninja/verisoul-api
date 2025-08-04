<?php

namespace Ninja\Verisoul\Contracts;

use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Ninja\Verisoul\Responses\VerifyIdResponse;

interface IDCheckInterface extends BiometricInterface
{
    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function verify(string $sessionId): VerifyIdResponse;
}
