<?php

namespace Ninja\Verisoul\Contracts;

use Ninja\Verisoul\Responses\VerifyPhoneResponse;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;

interface PhoneInterface
{
    /**
     * Verify a phone number and return carrier and line type information
     *
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function verifyPhone(string $phoneNumber): VerifyPhoneResponse;
}
