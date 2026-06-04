<?php

declare(strict_types=1);

namespace Capell\Comments\Providers;

use Capell\Admin\Data\Extensions\ExtensionManagementSurfaceData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Comments\Actions\RegisterDefaultCommentablesAction;
use Capell\Comments\Console\Commands\InstallCommentsCommand;
use Capell\Comments\Console\Commands\PruneCommentPrivacyDataCommand;
use Capell\Comments\Contracts\CommentSpamProvider;
use Capell\Comments\Events\CommentCreated;
use Capell\Comments\Filament\Settings\CommentSettingsSchema;
use Capell\Comments\Listeners\NotifyModeratorsOfNewComment;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Models\CommentModerationEvent;
use Capell\Comments\Models\CommentReaction;
use Capell\Comments\Models\CommentToken;
use Capell\Comments\Policies\CommentAuthorPolicy;
use Capell\Comments\Policies\CommentPolicy;
use Capell\Comments\Settings\CommentSettings;
use Capell\Comments\Support\CommentableRegistry;
use Capell\Comments\Support\Spam\ConfiguredCommentSpamProvider;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Override;
use Spatie\LaravelPackageTools\Package;

class CommentsServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-comments';

    public static string $packageName = 'capell-app/comments';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile('capell-comments')
            ->hasTranslations()
            ->hasViews(self::$name)
            ->hasMigrations([
                '2026_05_24_000001_create_comment_authors_table',
                '2026_05_24_000002_create_comments_table',
                '2026_05_24_000003_create_comment_tokens_table',
                '2026_05_24_000004_create_comment_moderation_events_table',
                '2026_05_24_000006_add_reply_notification_opt_out_to_comment_authors_table',
                '2026_05_24_000007_create_comment_reactions_table',
            ]);
    }

    public function registeringPackage(): void
    {
        $this->app->singleton(CommentableRegistry::class);
        $this->app->bind(CommentSpamProvider::class, ConfiguredCommentSpamProvider::class);
        $this->app->register(AdminServiceProvider::class);
        $this->app->register(FrontendServiceProvider::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommentsCommand::class,
                PruneCommentPrivacyDataCommand::class,
            ]);
        }
    }

    public function packageRegistered(): void
    {
        $this->app->booted(function (): void {
            if (! $this->isPackageInstalled()) {
                return;
            }

            $this
                ->registerRoutes()
                ->registerModels()
                ->registerSettings()
                ->registerProtectedTables()
                ->registerCommentables()
                ->registerListeners()
                ->registerRateLimits();
        });
    }

    public function packageBooted(): void
    {
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(CommentAuthor::class, CommentAuthorPolicy::class);

        if (! $this->isPackageInstalled()) {
            return;
        }

        Relation::morphMap([
            'comment' => Comment::class,
            'comment_author' => CommentAuthor::class,
        ], merge: true);
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(static::$packageName);
    }

    private function registerModels(): self
    {
        CapellCore::registerModels([
            Comment::class,
            CommentAuthor::class,
            CommentToken::class,
            CommentModerationEvent::class,
            CommentReaction::class,
        ]);

        return $this;
    }

    private function registerRoutes(): self
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        return $this;
    }

    private function registerSettings(): self
    {
        /** @var SettingsSchemaRegistry $registry */
        $registry = $this->app->make(SettingsSchemaRegistry::class);
        $registry->registerSettingsClass('comments', CommentSettings::class);
        $registry->register('comments', CommentSettingsSchema::class);
        CapellAdmin::registerExtensionManagementSurface(ExtensionManagementSurfaceData::settings(
            packageName: self::$packageName,
            label: 'capell-comments::package.name',
            settingsGroup: 'comments',
            icon: 'heroicon-o-chat-bubble-left-right',
        ));

        return $this;
    }

    private function registerProtectedTables(): self
    {
        foreach (['comment_authors', 'comments', 'comment_tokens', 'comment_moderation_events', 'comment_reactions'] as $table) {
            CapellCore::registerProtectedTable(static fn (): string => $table);
        }

        return $this;
    }

    private function registerCommentables(): self
    {
        RegisterDefaultCommentablesAction::run();

        return $this;
    }

    private function registerListeners(): self
    {
        Event::listen(CommentCreated::class, NotifyModeratorsOfNewComment::class);

        return $this;
    }

    private function registerRateLimits(): self
    {
        RateLimiter::for('comments-verification', static fn (Request $request): Limit => Limit::perMinute(12)->by($request->ip() ?? 'unknown'));

        return $this;
    }
}
