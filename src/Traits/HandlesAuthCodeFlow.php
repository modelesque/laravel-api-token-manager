<?php

namespace Modelesque\ApiTokenManager\Traits;

use Modelesque\ApiTokenManager\Abstracts\BaseClient;
use Modelesque\ApiTokenManager\Contracts\AuthCodeTokenProviderInterface;
use Modelesque\ApiTokenManager\Services\Providers\AuthCodeTokenProvider;

/**
 * @mixin BaseClient
 */
trait HandlesAuthCodeFlow
{
    protected AuthCodeTokenProvider|null $provider = null;

    /**
     * Get the PKCE token provider to handle Authorization Code Flow process.
     *
     * @param int $retryAttempts
     * @param int $retrySleepMs
     * @param string $redirectUri
     * @return AuthCodeTokenProviderInterface
     */
    public function pkce(int $retryAttempts = 2, int $retrySleepMs = 150, string $redirectUri = ''): AuthCodeTokenProviderInterface
    {
        if ($this->provider) {
            return $this->provider;
        }

        $this->provider = new AuthCodeTokenProvider(
            $this->configKey,
            $this->account,
            $retryAttempts,
            $retrySleepMs,
            $redirectUri
        );

        return $this->provider;
    }
}