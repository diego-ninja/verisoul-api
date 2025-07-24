<?php

namespace Ninja\Verisoul\Contracts;

use Ninja\Verisoul\Responses\AuthenticateSessionResponse;
use Ninja\Verisoul\Responses\SessionResponse;
use Ninja\Verisoul\DTO\UserAccount;

interface SessionInterface
{
    /**
     * Authenticate session with account
     */
    public function authenticate(UserAccount $account, string $sessionId, bool $accountsLinked = false): AuthenticateSessionResponse;

    /**
     * Evaluate unauthenticated session
     */
    public function unauthenticated(string $sessionId, bool $accountsLinked = false): SessionResponse;

    /**
     * Get session details
     */
    public function getSession(string $sessionId): SessionResponse;
}
