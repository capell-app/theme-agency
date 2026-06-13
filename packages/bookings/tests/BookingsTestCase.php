<?php

declare(strict_types=1);

namespace Capell\Bookings\Tests;

use Capell\Bookings\Providers\BookingsServiceProvider;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\CapellCoreManager;
use Capell\CustomerPortal\Providers\CustomerPortalServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\ActionServiceProvider;
use Orchestra\Testbench\TestCase;
use Override;
use Spatie\LaravelData\LaravelDataServiceProvider;

class BookingsTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('sites')) {
            Schema::create('sites', function (Blueprint $table): void {
                $table->id();
            });
        }

        $this->loadMigrationsFrom(__DIR__ . '/../../customer-portal/database/migrations');
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
            CustomerPortalServiceProvider::class,
            BookingsServiceProvider::class,
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
        $app->make(Repository::class)->set('capell-customer-portal.hash_secret', 'customer-portal-test-secret');

        CapellCore::forcePackageInstalled(CustomerPortalServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(BookingsServiceProvider::$packageName);
    }
}
