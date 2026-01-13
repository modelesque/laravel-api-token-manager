<?php

namespace Modelesque\ApiTokenManager\Traits;

use Modelesque\ApiTokenManager\Abstracts\BaseClient;
use Modelesque\ApiTokenManager\Contracts\PKCEAuthCodeFlowInterface;
use Modelesque\ApiTokenManager\Services\Providers\PKCEAuthTokenProvider;

/**
 * @mixin BaseClient
 */
trait HandlesPKCEAuthCodeFlow
{
    protected PKCEAuthTokenProvider|null $provider = null;

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

        $this->provider = new PKCEAuthTokenProvider(
            $this->configKey,
            $this->account,
            $retryAttempts,
            $retrySleepMs,
            $redirectUri
        );

        return $this->provider;
    }
}