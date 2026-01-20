<?php

namespace Modelesque\ApiTokenManager\Tests;

use Dotenv\Dotenv;
use Modelesque\ApiTokenManager\ApiTokenManagerServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** @inheritdoc */
    protected function getApplicationTimezone($app): string
    {
        return 'Europe/Berlin';
    }

    /** @inheritdoc */
    protected function getPackageProviders($app): array
    {
        return [
            ApiTokenManagerServiceProvider::class,
        ];
    }

    /** @inheritdoc */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('apis', require __DIR__ . '/../config/apis.php');

        // Use SQLite in-memory for testing
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /** @inheritdoc */
    protected function setUp(): void
    {
        // ensure .env is loaded before setting up so Pest parses env() in the default config
        Dotenv::createImmutable(__DIR__ . '/..')->load();

        parent::setUp();

        // load and run the migration from the Token Manager package
        $this->loadMigrationsFrom([
            '--database' => 'testbench',
            '--path' => realpath(__DIR__ . '/../database/migrations'),
        ]);
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }
}