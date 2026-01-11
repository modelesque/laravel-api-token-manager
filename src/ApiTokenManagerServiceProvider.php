<?php

namespace Modelesque\ApiTokenManager;

use Modelesque\ApiTokenManager\Contracts\ApiTokenRepositoryInterface;
use Modelesque\ApiTokenManager\Contracts\PKCEAuthCodeFlowInterface;
use Modelesque\ApiTokenManager\Repositories\EloquentApiTokenRepository;
use Modelesque\ApiTokenManager\Services\Providers\PKCEAuthTokenProvider;
use Illuminate\Support\ServiceProvider;

class ApiTokenManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TokenManager::class);
        $this->app->bind(PKCEAuthCodeFlowInterface::class, PKCEAuthTokenProvider::class);
        $this->app->bind(ApiTokenRepositoryInterface::class, EloquentApiTokenRepository::class);
    }

    public function boot(): void
    {
        // let hosts run this migration via:
        // php artisan vendor:publish --tag=migrations
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'migrations');
        }
    }
}