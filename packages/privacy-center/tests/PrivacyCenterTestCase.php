<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Tests;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\CapellCoreManager;
use Capell\PrivacyCenter\Providers\PrivacyCenterServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\ActionServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Override;
use Spatie\LaravelData\LaravelDataServiceProvider;

require_once __DIR__ . '/autoload.php';

class PrivacyCenterTestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function createPrivacyCenterSite(): int
    {
        return (int) DB::table('sites')->insertGetId([]);
    }

    /**
     * @return class-string[]
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array
    {
        return [
            ActionServiceProvider::class,
            LaravelDataServiceProvider::class,
            PrivacyCenterServiceProvider::class,
        ];
    }

    #[Override]
    protected function defineEnvironment(mixed $app): void
    {
        $app->singleton(CapellCoreManager::class);

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('database.connections.sqlite.url');
        Config::set('app.key', 'base64:' . base64_encode(str_repeat('p', 32)));
        Config::set('capell-privacy-center.hash_secret', 'privacy-center-test-secret');

        CapellCore::forcePackageInstalled(PrivacyCenterServiceProvider::$packageName);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('sites', function (Blueprint $table): void {
            $table->id();
        });

        Schema::create('privacy_center_test_subjects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
