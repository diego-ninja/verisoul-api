<?php

namespace Ninja\Verisoul\Clients\Liveness;

use Ninja\Verisoul\Responses\LivenessSessionResponse;
use Ninja\Verisoul\Responses\VerifyIdResponse;
use Ninja\Verisoul\Contracts\IDCheckInterface;
use Ninja\Verisoul\Enums\VerisoulApiEndpoint;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;

final class IDCheckClient extends LivenessApiClient implements IDCheckInterface
{
    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function session(?string $referringSessionId = null): ?LivenessSessionResponse
    {
        $params = $referringSessionId !== null ?
            ['referring_session_id' => $referringSessionId] :
            [];

        $response = $this->call(VerisoulApiEndpoint::IDCheckSessionStart, array_merge($params, ['id' => 'true']));

        return LivenessSessionResponse::from($response);
    }

    /**
     * @throws VerisoulApiException
     * @throws VerisoulConnectionException
     */
    public function verify(string $sessionId): VerifyIdResponse
    {
        $response = $this->call(
            endpoint: VerisoulApiEndpoint::VerifyId,
            data: ['session_id' => $sessionId],
        );

        return VerifyIdResponse::from($response);
    }
}
