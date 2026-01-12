<?php

namespace Modelesque\ApiTokenManager\Repositories;

use Modelesque\ApiTokenManager\Abstracts\BaseRepository;
use Modelesque\ApiTokenManager\Contracts\ApiTokenRepositoryInterface;
use Modelesque\ApiTokenManager\Models\ApiToken;

class EloquentApiTokenRepository implements ApiTokenRepositoryInterface
{
    public function __construct(protected ApiToken $model) {}

    /** @inheritDoc */
    public function getSavedToken(string $provider, string $account, string $grantType): ?ApiToken
    {
        return $this->model::query()
            ->where([
                ['provider', $provider],
                ['account', $account],
                ['grant_type', $grantType],
            ])
            ->first();
    }

    /** @inheritDoc */
    public function saveToken(string $provider, string $account, string $grantType, array $payload): ApiToken
    {
        $payload['provider'] = $provider;

        return $this->model::query()->updateOrCreate(
            [
                'provider' => $provider,
                'account' => $account,
                'grant_type' => $grantType,
            ],
            $payload
        );
    }

    /** @inheritDoc */
    public function deleteSavedToken(string $provider, string $account, string $grantType): void
    {
        $this->model::query()
            ->where([
                ['provider', $provider],
                ['account', $account],
                ['grant_type', $grantType],
            ])->delete();
    }
}