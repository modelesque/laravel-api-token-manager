<?php

namespace Modelesque\ApiTokenManager\Contracts;

use Modelesque\ApiTokenManager\Exceptions\AuthCodeFlowRequiredException;
use Modelesque\ApiTokenManager\Models\ApiToken;
use Illuminate\Http\Client\ConnectionException;
use Modelesque\ApiTokenManager\Services\Providers\ClientCredentialsTokenProvider;
use Modelesque\ApiTokenManager\Services\Providers\AuthCodeFlowTokenProvider;

/**
 * @see ClientCredentialsTokenProvider
 * @see AuthCodeFlowTokenProvider
 */
interface OAuth2TokenProviderInterface
{
    /**
     * Request an auth token from the API provider.
     *
     * @param ApiToken|null $token
     * @return array
     * @throws ConnectionException
     * @throws AuthCodeFlowRequiredException
     */
    public function requestToken(?ApiToken $token): array;
}