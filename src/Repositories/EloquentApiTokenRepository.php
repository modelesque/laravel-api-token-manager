<?php

namespace Modelesque\ApiTokenManager\Repositories;

use Modelesque\ApiTokenManager\Abstracts\BaseRepository;
use Modelesque\ApiTokenManager\Contracts\ApiTokenRepositoryInterface;
use Modelesque\ApiTokenManager\Models\ApiToken;
use JetBrains\PhpStorm\Pure;

/**
 * @see https://asperbrothers.com/blog/implement-repository-pattern-in-laravel/
 */
class EloquentApiTokenRepository extends BaseRepository implements ApiTokenRepositoryInterface
{
    #[Pure]
    public function __construct(ApiToken $model)
    {
        parent::__construct($model);
    }

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