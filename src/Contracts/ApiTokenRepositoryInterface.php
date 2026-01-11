<?php
namespace Modelesque\ApiTokenManager\Contracts;

use Modelesque\ApiTokenManager\Models\ApiToken;
use Modelesque\ApiTokenManager\Repositories\EloquentApiTokenRepository;

/**
 * @see EloquentApiTokenRepository
 */
interface ApiTokenRepositoryInterface
{
    /**
     * @param string $provider The API (e.g. "spotify").
     * @param string $account The account type (e.g. "public").
     * @return ApiToken|null
     */
    public function getSavedToken(string $provider, string $account): ?ApiToken;

    /**
     * @param string $provider The API (e.g. "spotify").
     * @param string $account The account type (e.g. "public").
     * @param array $payload The data to be saved for this token.
     * @return ApiToken
     */
    public function saveToken(string $provider, string $account, array $payload): ApiToken;

    /**
     * @param string $provider The API (e.g. "spotify").
     * @param string $account The account type (e.g. "public").
     */
    public function deleteSavedToken(string $provider, string $account): void;
}