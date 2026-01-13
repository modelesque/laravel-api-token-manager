<?php

namespace Modelesque\ApiTokenManager;

use Modelesque\ApiTokenManager\Contracts\ApiTokenRepositoryInterface;
use Modelesque\ApiTokenManager\Contracts\PKCEAuthCodeFlowInterface;
use Modelesque\ApiTokenManager\Factories\ApiClientFactory;
use Modelesque\ApiTokenManager\Repositories\EloquentApiTokenRepository;
use Modelesque\ApiTokenManager\Services\Providers\AuthCodeFlowTokenProvider;
use Illuminate\Support\ServiceProvider;

class ApiTokenManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ApiClientFactory::class);
        $this->app->singleton(TokenManager::class);
        $this->app->bind(PKCEAuthCodeFlowInterface::class, AuthCodeFlowTokenProvider::class);
        $this->app->bind(ApiTokenRepositoryInterface::class, EloquentApiTokenRepository::class);

        // provide a fallback config that can be overwritten by hosts using this package
        $this->mergeConfigFrom(__DIR__ . '/../config/apis.php', 'apis');
    }

    public function boot(): void
    {
        // Load migrations automatically
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // let hosts run this migration via:
        // php artisan vendor:publish --tag=migrations
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'migrations');
        }
    }
}