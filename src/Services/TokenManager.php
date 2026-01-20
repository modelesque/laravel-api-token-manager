<?php /** @noinspection PhpUndefinedFunctionInspection */

namespace Modelesque\ApiTokenManager\Services;

use Modelesque\ApiTokenManager\Contracts\ApiTokenRepositoryInterface;
use Modelesque\ApiTokenManager\Contracts\OAuth2TokenProviderInterface;
use Modelesque\ApiTokenManager\Enums\ApiTokenGrantType;
use Modelesque\ApiTokenManager\Exceptions\InvalidConfigException;
use Modelesque\ApiTokenManager\Exceptions\AuthCodeFlowRequiredException;
use Modelesque\ApiTokenManager\Helpers\Config;
use Modelesque\ApiTokenManager\Services\Providers\ClientCredentialsTokenProvider;
use Modelesque\ApiTokenManager\Services\Providers\AuthCodeTokenProvider;
use Illuminate\Http\Client\ConnectionException;
use RuntimeException;

class TokenManager
{
    public function __construct(
        protected ApiTokenRepositoryInterface $repository
    ) {}

    /**
     * Returns a valid access token for $provider. Refreshes if expired or missing.
     *
     * @param string $configKey The API's array key in /config/apis.php (e.g. "spotify")
     * @param string $account
     * @param string $grantType
     * @return string|null
     * @throws ConnectionException
     * @throws AuthCodeFlowRequiredException
     * @throws InvalidConfigException
     */
    public function getToken(string $configKey, string $account, string $grantType): ?string
    {
        // if saved token exists and won't expire within the next 30 seconds
        $savedToken = $this->repository->getSavedToken(
            $configKey,
            $account,
            $grantType
        );
        if ($savedToken && $savedToken->isValid()) {
            return $savedToken->token;
        }

        // ensure we have a config array for the API that needs a token
        Config::getProvider($configKey);

        // no saved token, so get a new one
        $provider = $this->makeProvider($configKey, $account, $grantType);
        $payload = $provider->requestToken($savedToken);

        // save the returned token to the db
        $saved = $this->repository->saveToken(
            $configKey,
            $account,
            $grantType,
            $payload
        );

        return $saved->token;
    }

    /**
     * Return a new TokenProvider object with methods to handle new tokens.
     *
     * @param string $configKey
     * @param string $account
     * @param string $grantType
     * @return OAuth2TokenProviderInterface
     * @throws InvalidConfigException
     */
    public function makeProvider(string $configKey, string $account, string $grantType): OAuth2TokenProviderInterface
    {
        return match ($grantType) {
            ApiTokenGrantType::AUTHORIZATION_CODE->value => new AuthCodeTokenProvider($configKey, $account),
            ApiTokenGrantType::CLIENT_CREDENTIALS->value => new ClientCredentialsTokenProvider($configKey,$account),
            default => throw new RuntimeException("Unsupported token grant type: $grantType"),
        };
    }
}