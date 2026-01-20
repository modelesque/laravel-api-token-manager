<?php

namespace Modelesque\ApiTokenManager\Services\Providers;

use Modelesque\ApiTokenManager\Abstracts\BaseTokenProvider;
use Modelesque\ApiTokenManager\Contracts\OAuth2TokenProviderInterface;
use Modelesque\ApiTokenManager\Enums\ApiTokenGrantType;
use Modelesque\ApiTokenManager\Models\ApiToken;

class ClientCredentialsTokenProvider extends BaseTokenProvider implements OAuth2TokenProviderInterface
{
    /** @inheritdoc */
    public function requestToken(?ApiToken $token): array
    {
        $authorization = implode(':', [$this->clientId(), $this->clientSecret()]);
        $headers = ['Authorization' => 'Basic ' . base64_encode($authorization)];

        $response = $this->postRequestForToken(
            ['grant_type' => ApiTokenGrantType::CLIENT_CREDENTIALS->value],
            $headers
        );

        return $this->normalizeResponse($response);
    }

    /**
     * After requesting a token from the API, normalize its response so the
     * data can be saved as an ApiToken.
     *
     * @param array $response
     * @return array
     */
    protected function normalizeResponse(array $response): array
    {
        $token = $response['access_token'] ?? $response['token'] ?? null;
        $tokenType = strtolower($response['token_type'] ?? 'bearer');
        $expiresAt = $this->normalizeValueForExpiresIn(
            $response['expires_in'] ?? $response['expires_at'] ?? $response['expires'] ?? null
        );

        return array_filter([
            'expires_at' => $expiresAt ?: false,
            'grant_type' => ApiTokenGrantType::CLIENT_CREDENTIALS->value,
            'meta' => $response,
            'token' => $token ?? false,
            'token_type' => $tokenType,
        ]);
    }
}