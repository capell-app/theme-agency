<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Tests;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\CapellCoreManager;
use Capell\KnowledgeBase\Providers\KnowledgeBaseServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lorisleiva\Actions\ActionServiceProvider;
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
            ActionServiceProvider::class,
            LaravelDataServiceProvider::class,
            KnowledgeBaseServiceProvider::class,
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
        $app->singleton(CapellCoreManager::class);

        Config::set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('database.connections.sqlite.url');
        Config::set('capell-knowledge-base.public_path_prefix', 'docs');
        Config::set('capell-knowledge-base.default_search_weight', 50);
        Config::set('capell-knowledge-base.feedback.hash_salt', 'knowledge-base-test');
        CapellCore::forcePackageInstalled(KnowledgeBaseServiceProvider::$packageName);
    }
}
