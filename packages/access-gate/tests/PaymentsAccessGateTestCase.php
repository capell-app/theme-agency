<?php

declare(strict_types=1);

namespace Capell\AccessGate\Tests;

use Capell\AccessGate\Providers\AccessGateServiceProvider;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\CapellCoreManager;
use Capell\Payments\Providers\PaymentsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lorisleiva\Actions\ActionServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Override;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class PaymentsAccessGateTestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, class-string>
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array
    {
        return [
            ActionServiceProvider::class,
            LaravelDataServiceProvider::class,
            PaymentsServiceProvider::class,
            AccessGateServiceProvider::class,
        ];
    }

    #[Override]
    protected function defineEnvironment(mixed $app): void
    {
        $app->singleton(CapellCoreManager::class);

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('database.connections.sqlite.url');
        Config::set('app.key', 'base64:' . base64_encode(str_repeat('x', 32)));

        CapellCore::forcePackageInstalled(PaymentsServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(AccessGateServiceProvider::$packageName);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../payments/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
