<?php

namespace Ninja\Verisoul\Responses;

use Ninja\Verisoul\Enums\VerisoulEnvironment;

final readonly class LivenessSessionResponse extends ApiResponse
{
    public function __construct(
        public string $requestId,
        public string $sessionId,
    ) {}

    public function redirectUrl(VerisoulEnvironment $environment = VerisoulEnvironment::Sandbox, ?string $redirectUrl = null): string
    {
        $url = sprintf(
            'https://app.%s.verisoul.ai/?session_id=%s',
            $environment->value,
            $this->sessionId
        );

        if ($redirectUrl) {
            return sprintf('%s&redirect_url=%s', $url, urlencode($redirectUrl));
        }

        return $url;
    }
}
