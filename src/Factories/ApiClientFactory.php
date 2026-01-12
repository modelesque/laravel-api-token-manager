<?php

namespace Modelesque\ApiTokenManager\Factories;

use Modelesque\ApiTokenManager\Enums\ApiTokenGrantType;
use Modelesque\ApiTokenManager\Exceptions\PKCEAuthorizationRequiredException;
use Modelesque\ApiTokenManager\Services\TokenManager;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class ApiClientFactory
{
    public function __construct(
        protected TokenManager $tokenManager
    ) {}

    /**
     * Return a configured pending request (Illuminate\Http\Client\PendingRequest)
     *
     * @param string $provider The API's array key in /config/apis.php (e.g. "spotify")
     * @param string $account
     * @param string $grantType
     * @return PendingRequest
     * @throws ConnectionException
     * @throws PKCEAuthorizationRequiredException
     */
    public function make(string $provider, string $account, string $grantType): PendingRequest
    {
        $config = config("apis.providers.$provider");
        if (! isset($config[$account])) {
            throw new InvalidArgumentException(sprintf(
                "Keys of '%s' and/or '%s' not found in 'providers' array in %s",
                $provider,
                $account,
                config_path('apis.php')
            ));
        }

        $client = Http::baseUrl($config['base_url'] ?? '')->acceptJson();

        if (ApiTokenGrantType::isOAuth2($grantType)) {
            $token = $this->tokenManager->getToken(
                $provider,
                $account,
                $grantType
            );

            if ($token) {
                $client = $client->withToken($token);
            }
        }

        return $client;
    }
}