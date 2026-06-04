<?php

declare(strict_types=1);

namespace Capell\UrlManager\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use Override;

class UrlManagerTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Lang::addNamespace('capell-url-manager', __DIR__ . '/../resources/lang');
        config(['capell-url-manager' => require __DIR__ . '/../config/capell-url-manager.php']);
    }

    /**
     * @return array<int, class-string>
     */
    #[Override]
    protected function getPackageProviders($app): array
    {
        return [];
    }

    #[Override]
    protected function defineDatabaseMigrations(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
        });

        Schema::create('sites', function (Blueprint $table): void {
            $table->id();
        });

        Schema::create('languages', function (Blueprint $table): void {
            $table->id();
        });

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
