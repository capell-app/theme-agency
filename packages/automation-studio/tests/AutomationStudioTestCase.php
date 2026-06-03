<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Tests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use RuntimeException;

abstract class AutomationStudioTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->app instanceof Application) {
            throw new RuntimeException('Laravel application is not available for Automation Studio tests.');
        }

        $this->app->make(Translator::class)->addNamespace('capell-automation-studio', __DIR__ . '/../resources/lang');
        $this->createSitesTable();
        $this->runAutomationStudioMigrations();
    }

    /**
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $key = 'base64:' . base64_encode(str_repeat('a', 32));

        $app->make(Repository::class)->set('app', [
            ...($app->make(Repository::class)->get('app') ?? []),
            'key' => $key,
            'cipher' => 'AES-256-CBC',
        ]);
        $app->make(Repository::class)->set('app.key', $key);
        $app->make(Repository::class)->set('app.cipher', 'AES-256-CBC');
        $app->make(Repository::class)->set('database.default', 'testing');
        $app->make(Repository::class)->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app->make(Repository::class)->set('data', require __DIR__ . '/../../../vendor/spatie/laravel-data/config/data.php');
        $app->make(Repository::class)->set('queue.default', 'sync');
    }

    private function createSitesTable(): void
    {
        if (Schema::hasTable('sites')) {
            return;
        }

        Schema::create('sites', function (Blueprint $table): void {
            $table->id();
        });
    }

    private function runAutomationStudioMigrations(): void
    {
        $migrations = [
            __DIR__ . '/../database/migrations/2026_05_31_170000_01_create_automation_rules_table.php',
            __DIR__ . '/../database/migrations/2026_05_31_170000_02_create_automation_runs_table.php',
        ];

        foreach ($migrations as $migrationPath) {
            $migration = require $migrationPath;
            $migration->up();
        }
    }
}
