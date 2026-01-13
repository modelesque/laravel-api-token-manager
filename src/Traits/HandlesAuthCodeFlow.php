<?php /** @noinspection PhpUnused */

namespace Modelesque\ApiTokenManager\Traits;

use Modelesque\ApiTokenManager\Abstracts\BaseClient;
use Modelesque\ApiTokenManager\Contracts\AuthCodeFlowInterface;
use Modelesque\ApiTokenManager\Exceptions\InvalidConfigException;
use Modelesque\ApiTokenManager\Services\Providers\AuthCodeTokenProvider;

/**
 * @mixin BaseClient
 */
trait HandlesAuthCodeFlow
{
    protected AuthCodeTokenProvider|AuthCodeFlowInterface|null $provider = null;

    /**
     * Get the PKCE token provider to handle Authorization Code Flow process.
     *
     * @param int $retryAttempts
     * @param int $retrySleepMs
     * @param string $redirectUri
     * @return AuthCodeFlowInterface
     * @throws InvalidConfigException
     */
    public function provider(
        int $retryAttempts = 2,
        int $retrySleepMs = 150,
        string $redirectUri = ''
    ): AuthCodeFlowInterface
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