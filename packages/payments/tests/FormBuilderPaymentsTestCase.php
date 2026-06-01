<?php

declare(strict_types=1);

namespace Capell\Payments\Tests;

use Capell\Payments\Providers\PaymentsServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\ActionServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Override;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class FormBuilderPaymentsTestCase extends OrchestraTestCase
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
        ];
    }

    #[Override]
    protected function defineEnvironment(mixed $app): void
    {
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('database.connections.sqlite.url');
        Config::set('app.key', 'base64:' . base64_encode(str_repeat('x', 32)));
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('sites', function (Blueprint $table): void {
            $table->id();
        });

        $this->loadMigrationsFrom(__DIR__ . '/../../form-builder/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
