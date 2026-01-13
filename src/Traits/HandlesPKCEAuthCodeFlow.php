<?php

namespace Modelesque\ApiTokenManager\Traits;

use Modelesque\ApiTokenManager\Abstracts\BaseClient;
use Modelesque\ApiTokenManager\Contracts\PKCEAuthCodeFlowInterface;
use Modelesque\ApiTokenManager\Services\Providers\AuthCodeFlowTokenProvider;

/**
 * @mixin BaseClient
 */
trait HandlesAuthCodeFlow
{
    protected AuthCodeFlowTokenProvider|null $provider = null;

    /**
     * Get the PKCE token provider to handle Authorization Code Flow process.
     *
     * @param int $retryAttempts
     * @param int $retrySleepMs
     * @param string $redirectUri
     * @return PKCEAuthCodeFlowInterface
     */
    public function pkce(int $retryAttempts = 2, int $retrySleepMs = 150, string $redirectUri = ''): PKCEAuthCodeFlowInterface
    {
        if ($this->provider) {
            return $this->provider;
        }

        $this->provider = new AuthCodeFlowTokenProvider(
            $this->configKey,
            $this->account,
            $retryAttempts,
            $retrySleepMs,
            $redirectUri
        );

        return $this->provider;
    }
}