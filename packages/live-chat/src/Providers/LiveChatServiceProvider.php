<?php

declare(strict_types=1);

namespace Capell\LiveChat\Providers;

use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Support\CapellAdminManager;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Frontend\Enums\RenderHookLocation;
use Capell\Frontend\Support\Render\FrontendHookRegistrar;
use Capell\Frontend\Support\Render\RenderHookRegistry;
use Capell\LiveChat\Contracts\LiveChatKnowledgeProvider;
use Capell\LiveChat\Contracts\LiveChatResponder;
use Capell\LiveChat\Contracts\LiveChatWidgetRenderer;
use Capell\LiveChat\Enums\ResourceEnum;
use Capell\LiveChat\Models\LiveChatAIRun;
use Capell\LiveChat\Models\LiveChatAvailabilityException;
use Capell\LiveChat\Models\LiveChatAvailabilityWindow;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatEscalationRule;
use Capell\LiveChat\Models\LiveChatInstallation;
use Capell\LiveChat\Models\LiveChatKnowledgeDocument;
use Capell\LiveChat\Models\LiveChatKnowledgeGap;
use Capell\LiveChat\Models\LiveChatKnowledgeSource;
use Capell\LiveChat\Models\LiveChatMessage;
use Capell\LiveChat\Policies\LiveChatAIRunPolicy;
use Capell\LiveChat\Policies\LiveChatAvailabilityExceptionPolicy;
use Capell\LiveChat\Policies\LiveChatAvailabilityWindowPolicy;
use Capell\LiveChat\Policies\LiveChatConversationPolicy;
use Capell\LiveChat\Policies\LiveChatEscalationRulePolicy;
use Capell\LiveChat\Policies\LiveChatInstallationPolicy;
use Capell\LiveChat\Policies\LiveChatKnowledgeDocumentPolicy;
use Capell\LiveChat\Policies\LiveChatKnowledgeGapPolicy;
use Capell\LiveChat\Policies\LiveChatKnowledgeSourcePolicy;
use Capell\LiveChat\Rendering\BladeLiveChatWidgetRenderer;
use Capell\LiveChat\Support\KnowledgeBaseLiveChatKnowledgeProvider;
use Capell\LiveChat\Support\LocalLiveChatResponder;
use Capell\LiveChat\Support\ManualLiveChatKnowledgeProvider;
use Capell\LiveChat\Support\RenderHooks\RegisterLiveChatWidgetHook;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Override;
use Spatie\LaravelPackageTools\Package;

final class LiveChatServiceProvider extends AbstractPackageServiceProvider
{
    /** @var list<string> */
    private const array MIGRATIONS = [
        '2026_06_13_000001_create_live_chat_conversations_table',
        '2026_06_13_000002_create_live_chat_messages_table',
        '2026_06_13_000003_create_live_chat_availability_tables',
        '2026_06_13_000004_create_live_chat_rules_and_sources_table',
        '2026_06_13_000005_create_live_chat_installations_table',
        '2026_06_13_000006_create_live_chat_ai_source_tables',
    ];

    public static string $name = 'capell-live-chat';

    public static string $packageName = 'capell-app/live-chat';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile('capell-live-chat')
            ->hasTranslations()
            ->hasViews(self::$name)
            ->hasMigrations(self::MIGRATIONS);
    }

    public function registeringPackage(): void
    {
        $this->app->bindIf(LiveChatResponder::class, LocalLiveChatResponder::class);
        $this->app->bindIf(LiveChatWidgetRenderer::class, BladeLiveChatWidgetRenderer::class);
        $this->app->singleton(ManualLiveChatKnowledgeProvider::class);
        $this->app->singleton(KnowledgeBaseLiveChatKnowledgeProvider::class);
        $this->app->tag([
            ManualLiveChatKnowledgeProvider::class,
            KnowledgeBaseLiveChatKnowledgeProvider::class,
        ], LiveChatKnowledgeProvider::TAG);
    }

    public function packageRegistered(): void
    {
        $this->app->booted(function (): void {
            if (! $this->isPackageInstalled()) {
                return;
            }

            $this
                ->registerModels()
                ->registerProtectedTables()
                ->registerPolicies()
                ->registerAdminResources();
        });
    }

    public function bootingPackage(): void
    {
        RateLimiter::for('capell-live-chat', static fn (Request $request): Limit => Limit::perMinute(30)->by($request->ip() ?? 'unknown'));

        RateLimiter::for('capell-live-chat-handoff', static fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip() ?? 'unknown'));
    }

    public function packageBooted(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        Relation::morphMap([
            'live_chat_conversation' => LiveChatConversation::class,
            'live_chat_message' => LiveChatMessage::class,
            'live_chat_ai_run' => LiveChatAIRun::class,
            'live_chat_availability_window' => LiveChatAvailabilityWindow::class,
            'live_chat_availability_exception' => LiveChatAvailabilityException::class,
            'live_chat_escalation_rule' => LiveChatEscalationRule::class,
            'live_chat_installation' => LiveChatInstallation::class,
            'live_chat_knowledge_document' => LiveChatKnowledgeDocument::class,
            'live_chat_knowledge_gap' => LiveChatKnowledgeGap::class,
            'live_chat_knowledge_source' => LiveChatKnowledgeSource::class,
        ], merge: true);

        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        if (config('capell-live-chat.auto_inject', true) !== true) {
            return;
        }

        if ($this->app->bound(FrontendHookRegistrar::class)) {
            resolve(FrontendHookRegistrar::class)->contribute(
                location: RenderHookLocation::BodyEnd,
                extension: new RegisterLiveChatWidgetHook,
                owner: 'capell-app/live-chat',
                key: 'live-chat-widget',
                cacheSafe: false,
            );

            return;
        }

        if ($this->app->bound(RenderHookRegistry::class)) {
            resolve(RenderHookRegistry::class)->registerExtension(
                location: RenderHookLocation::BodyEnd,
                extension: new RegisterLiveChatWidgetHook,
            );
        }
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerModels(): self
    {
        $this->surface()->models([
            LiveChatConversation::class,
            LiveChatMessage::class,
            LiveChatAIRun::class,
            LiveChatAvailabilityWindow::class,
            LiveChatAvailabilityException::class,
            LiveChatEscalationRule::class,
            LiveChatInstallation::class,
            LiveChatKnowledgeDocument::class,
            LiveChatKnowledgeGap::class,
            LiveChatKnowledgeSource::class,
        ]);

        return $this;
    }

    private function registerProtectedTables(): self
    {
        $tables = config('capell-live-chat.tables', []);

        if (! is_array($tables)) {
            return $this;
        }

        foreach ($tables as $tableName) {
            if (! is_string($tableName) || $tableName === '') {
                continue;
            }

            CapellCore::registerProtectedTable(static fn (): string => $tableName);
        }

        return $this;
    }

    private function registerPolicies(): self
    {
        Gate::policy(LiveChatConversation::class, LiveChatConversationPolicy::class);
        Gate::policy(LiveChatAIRun::class, LiveChatAIRunPolicy::class);
        Gate::policy(LiveChatAvailabilityWindow::class, LiveChatAvailabilityWindowPolicy::class);
        Gate::policy(LiveChatAvailabilityException::class, LiveChatAvailabilityExceptionPolicy::class);
        Gate::policy(LiveChatEscalationRule::class, LiveChatEscalationRulePolicy::class);
        Gate::policy(LiveChatInstallation::class, LiveChatInstallationPolicy::class);
        Gate::policy(LiveChatKnowledgeDocument::class, LiveChatKnowledgeDocumentPolicy::class);
        Gate::policy(LiveChatKnowledgeGap::class, LiveChatKnowledgeGapPolicy::class);
        Gate::policy(LiveChatKnowledgeSource::class, LiveChatKnowledgeSourcePolicy::class);

        return $this;
    }

    private function registerAdminResources(): self
    {
        if (! $this->app->bound(CapellAdminManager::class)) {
            return $this;
        }

        foreach (ResourceEnum::cases() as $resource) {
            CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::resource(
                class: $resource->value,
                group: $resource->name,
            ));
        }

        return $this;
    }
}
