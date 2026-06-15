<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Providers;

use Capell\BlockLibrary\Contracts\BlockDefinitionProvider;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\SocialFeeds\Blocks\SocialFeedBlockDefinitionProvider;
use Capell\SocialFeeds\Console\Commands\SyncSocialFeedsCommand;
use Capell\SocialFeeds\Contracts\SocialFeedHostResolver;
use Capell\SocialFeeds\Contracts\SocialFeedProviderProvider;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Capell\SocialFeeds\Models\SocialFeedItem;
use Capell\SocialFeeds\Models\SocialFeedOAuthState;
use Capell\SocialFeeds\Support\DefaultSocialFeedProviderProvider;
use Capell\SocialFeeds\Support\DnsSocialFeedHostResolver;
use Capell\SocialFeeds\Support\SocialFeedProviderRegistry;
use Illuminate\Console\Scheduling\Schedule;
use Spatie\LaravelPackageTools\Package;

final class SocialFeedsServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-social-feeds';

    public static string $packageName = 'capell-app/social-feeds';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile()
            ->hasViews(self::$name)
            ->hasTranslations()
            ->hasCommands([
                SyncSocialFeedsCommand::class,
            ])
            ->hasMigrations([
                '2026_06_04_000001_create_social_feed_connections_table',
                '2026_06_04_000002_create_social_feed_items_table',
                '2026_06_04_000003_create_social_feed_oauth_states_table',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->register(AdminServiceProvider::class);

        $this->app->singleton(SocialFeedHostResolver::class, DnsSocialFeedHostResolver::class);
        $this->app->singleton(SocialFeedProviderRegistry::class);
        $this->app->tag([DefaultSocialFeedProviderProvider::class], SocialFeedProviderProvider::TAG);
        $this->app->tag([SocialFeedBlockDefinitionProvider::class], BlockDefinitionProvider::TAG);

        $this->callAfterResolving(SocialFeedProviderRegistry::class, function (SocialFeedProviderRegistry $registry): void {
            foreach ($this->app->tagged(SocialFeedProviderProvider::TAG) as $provider) {
                if (! $provider instanceof SocialFeedProviderProvider) {
                    continue;
                }

                $provider->registerProviders($registry);
            }
        });

        $this->app->booted(function (): void {
            if (! CapellCore::isPackageInstalled(self::$packageName)) {
                return;
            }

            $this->surface()->models([
                SocialFeedConnection::class,
                SocialFeedItem::class,
                SocialFeedOAuthState::class,
            ]);

            CapellCore::registerProtectedTable('social_feed_connections');
            CapellCore::registerProtectedTable('social_feed_items');
            CapellCore::registerProtectedTable('social_feed_oauth_states');

            if ((bool) config('capell-social-feeds.sync_schedule_enabled', false)) {
                $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
                    $schedule->command('capell:social-feeds:sync --all')
                        ->hourly()
                        ->withoutOverlapping();
                });
            }
        });
    }
}
