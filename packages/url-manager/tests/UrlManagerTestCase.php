<?php

declare(strict_types=1);

namespace Capell\UrlManager\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
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
    protected function getPackageProviders(mixed $app): array
    {
        return [];
    }

    #[Override]
    protected function defineEnvironment(mixed $app): void
    {
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('database.connections.sqlite.url');
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

        DB::table('users')->insert([
            ['id' => 30],
        ]);
        DB::table('sites')->insert([
            ['id' => 1],
            ['id' => 10],
            ['id' => 12],
        ]);
        DB::table('languages')->insert([
            ['id' => 1],
            ['id' => 2],
            ['id' => 20],
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
