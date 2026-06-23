<?php

declare(strict_types=1);

namespace Capell\AccessGate\Tests;

use Capell\AccessGate\Providers\AccessGateServiceProvider;
use Capell\AccessGate\Tests\Support\FakePageCacheMiddleware;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Override;
use Sinnbeck\DomAssertions\DomAssertionsServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders(mixed $app): array
    {
        return [
            AccessGateServiceProvider::class,
            DomAssertionsServiceProvider::class,
        ];
    }

    #[Override]
    protected function defineEnvironment(mixed $app): void
    {
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('database.connections.sqlite.url');
        Config::set('app.key', 'base64:' . base64_encode(str_repeat('x', 32)));

        $app->make(Repository::class)->set('access-gate.middleware.page_cache_aliases', [
            'frontend.cache',
            FakePageCacheMiddleware::class,
        ]);
        $app->make(Repository::class)->set('access-gate.status_endpoint_enabled', true);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
