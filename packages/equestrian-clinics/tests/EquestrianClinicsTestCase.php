<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Tests;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\CapellCoreManager;
use Capell\EquestrianClinics\Providers\EquestrianClinicsServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lorisleiva\Actions\ActionServiceProvider;
use Orchestra\Testbench\TestCase;
use Override;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class EquestrianClinicsTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * @param  Application  $app
     * @return class-string[]
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array
    {
        return [
            ActionServiceProvider::class,
            LaravelDataServiceProvider::class,
            EquestrianClinicsServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    #[Override]
    protected function getEnvironmentSetUp(mixed $app): void
    {
        $app->singleton(CapellCoreManager::class);

        $app->make(Repository::class)->set('database.default', 'sqlite');
        $app->make(Repository::class)->set('database.connections.sqlite.database', ':memory:');
        $app->make(Repository::class)->set('database.connections.sqlite.url');
        $app->make(Repository::class)->set('app.key', 'base64:' . base64_encode(str_repeat('x', 32)));

        CapellCore::forcePackageInstalled(EquestrianClinicsServiceProvider::$packageName);
    }
}
