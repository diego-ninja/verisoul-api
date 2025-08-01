<?php

namespace Ninja\Verisoul\Clients\Liveness;

use Ninja\Verisoul\Contracts\FaceMatchInterface;
use Ninja\Verisoul\Enums\VerisoulApiEndpoint;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Ninja\Verisoul\Responses\LivenessSessionResponse;
use Ninja\Verisoul\Responses\VerifyFaceResponse;
use Ninja\Verisoul\Responses\VerifyIdentityResponse;

final class FaceMatchClient extends LivenessApiClient implements FaceMatchInterface
{
    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function session(?string $referringSessionId = null): ?LivenessSessionResponse
    {
        $params = null !== $referringSessionId ?
            ['referring_session_id' => $referringSessionId] :
            [];

        $response = $this->call(VerisoulApiEndpoint::FaceMatchSessionStart, $params);

        return LivenessSessionResponse::from($response);
    }

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function verify(string $sessionId): VerifyFaceResponse
    {
        $response = $this->call(
            endpoint: VerisoulApiEndpoint::VerifyFace,
            data: ['session_id' => $sessionId],
        );

        return VerifyFaceResponse::from($response);
    }

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function verifyIdentity(string $sessionId, string $accountId): VerifyIdentityResponse
    {
        $response = $this->call(
            endpoint: VerisoulApiEndpoint::VerifyIdentity,
            data: ['session_id' => $sessionId, 'account_id' => $accountId],
        );

        return VerifyIdentityResponse::from($response);
    }
}
