<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;

/**
 * @extends TestCase<never>
 */
abstract class AutomationStudioTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['translator']->addNamespace('capell-automation-studio', __DIR__ . '/../resources/lang');
        $this->createSitesTable();
        $this->runAutomationStudioMigrations();
    }

    /**
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $key = 'base64:' . base64_encode(str_repeat('a', 32));

        $app['config']->set('app', [
            ...($app['config']->get('app') ?? []),
            'key' => $key,
            'cipher' => 'AES-256-CBC',
        ]);
        $app['config']->set('app.key', $key);
        $app['config']->set('app.cipher', 'AES-256-CBC');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('data', require __DIR__ . '/../../../vendor/spatie/laravel-data/config/data.php');
        $app['config']->set('queue.default', 'sync');
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
