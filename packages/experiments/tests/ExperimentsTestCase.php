<?php

declare(strict_types=1);

namespace Capell\Experiments\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lorisleiva\Actions\ActionServiceProvider;
use Orchestra\Testbench\TestCase;
use Override;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class ExperimentsTestCase extends TestCase
{
    use RefreshDatabase;

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
        ];
    }

    /**
     * @param  Application  $app
     */
    #[Override]
    protected function getEnvironmentSetUp(mixed $app): void
    {
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('database.connections.sqlite.url');
        Config::set('capell-experiments.tables.experiments', 'experiments');
        Config::set('capell-experiments.tables.variants', 'experiment_variants');
        Config::set('capell-experiments.tables.allocations', 'experiment_allocations');
        Config::set('capell-experiments.tables.goals', 'experiment_goals');
        Config::set('capell-experiments.tables.goal_events', 'experiment_goal_events');
        Config::set('capell-experiments.tables.audience_rules', 'experiment_audience_rules');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
