<?php

namespace Ninja\Verisoul\Clients\Liveness;

use Ninja\Verisoul\Contracts\IDCheckInterface;
use Ninja\Verisoul\Enums\VerisoulApiEndpoint;
use Ninja\Verisoul\Exceptions\VerisoulApiException;
use Ninja\Verisoul\Exceptions\VerisoulConnectionException;
use Ninja\Verisoul\Responses\LivenessSessionResponse;
use Ninja\Verisoul\Responses\VerifyIdResponse;

final class IDCheckClient extends LivenessApiClient implements IDCheckInterface
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
