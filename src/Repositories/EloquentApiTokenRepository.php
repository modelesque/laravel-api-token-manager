<?php

namespace Modelesque\ApiTokenManager\Repositories;

use Modelesque\ApiTokenManager\Abstracts\BaseRepository;
use Modelesque\ApiTokenManager\Contracts\ApiTokenRepositoryInterface;
use Modelesque\ApiTokenManager\Models\ApiToken;

class EloquentApiTokenRepository implements ApiTokenRepositoryInterface
{
    public function __construct(protected ApiToken $model) {}

    /** @inheritDoc */
    public function getSavedToken(string $provider, string $account): ?ApiToken
    {
        return $this->model::query()
            ->where([
                ['provider', $provider],
                ['account', $account]
            ])
            ->first();
    }

    /** @inheritDoc */
    public function saveToken(string $provider, string $account, array $payload): ApiToken
    {
        $payload['provider'] = $provider;

        return $this->model::query()->updateOrCreate(
            [
                'provider' => $provider,
                'account' => $account,
            ],
            $payload
        );
    }

    /** @inheritDoc */
    public function deleteSavedToken(string $provider, string $account): void
    {
        $this->model::query()
            ->where([
                ['provider', $provider],
                ['account', $account]
            ])->delete();
    }
}