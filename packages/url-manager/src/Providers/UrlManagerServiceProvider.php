<?php

declare(strict_types=1);

namespace Capell\UrlManager\Providers;

use Capell\Admin\Support\CapellAdminManager;
use Capell\Core\Contracts\RedirectResolver;
use Capell\Core\Enums\PackageTypeEnum;
use Capell\Core\Events\PageUrlChanged;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Frontend\Support\Routing\FrontendRouteMiddlewareRegistry;
use Capell\UrlManager\Console\Commands\PruneRedirectHitsCommand;
use Capell\UrlManager\Filament\Pages\NotFoundOpportunitiesPage;
use Capell\UrlManager\Filament\Pages\RedirectRulesPage;
use Capell\UrlManager\Http\Middleware\RecordNotFoundOpportunityMiddleware;
use Capell\UrlManager\Http\Middleware\ServeGoneRedirectRuleMiddleware;
use Capell\UrlManager\Listeners\RecordRedirectForChangedPageUrl;
use Capell\UrlManager\Models\NotFoundOpportunity;
use Capell\UrlManager\Models\RedirectHit;
use Capell\UrlManager\Models\RedirectRule;
use Capell\UrlManager\Support\Redirects\UrlManagerRedirectResolver;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelPackageTools\Package;

final class UrlManagerServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-url-manager';

    public static string $packageName = 'capell-app/url-manager';

    public static PackageTypeEnum $type = PackageTypeEnum::Plugin;

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile('capell-url-manager')
            ->hasTranslations()
            ->hasCommand(PruneRedirectHitsCommand::class)
            ->hasMigrations([
                '2026_05_31_000001_create_url_manager_redirect_rules_table',
                '2026_05_31_000002_create_url_manager_redirect_hits_table',
                '2026_05_31_000003_create_url_manager_not_found_opportunities_table',
                '2026_06_04_000001_add_priority_to_url_manager_redirect_rules_table',
            ]);
    }

    public function registeringPackage(): void
    {
        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this
            ->registerModels()
            ->registerProtectedTables()
            ->registerRedirectResolver()
            ->registerNotFoundCaptureMiddleware()
            ->registerChangedUrlListener()
            ->registerAdminPages();
    }

    private function registerModels(): self
    {
        CapellCore::registerModels([
            RedirectRule::class,
            RedirectHit::class,
            NotFoundOpportunity::class,
        ]);

        return $this;
    }

    private function registerProtectedTables(): self
    {
        CapellCore::registerProtectedTable('url_manager_redirect_rules');
        CapellCore::registerProtectedTable('url_manager_redirect_hits');
        CapellCore::registerProtectedTable('url_manager_not_found_opportunities');

        return $this;
    }

    private function registerRedirectResolver(): self
    {
        if (! interface_exists(RedirectResolver::class)) {
            return $this;
        }

        if ($this->app->bound(RedirectResolver::class)) {
            $this->app->extend(
                RedirectResolver::class,
                fn (RedirectResolver $fallbackResolver): UrlManagerRedirectResolver => new UrlManagerRedirectResolver($fallbackResolver),
            );

            return $this;
        }

        $this->app->singleton(RedirectResolver::class, UrlManagerRedirectResolver::class);

        return $this;
    }

    private function registerChangedUrlListener(): self
    {
        if (class_exists(PageUrlChanged::class)) {
            Event::listen(PageUrlChanged::class, [RecordRedirectForChangedPageUrl::class, 'handle']);
        }

        return $this;
    }

    private function registerNotFoundCaptureMiddleware(): self
    {
        if (! class_exists(FrontendRouteMiddlewareRegistry::class)) {
            return $this;
        }

        $configure = static fn (FrontendRouteMiddlewareRegistry $registry): FrontendRouteMiddlewareRegistry => $registry->append([
            ServeGoneRedirectRuleMiddleware::class,
            RecordNotFoundOpportunityMiddleware::class,
        ]);

        $this->app->afterResolving(FrontendRouteMiddlewareRegistry::class, $configure);

        if ($this->app->resolved(FrontendRouteMiddlewareRegistry::class)) {
            $configure($this->app->make(FrontendRouteMiddlewareRegistry::class));
        }

        return $this;
    }

    private function registerAdminPages(): self
    {
        if (! class_exists(CapellAdminManager::class)) {
            return $this;
        }

        $registerPages = static function (CapellAdminManager $capellAdminManager): void {
            $capellAdminManager->registerExtensionPage(self::$packageName, RedirectRulesPage::class);
            $capellAdminManager->registerExtensionPage(self::$packageName, NotFoundOpportunitiesPage::class);
        };

        if ($this->app->bound(CapellAdminManager::class)) {
            $registerPages($this->app->make(CapellAdminManager::class));
        }

        $this->app->afterResolving(CapellAdminManager::class, $registerPages);

        return $this;
    }
}
