<?php

namespace Modelesque\ApiTokenManager\Factories;

use Modelesque\ApiTokenManager\Enums\ApiTokenGrantType;
use Modelesque\ApiTokenManager\Exceptions\InvalidConfigException;
use Modelesque\ApiTokenManager\Exceptions\AuthCodeFlowRequiredException;
use Modelesque\ApiTokenManager\Helpers\Config;
use Modelesque\ApiTokenManager\Services\TokenManager;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

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
     * @throws AuthCodeFlowRequiredException
     * @throws InvalidConfigException
     */
    public function make(string $provider, string $account, string $grantType): PendingRequest
    {
        $config = Config::getRequired($provider, 'base_url', $account);

        $client = Http::baseUrl($config)->acceptJson();

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