<?php

declare(strict_types=1);

namespace Capell\Payments\Tests;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\CapellCoreManager;
use Capell\CustomerPortal\Providers\CustomerPortalServiceProvider;
use Capell\Payments\Providers\PaymentsServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\ActionServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Override;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class CustomerPortalPaymentsTestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function createPortalPaymentsSite(): int
    {
        return (int) DB::table('sites')->insertGetId([]);
    }

    /**
     * @return array<int, class-string>
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array
    {
        return [
            ActionServiceProvider::class,
            LaravelDataServiceProvider::class,
            CustomerPortalServiceProvider::class,
            PaymentsServiceProvider::class,
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
        Config::set('capell-customer-portal.hash_secret', 'customer-portal-payments-test-secret');

        CapellCore::forcePackageInstalled(CustomerPortalServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(PaymentsServiceProvider::$packageName);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('sites', function (Blueprint $table): void {
            $table->id();
        });

        $this->loadMigrationsFrom(__DIR__ . '/../../customer-portal/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
