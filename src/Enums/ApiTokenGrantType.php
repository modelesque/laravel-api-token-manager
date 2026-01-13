<?php

namespace Modelesque\ApiTokenManager\Enums;

enum ApiTokenGrantType: string
{
    case AUTHORIZATION_CODE = 'authorization_code';
    case REFRESH_TOKEN = 'refresh_token';
    case CLIENT_CREDENTIALS = 'client_credentials';

    /** @return string The read-friendly label for a grant type. */
    public function label(): string
    {
        return match ($this) {
            self::AUTHORIZATION_CODE => 'PKCE Authorization Code Flow',
            self::REFRESH_TOKEN => 'PKCE Refresh Token',
            self::CLIENT_CREDENTIALS => 'Client Credentials',
        };
    }

    /**
     * If the grant type is related to OAuth 2.0.
     *
     * @param mixed $authType
     * @return bool
     */
    public static function isOAuth2(mixed $authType): bool
    {
        return $authType === self::AUTHORIZATION_CODE->value ||
            $authType === self::CLIENT_CREDENTIALS->value ||
            $authType === self::REFRESH_TOKEN->value;
    }

    /**
     * If the grant type incorporates a refresh system, whereby a 'refresh_token' is
     * stored to refresh an expired token.
     *
     * @param mixed $authType
     * @return bool
     */
    public static function hasRefreshToken(mixed $authType): bool
    {
        return $authType === self::AUTHORIZATION_CODE->value ||
            $authType === self::REFRESH_TOKEN->value;
    }
}