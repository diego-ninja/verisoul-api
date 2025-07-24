<?php

namespace Ninja\Verisoul\Clients;

use Ninja\Verisoul\Contracts\PhoneInterface;
use Ninja\Verisoul\Responses\VerifyPhoneResponse;
use Ninja\Verisoul\Enums\VerisoulApiEndpoint;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;

final class PhoneClient extends Client implements PhoneInterface
{
    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function verifyPhone(string $phoneNumber): VerifyPhoneResponse
    {
        $response = $this->call(
            VerisoulApiEndpoint::VerifyPhone,
            [],
            ['phone_number' => $phoneNumber]
        );

        return VerifyPhoneResponse::from($response);
    }
}
