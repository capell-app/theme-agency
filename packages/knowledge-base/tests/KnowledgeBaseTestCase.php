<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;
use Override;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class KnowledgeBaseTestCase extends TestCase
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
            LaravelDataServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * @param  Application  $app
     */
    #[Override]
    protected function getEnvironmentSetUp(mixed $app): void
    {
        config()->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('database.connections.sqlite.url');
        config()->set('capell-knowledge-base.public_path_prefix', 'docs');
        config()->set('capell-knowledge-base.default_search_weight', 50);
        config()->set('capell-knowledge-base.feedback.hash_salt', 'knowledge-base-test');
    }
}
