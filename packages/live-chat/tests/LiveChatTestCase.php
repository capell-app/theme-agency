<?php

declare(strict_types=1);

namespace Capell\LiveChat\Tests;

use Capell\AIOrchestrator\Providers\AIOrchestratorServiceProvider;
use Capell\Contacts\Providers\ContactsServiceProvider;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\CapellCoreManager;
use Capell\Frontend\Support\Render\RenderHookRegistry;
use Capell\KnowledgeBase\Providers\KnowledgeBaseServiceProvider;
use Capell\LiveChat\Models\LiveChatInstallation;
use Capell\LiveChat\Providers\LiveChatServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\ActionServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Override;
use Spatie\LaravelData\LaravelDataServiceProvider;

class LiveChatTestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function createLiveChatSite(): int
    {
        return (int) DB::table('sites')->insertGetId([]);
    }

    /**
     * @param  list<string>  $allowedDomains
     */
    protected function createLiveChatInstallation(?int $siteId = null, array $allowedDomains = ['example.test']): LiveChatInstallation
    {
        return LiveChatInstallation::query()->create([
            'site_id' => $siteId ?? $this->createLiveChatSite(),
            'name' => 'Example chat',
            'allowed_domains' => $allowedDomains,
            'timezone' => 'Europe/London',
            'widget_settings' => [
                'brand_name' => 'Example support',
            ],
        ]);
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
            ContactsServiceProvider::class,
            KnowledgeBaseServiceProvider::class,
            AIOrchestratorServiceProvider::class,
            LiveChatServiceProvider::class,
        ];
    }

    #[Override]
    protected function defineEnvironment(mixed $app): void
    {
        $app->singleton(CapellCoreManager::class);
        $app->singleton(RenderHookRegistry::class);

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('database.connections.sqlite.url');
        Config::set('app.key', 'base64:' . base64_encode(str_repeat('x', 32)));
        Config::set('capell-contacts.hash_secret', 'contacts-test-secret');
        Config::set('capell-live-chat.hash_secret', 'live-chat-test-secret');
        Config::set('capell-live-chat.default_site_id', 1);

        CapellCore::forcePackageInstalled(ContactsServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(AIOrchestratorServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(KnowledgeBaseServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(LiveChatServiceProvider::$packageName);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('sites', function (Blueprint $table): void {
            $table->id();
        });

        $this->loadMigrationsFrom(__DIR__ . '/../../contacts/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../../knowledge-base/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
