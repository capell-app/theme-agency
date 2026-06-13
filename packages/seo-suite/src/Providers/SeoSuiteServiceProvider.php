<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Providers;

use Capell\Admin\Contracts\DashboardSettingsContributor;
use Capell\Admin\Contracts\Extenders\PageAuthoringValidator;
use Capell\Admin\Contracts\Extenders\PageHeaderActionExtender;
use Capell\Admin\Contracts\Extenders\PageResourceWidgetExtender;
use Capell\Admin\Contracts\Extenders\PageSchemaExtender;
use Capell\Admin\Contracts\Extenders\PageTableExtender;
use Capell\Admin\Contracts\Extenders\SiteHeaderActionExtender;
use Capell\Admin\Contracts\Extenders\SiteSchemaExtender;
use Capell\Admin\Data\Extensions\ExtensionManagementSurfaceData;
use Capell\Admin\Enums\DashboardEnum;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Filament\Resources\Pages\Pages\EditPage;
use Capell\Admin\Support\AdminEventRegistry;
use Capell\Admin\Support\CapellAdminManager;
use Capell\Admin\Support\Notifications\AdminNotificationGroupRegistry;
use Capell\Core\Actions\RegisterBlazeOptimizedViewsAction;
use Capell\Core\Contracts\Pageable;
use Capell\Core\Enums\PackageTypeEnum;
use Capell\Core\Events\PageDeleted;
use Capell\Core\Events\PageSaved;
use Capell\Core\Events\SiteCreated;
use Capell\Core\Events\UrlVisitFailed;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Support\ContentGraph\ContentGraphRegistry;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Core\Support\Settings\SettingsGroupMetadata;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;
use Capell\Frontend\Contracts\FrontendRuntimeManifestContributor;
use Capell\Frontend\Events\FrontendContextResolved;
use Capell\Frontend\Support\Render\RenderHookRegistry;
use Capell\SeoSuite\Actions\Ai\RecordAiGenerationAction;
use Capell\SeoSuite\Actions\ClearAiDiscoveryCacheAction;
use Capell\SeoSuite\Console\Commands\ClearAiCacheCommand;
use Capell\SeoSuite\Console\Commands\DoctorCommand;
use Capell\SeoSuite\Console\Commands\InstallCommand;
use Capell\SeoSuite\Console\Commands\MonitorAiUsageCommand;
use Capell\SeoSuite\Console\Commands\PageSpeedAuditCommand;
use Capell\SeoSuite\Console\Commands\RefreshAiDiscoveryMarkdownCommand;
use Capell\SeoSuite\Console\Commands\SetupCommand;
use Capell\SeoSuite\Console\Commands\SyncSearchConsoleCommand;
use Capell\SeoSuite\Console\Commands\TestOpenAiConnectionCommand;
use Capell\SeoSuite\Contracts\PageSpeedInsightsClientInterface;
use Capell\SeoSuite\Contracts\Schemas\SearchMetaDataSectionExtenderResolverInterface;
use Capell\SeoSuite\Contracts\SearchConsoleClientInterface;
use Capell\SeoSuite\Contracts\SeoPublishReportProvider;
use Capell\SeoSuite\Enums\MetaSchemaEnum;
use Capell\SeoSuite\Enums\SchemaTemplateTypeEnum;
use Capell\SeoSuite\Events\AiGenerationCompleted;
use Capell\SeoSuite\Events\AiGenerationFailed;
use Capell\SeoSuite\Filament\Extenders\Page\PageSeoSettingsTabExtender;
use Capell\SeoSuite\Filament\Extenders\PageSpeed\PageSpeedPageTableExtender;
use Capell\SeoSuite\Filament\Extenders\Site\SiteDetailsMetaExtender;
use Capell\SeoSuite\Filament\Extenders\Site\SiteTranslationMetaExtender;
use Capell\SeoSuite\Filament\Pages\AiDiscoveryPage;
use Capell\SeoSuite\Filament\Pages\BrokenLinksPage;
use Capell\SeoSuite\Filament\Pages\NotFoundUrlsPage;
use Capell\SeoSuite\Filament\Pages\SearchRankingsPage;
use Capell\SeoSuite\Filament\Pages\SeoAuditPage;
use Capell\SeoSuite\Filament\Pages\TranslationCoveragePage;
use Capell\SeoSuite\Filament\Settings\AIOrchestratorSettingsSchema;
use Capell\SeoSuite\Filament\Settings\Contributors\SeoSuiteDashboardSettingsContributor;
use Capell\SeoSuite\Filament\Settings\SeoSettingsSchema;
use Capell\SeoSuite\Filament\Settings\StructuredDataSettingsSchema;
use Capell\SeoSuite\Filament\Widgets\AiDiscoveryCoverageWidget;
use Capell\SeoSuite\Filament\Widgets\EditPageAuditTabsWidget;
use Capell\SeoSuite\Filament\Widgets\EditPagePageSpeedAuditBadge;
use Capell\SeoSuite\Filament\Widgets\EditPagePageSpeedAuditWidget;
use Capell\SeoSuite\Filament\Widgets\EditPageSeoAuditBadge;
use Capell\SeoSuite\Filament\Widgets\EditPageSeoAuditWidget;
use Capell\SeoSuite\Filament\Widgets\SearchConsoleOverviewWidget;
use Capell\SeoSuite\Filament\Widgets\SearchIntelligenceWidget;
use Capell\SeoSuite\Filament\Widgets\SearchMovementWidget;
use Capell\SeoSuite\Filament\Widgets\SeoOpportunitiesWidget;
use Capell\SeoSuite\Filament\Widgets\TopSearchPagesWidget;
use Capell\SeoSuite\Handlers\ClearCircuitBreakerHandler;
use Capell\SeoSuite\Http\Controllers\LlmsFullTxtController;
use Capell\SeoSuite\Http\Controllers\LlmsTxtController;
use Capell\SeoSuite\Http\Controllers\PageMarkdownController;
use Capell\SeoSuite\Http\Controllers\RobotsTxtController;
use Capell\SeoSuite\Listeners\AiDiscovery\ClearAiDiscoveryCacheOnPageDeleted;
use Capell\SeoSuite\Listeners\AiDiscovery\ClearAiDiscoveryCacheOnPageSaved;
use Capell\SeoSuite\Listeners\AiDiscovery\SeedAiCrawlerRulesOnSiteCreated;
use Capell\SeoSuite\Listeners\LogAiGeneration;
use Capell\SeoSuite\Listeners\NotifyAiFailure;
use Capell\SeoSuite\Listeners\RecordBrokenLink;
use Capell\SeoSuite\Models\AiCreatorContext;
use Capell\SeoSuite\Models\AiCreatorSession;
use Capell\SeoSuite\Models\AiDiscoveryCrawlerRule;
use Capell\SeoSuite\Models\AiDiscoveryPageProfile;
use Capell\SeoSuite\Models\AiDiscoverySiteProfile;
use Capell\SeoSuite\Models\AiDiscoverySnapshot;
use Capell\SeoSuite\Models\AIGenerationHistory;
use Capell\SeoSuite\Models\BrokenLink;
use Capell\SeoSuite\Models\PageSeoSnapshot;
use Capell\SeoSuite\Models\PageSpeedAuditResult;
use Capell\SeoSuite\Models\PageSpeedAuditRun;
use Capell\SeoSuite\Models\SearchConsoleQueryMetric;
use Capell\SeoSuite\Models\SearchConsoleUrlMetric;
use Capell\SeoSuite\Policies\AiCreatorPolicy;
use Capell\SeoSuite\Settings\AIOrchestratorSettings;
use Capell\SeoSuite\Settings\SeoSuiteSettings;
use Capell\SeoSuite\Support\Admin\AiCreatorPageExtender;
use Capell\SeoSuite\Support\Admin\AiCreatorSiteExtender;
use Capell\SeoSuite\Support\Admin\PageContentEditorConfigurator;
use Capell\SeoSuite\Support\Admin\PageSeoAuditPageResourceWidgetExtender;
use Capell\SeoSuite\Support\Admin\PageTitleWithSlugInputExtender;
use Capell\SeoSuite\Support\Admin\RemoveInlineSeoTranslationComponents;
use Capell\SeoSuite\Support\Admin\SeoAuthoringQualityGateValidator;
use Capell\SeoSuite\Support\AiDiscovery\AiDiscoveryDiscoveryOutputSource;
use Capell\SeoSuite\Support\AiFeatureRegistry;
use Capell\SeoSuite\Support\AiRateLimiter;
use Capell\SeoSuite\Support\AiResponseParser;
use Capell\SeoSuite\Support\AiTokenCounter;
use Capell\SeoSuite\Support\Cache\AIGenerationCache;
use Capell\SeoSuite\Support\Cache\RateLimitCache;
use Capell\SeoSuite\Support\ContentGraph\BrokenLinkContentGraphExtractor;
use Capell\SeoSuite\Support\ContentGraph\PageSeoSnapshotContentGraphExtractor;
use Capell\SeoSuite\Support\ContentTargetResolver;
use Capell\SeoSuite\Support\PageSpeed\GooglePageSpeedInsightsClient;
use Capell\SeoSuite\Support\PageSpeed\NullPageSpeedInsightsClient;
use Capell\SeoSuite\Support\Pipelines\AiCreatorPipeline;
use Capell\SeoSuite\Support\PrismProvider;
use Capell\SeoSuite\Support\PromptRepository;
use Capell\SeoSuite\Support\Publishing\SeoPublishReportProviderAdapter;
use Capell\SeoSuite\Support\RenderHooks\RegisterSeoHeadHooks;
use Capell\SeoSuite\Support\Schemas\SearchMetaDataSectionExtenderResolver;
use Capell\SeoSuite\Support\SchemaTemplates\ArticleSchemaTemplate;
use Capell\SeoSuite\Support\SchemaTemplates\SchemaTemplateRegistry;
use Capell\SeoSuite\Support\SchemaTemplates\WebPageSchemaTemplate;
use Capell\SeoSuite\Support\SearchConsole\GoogleSearchConsoleClient;
use Capell\SeoSuite\Support\SearchConsole\NullSearchConsoleClient;
use Capell\SeoSuite\Support\SectionRegistry;
use Capell\SeoSuite\Support\SeoSuiteFrontendRuntimeManifestContributor;
use Capell\SeoSuite\Support\SiteDiscovery\AiDiscoveryGeneratedOutputCoverageSource;
use Capell\SeoSuite\Targets\FlatJsonTarget;
use Capell\SeoSuite\View\Composers\SchemaComponentComposer;
use Capell\SeoSuite\View\Composers\WebsiteSchemaComposer;
use Capell\SiteDiscovery\Contracts\DiscoveryOutputSource;
use Capell\SiteDiscovery\Contracts\GeneratedOutputCoverageSource;
use Capell\SiteDiscovery\Support\DiscoveryOutputRegistry;
use Closure;
use Filament\Support\Icons\Heroicon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Override;
use Spatie\LaravelPackageTools\Package;

class SeoSuiteServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-seo-suite';

    public static string $packageName = 'capell-app/seo-suite';

    public static PackageTypeEnum $type = PackageTypeEnum::Plugin;

    /**
     * @return list<string>
     */
    public static function getSettingMigrations(): array
    {
        return [
            '2026_05_10_190871_01_create_ai-orchestrator_settings',
            '2026_05_10_190871_03_create_seo_suite_settings',
            '2026_05_29_000001_add_pagespeed_seo_suite_settings',
        ];
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasViews(self::$name)
            ->hasTranslations()
            ->hasConfigFile(self::$name)
            ->hasCommands([
                ClearAiCacheCommand::class,
                DoctorCommand::class,
                InstallCommand::class,
                MonitorAiUsageCommand::class,
                PageSpeedAuditCommand::class,
                RefreshAiDiscoveryMarkdownCommand::class,
                SetupCommand::class,
                SyncSearchConsoleCommand::class,
                TestOpenAiConnectionCommand::class,
            ]);
    }

    public function registeringPackage(): void
    {
        $this->registerContentGraphExtractors();

        $this->app->booted(function (): void {
            if (! $this->isPackageInstalled()) {
                return;
            }

            $this->bootInstalledPackage();
        });
    }

    public function packageBooted(): void
    {
        $this->registerLivewireComponents();
    }

    /**
     * Discover migrations in database/migrations as filenames (no extension).
     *
     * @return array<int, string>
     */
    protected function discoveredMigrations(): array
    {
        return $this->discoverMigrations();
    }

    protected function registerAiServices(): self
    {
        $this->app->singleton(PrismProvider::class, fn (Application $app): PrismProvider => new PrismProvider(config('capell-seo-suite.prism', [])));

        $this->app->singleton(PromptRepository::class, fn (Application $app): PromptRepository => new PromptRepository(config('capell-seo-suite.prompts', [])));

        $this->app->singleton(AiResponseParser::class, fn (): AiResponseParser => new AiResponseParser);

        $this->app->singleton(AiRateLimiter::class, fn (Application $app): AiRateLimiter => new AiRateLimiter(
            $app->make(RateLimitCache::class),
            config('capell-seo-suite.rate_limiting', ['enabled' => false, 'requests_per_minute' => 60]),
        ));

        $this->app->singleton(AiTokenCounter::class, fn (): AiTokenCounter => new AiTokenCounter);

        $this->app->singleton(AiFeatureRegistry::class, fn (Application $app): AiFeatureRegistry => new AiFeatureRegistry(config('capell-seo-suite.features', [])));

        $this->app->singleton(AIGenerationCache::class, fn (Application $app): AIGenerationCache => new AIGenerationCache(
            config('cache.default'),
            config('capell-seo-suite.cache.ttl', 86400),
        ));

        $this->app->singleton(RateLimitCache::class, fn (\Illuminate\Foundation\Application $app): RateLimitCache => new RateLimitCache((string) config('cache.default')));

        $this->app->singleton(SectionRegistry::class, fn (): SectionRegistry => new SectionRegistry);

        $this->app->singleton(ContentTargetResolver::class, function (Application $app): ContentTargetResolver {
            $resolver = new ContentTargetResolver;
            $resolver->register($app->make(FlatJsonTarget::class));

            foreach ($app->tagged('capell-seo-suite:content-targets') as $target) {
                $resolver->register($target);
            }

            return $resolver;
        });

        $this->app->singleton(AiCreatorPolicy::class, fn (Application $app): AiCreatorPolicy => new AiCreatorPolicy(
            $app->make(AIOrchestratorSettings::class),
        ));

        $this->app->singleton(AiCreatorPipeline::class, fn (Application $app): AiCreatorPipeline => new AiCreatorPipeline(
            $app->make(PromptRepository::class),
            $app->make(PrismProvider::class),
            $app->make(AiRateLimiter::class),
            $app->make(SectionRegistry::class),
            $app->make(RecordAiGenerationAction::class),
            $app->make(AiCreatorPolicy::class),
        ));

        /** @var AiFeatureRegistry $registry */
        $registry = $this->app->make(AiFeatureRegistry::class);
        foreach (config('capell-seo-suite.features', []) as $name => $feature) {
            if (is_array($feature)) {
                $registry->register($name, $feature);
            }
        }

        return $this;
    }

    protected function registerAiEventListeners(): self
    {
        $events = $this->app->make(Dispatcher::class);
        $events->listen(
            AiGenerationFailed::class,
            NotifyAiFailure::class,
        );
        $events->listen(
            AiGenerationCompleted::class,
            LogAiGeneration::class,
        );

        return $this;
    }

    protected function registerBrokenLinkEventListeners(): self
    {
        $events = $this->app->make(Dispatcher::class);
        $events->listen(UrlVisitFailed::class, RecordBrokenLink::class);

        return $this;
    }

    protected function registerAdminEvents(): self
    {
        /** @var AdminEventRegistry $registry */
        $registry = $this->app->make(AdminEventRegistry::class);

        $registry->register(EditPage::class, 'clear-circuit-breaker', ClearCircuitBreakerHandler::class);

        return $this;
    }

    protected function registerAdminExtenders(): self
    {
        $this->app->tag([
            PageContentEditorConfigurator::class,
        ], 'capell-admin:page-content-editor');

        $this->app->tag([
            PageTitleWithSlugInputExtender::class,
        ], 'capell-admin:page-title-with-slug-input');

        $this->app->tag([
            AiCreatorPageExtender::class,
        ], PageHeaderActionExtender::TAG);

        $this->app->tag([
            AiCreatorSiteExtender::class,
        ], SiteHeaderActionExtender::TAG);

        $this->app->tag([
            PageSeoAuditPageResourceWidgetExtender::class,
        ], PageResourceWidgetExtender::TAG);

        $this->app->tag([
            PageSpeedPageTableExtender::class,
        ], PageTableExtender::TAG);

        $this->app->tag([
            SeoAuthoringQualityGateValidator::class,
        ], PageAuthoringValidator::TAG);

        return $this;
    }

    protected function registerPageSchemaExtenders(): self
    {
        $this->app->tag(
            [
                PageSeoSettingsTabExtender::class,
            ],
            PageSchemaExtender::TAG,
        );

        $this->app->singleton(RemoveInlineSeoTranslationComponents::class);
        $this->app->tag(RemoveInlineSeoTranslationComponents::class, 'capell-admin:schema-component-replacers:page');

        return $this;
    }

    protected function registerSiteSchemaExtenders(): self
    {
        $this->app->tag(
            [
                SiteTranslationMetaExtender::class,
                SiteDetailsMetaExtender::class,
            ],
            SiteSchemaExtender::TAG,
        );

        return $this;
    }

    protected function registerSettingsSchema(): self
    {
        /** @var SettingsSchemaRegistry $registry */
        $registry = $this->app->make(SettingsSchemaRegistry::class);
        $registry->register('ai-orchestrator', AIOrchestratorSettingsSchema::class);
        $registry->registerSettingsClass('ai-orchestrator', AIOrchestratorSettings::class);
        $registry->registerSettingsClass('seo_suite', SeoSuiteSettings::class);
        $registry->registerMetadata(new SettingsGroupMetadata(
            group: 'seo_suite',
            label: 'capell-seo-suite::generic.seo_settings',
            icon: Heroicon::OutlinedMagnifyingGlass,
            navigationGroup: 'capell-admin::navigation.group_system',
            navigationSort: 94,
            packageName: static::$packageName,
        ));
        $registry->register('seo_suite', SeoSettingsSchema::class);
        $registry->register('frontend', StructuredDataSettingsSchema::class);

        return $this;
    }

    protected function registerFilamentPages(): self
    {
        /** @var CapellAdminManager $adminManager */
        $adminManager = $this->app->make(CapellAdminManager::class);

        $adminManager->registerExtensionPage(static::$packageName, NotFoundUrlsPage::class);
        $adminManager->registerExtensionPage(static::$packageName, BrokenLinksPage::class);
        $adminManager->registerExtensionPage(static::$packageName, SeoAuditPage::class);
        $adminManager->registerExtensionPage(static::$packageName, SearchRankingsPage::class);
        $adminManager->registerExtensionPage(static::$packageName, AiDiscoveryPage::class);
        $adminManager->registerExtensionManagementSurface(ExtensionManagementSurfaceData::settings(
            packageName: static::$packageName,
            label: 'capell-seo-suite::generic.seo_settings',
            settingsGroup: 'seo_suite',
            icon: Heroicon::OutlinedMagnifyingGlass,
        ));
        $adminManager->registerExtensionPage(static::$packageName, TranslationCoveragePage::class);

        return $this;
    }

    protected function registerDashboardSettingsContributor(): self
    {
        $this->app->tag([SeoSuiteDashboardSettingsContributor::class], DashboardSettingsContributor::TAG);

        return $this;
    }

    protected function registerDashboardWidgets(): self
    {
        CapellAdmin::registerDashboardWidget(SearchConsoleOverviewWidget::class, DashboardEnum::Main);
        CapellAdmin::registerDashboardWidget(TopSearchPagesWidget::class, DashboardEnum::Main);
        CapellAdmin::registerDashboardWidget(SearchMovementWidget::class, DashboardEnum::Main);
        CapellAdmin::registerDashboardWidget(SeoOpportunitiesWidget::class, DashboardEnum::Main);
        CapellAdmin::registerDashboardWidget(SearchIntelligenceWidget::class, DashboardEnum::Main);
        CapellAdmin::registerDashboardWidget(AiDiscoveryCoverageWidget::class, DashboardEnum::Main);

        return $this;
    }

    protected function registerFrontendViews(): self
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'capell');
        View::composer([
            'capell::components.schema.breadcrumb',
            'capell::components.schema.image',
            'capell::components.schema.organization',
            'capell::components.schema.webpage',
        ], SchemaComponentComposer::class);
        View::composer('capell::components.schema.website', WebsiteSchemaComposer::class);

        return $this;
    }

    protected function registerBlazeComponents(): self
    {
        RegisterBlazeOptimizedViewsAction::run(__DIR__ . '/../../resources/views/components/schema');

        return $this;
    }

    protected function registerSchemaTemplateRegistry(): self
    {
        /** @var SchemaTemplateRegistry $registry */
        $registry = $this->app->make(SchemaTemplateRegistry::class);

        $registry->registerIfMissing(SchemaTemplateTypeEnum::WebPage, new WebPageSchemaTemplate);
        $registry->registerIfMissing(SchemaTemplateTypeEnum::Article, new ArticleSchemaTemplate);

        return $this;
    }

    protected function registerAiDiscoveryEventListeners(): self
    {
        $events = $this->app->make(Dispatcher::class);
        $events->listen(PageSaved::class, ClearAiDiscoveryCacheOnPageSaved::class);
        $events->listen(PageDeleted::class, ClearAiDiscoveryCacheOnPageDeleted::class);
        $events->listen(SiteCreated::class, SeedAiCrawlerRulesOnSiteCreated::class);

        return $this;
    }

    protected function bindPageMarkdownResponder(): self
    {
        $this->app->bind(
            'capell.frontend.page-markdown-response',
            fn (Application $application): Closure => fn (): ?Response => $application
                ->make(PageMarkdownController::class)
                ->forAcceptHeader($application->make(Request::class)),
        );

        return $this;
    }

    protected function registerRenderHooks(): self
    {
        if (class_exists(RenderHookRegistry::class)) {
            $this->app->make(RegisterSeoHeadHooks::class)->register();
        }

        return $this;
    }

    protected function registerContentGraphExtractors(): self
    {
        if (class_exists(ContentGraphRegistry::class)) {
            $this->app->singleton(PageSeoSnapshotContentGraphExtractor::class);
            $this->app->singleton(BrokenLinkContentGraphExtractor::class);
            $this->app->tag([
                PageSeoSnapshotContentGraphExtractor::class,
                BrokenLinkContentGraphExtractor::class,
            ], ContentGraphRegistry::TAG);
        }

        return $this;
    }

    protected function registerLlmsTxtRoute(): self
    {
        Route::name('capell-frontend.')
            ->group(function (): void {
                Route::middleware(['web', 'frontend.resolve'])
                    ->group(function (): void {
                        Route::get('llms.txt', LlmsTxtController::class)->name('llms-txt');
                        Route::get('llms-full.txt', LlmsFullTxtController::class)->name('llms-full-txt');
                        Route::get('robots.txt', RobotsTxtController::class)->name('robots-txt');
                    });

                Route::middleware(['web'])
                    ->group(function (): void {
                        Route::get('index.md', PageMarkdownController::class)->name('page-markdown-home');
                        Route::get('{url}.md', PageMarkdownController::class)
                            ->where('url', config('capell-frontend.route.url_regex', '.*'))
                            ->name('page-markdown');
                    });
            });

        return $this;
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(static::$packageName);
    }

    private function registerLivewireComponents(): void
    {
        Livewire::component('capell-seo-suite.edit-page-audit-tabs', EditPageAuditTabsWidget::class);
        Livewire::component('capell-seo-suite.edit-page-seo-audit', EditPageSeoAuditWidget::class);
        Livewire::component('capell-seo-suite.edit-page-pagespeed-audit', EditPagePageSpeedAuditWidget::class);
        Livewire::component('capell-seo-suite.edit-page-seo-audit-badge', EditPageSeoAuditBadge::class);
        Livewire::component('capell-seo-suite.edit-page-pagespeed-audit-badge', EditPagePageSpeedAuditBadge::class);
    }

    private function bootInstalledPackage(): self
    {
        return $this
            ->registerExtenderResolvers()
            ->registerModels()
            ->registerModelRelations()
            ->registerBlazeComponents()
            ->bindSchemaTemplateRegistry()
            ->bindSearchConsoleClient()
            ->bindPageSpeedInsightsClient()
            ->bindSeoPublishReportProvider()
            ->registerNotificationGroups()
            ->registerAdminEvents()
            ->registerAdminExtenders()
            ->registerPageSchemaExtenders()
            ->registerSiteSchemaExtenders()
            ->registerAiServices()
            ->registerAiEventListeners()
            ->registerAiDiscoveryEventListeners()
            ->registerAiDiscoveryOutputSource()
            ->registerAiDiscoveryGeneratedOutputCoverage()
            ->registerAiDiscoveryModelCacheInvalidation()
            ->registerBrokenLinkEventListeners()
            ->registerSettingsSchema()
            ->registerSchemaTemplateRegistry()
            ->bindPageMarkdownResponder()
            ->registerFrontendContextHydration()
            ->registerFrontendRuntimeManifestContributors()
            ->registerFilamentPages()
            ->registerDashboardSettingsContributor()
            ->registerDashboardWidgets()
            ->registerPageSpeedSchedule()
            ->registerFrontendViews()
            ->registerRenderHooks()
            ->registerLlmsTxtRoute();
    }

    private function registerAiDiscoveryOutputSource(): self
    {
        if (! interface_exists(DiscoveryOutputSource::class)) {
            return $this;
        }

        $this->app->singleton(AiDiscoveryDiscoveryOutputSource::class);
        $this->app->tag([AiDiscoveryDiscoveryOutputSource::class], DiscoveryOutputSource::TAG);

        if (class_exists(DiscoveryOutputRegistry::class) && $this->app->bound(DiscoveryOutputRegistry::class)) {
            /** @var DiscoveryOutputRegistry $registry */
            $registry = $this->app->make(DiscoveryOutputRegistry::class);
            $registry->register($this->app->make(AiDiscoveryDiscoveryOutputSource::class));
        }

        return $this;
    }

    private function registerFrontendRuntimeManifestContributors(): self
    {
        if (interface_exists(FrontendRuntimeManifestContributor::class)) {
            $this->app->tag([SeoSuiteFrontendRuntimeManifestContributor::class], FrontendRuntimeManifestContributor::TAG);
        }

        return $this;
    }

    private function registerAiDiscoveryGeneratedOutputCoverage(): self
    {
        if (! interface_exists(GeneratedOutputCoverageSource::class)) {
            return $this;
        }

        $this->app->singleton(AiDiscoveryGeneratedOutputCoverageSource::class);
        $this->app->tag([AiDiscoveryGeneratedOutputCoverageSource::class], GeneratedOutputCoverageSource::TAG);

        return $this;
    }

    private function registerAiDiscoveryModelCacheInvalidation(): self
    {
        AiDiscoverySiteProfile::saved(fn (AiDiscoverySiteProfile $profile): int => $profile->site instanceof Site
            ? ClearAiDiscoveryCacheAction::run($profile->site, $profile->language)
            : 0);
        AiDiscoverySiteProfile::deleted(fn (AiDiscoverySiteProfile $profile): int => $profile->site instanceof Site
            ? ClearAiDiscoveryCacheAction::run($profile->site, $profile->language)
            : 0);

        AiDiscoveryPageProfile::saved(fn (AiDiscoveryPageProfile $profile): int => $profile->site instanceof Site
            ? ClearAiDiscoveryCacheAction::run($profile->site, $profile->language, $profile->page)
            : 0);
        AiDiscoveryPageProfile::deleted(fn (AiDiscoveryPageProfile $profile): int => $profile->site instanceof Site
            ? ClearAiDiscoveryCacheAction::run($profile->site, $profile->language, $profile->page)
            : 0);

        return $this;
    }

    private function registerFrontendContextHydration(): self
    {
        $events = $this->app->make(Dispatcher::class);
        $events->listen(FrontendContextResolved::class, function (FrontendContextResolved $event): void {
            $page = $event->context->page();

            if (! $page instanceof Pageable || ! $page instanceof Model || ! $this->shouldHydratePageImages($event)) {
                return;
            }

            $page->loadMissing(['image', 'media']);
        });

        return $this;
    }

    private function shouldHydratePageImages(FrontendContextResolved $event): bool
    {
        $metaSchema = data_get($event->context->site?->meta, 'meta_schema');

        return is_array($metaSchema) && in_array(MetaSchemaEnum::Image->getComponent(), $metaSchema, true);
    }

    private function registerExtenderResolvers(): self
    {
        $this->app->singleton(
            SearchMetaDataSectionExtenderResolverInterface::class,
            fn (): SearchMetaDataSectionExtenderResolver => new SearchMetaDataSectionExtenderResolver,
        );

        return $this;
    }

    private function bindSchemaTemplateRegistry(): self
    {
        $this->app->singleton(SchemaTemplateRegistry::class, fn (): SchemaTemplateRegistry => new SchemaTemplateRegistry);

        return $this;
    }

    private function bindSearchConsoleClient(): self
    {
        $this->app->singleton(SearchConsoleClientInterface::class, function (): SearchConsoleClientInterface {
            $config = config('capell-seo-suite.search_console', []);

            if (! is_array($config)) {
                return new NullSearchConsoleClient;
            }

            $credentialsPath = $config['credentials_path'] ?? null;

            if (($config['enabled'] ?? false) !== true || ! is_string($credentialsPath) || trim($credentialsPath) === '') {
                return new NullSearchConsoleClient;
            }

            return new GoogleSearchConsoleClient($config);
        });

        return $this;
    }

    private function bindSeoPublishReportProvider(): self
    {
        $this->app->singleton(SeoPublishReportProvider::class, SeoPublishReportProviderAdapter::class);

        return $this;
    }

    private function bindPageSpeedInsightsClient(): self
    {
        $this->app->singleton(PageSpeedInsightsClientInterface::class, function (): PageSpeedInsightsClientInterface {
            $config = config('capell-seo-suite.pagespeed', []);

            if (! is_array($config)) {
                return new NullPageSpeedInsightsClient;
            }

            $apiKey = $config['api_key'] ?? null;

            if (($config['enabled'] ?? false) !== true || ! is_string($apiKey) || trim($apiKey) === '') {
                return new NullPageSpeedInsightsClient;
            }

            return new GooglePageSpeedInsightsClient($config);
        });

        return $this;
    }

    private function registerNotificationGroups(): self
    {
        $this->app->afterResolving(AdminNotificationGroupRegistry::class, function (AdminNotificationGroupRegistry $registry): void {
            $registry->register(
                key: 'seo_suite_pagespeed_reports',
                label: (string) __('capell-seo-suite::generic.pagespeed_digest_group_label'),
                description: (string) __('capell-seo-suite::generic.pagespeed_digest_group_description'),
                defaultRecipients: fn (): EloquentCollection => $this->defaultPageSpeedDigestRecipients(),
            );
        });

        return $this;
    }

    private function registerPageSpeedSchedule(): self
    {
        if (! Schema::hasTable('settings')) {
            return $this;
        }

        if (! $this->pageSpeedAuditsAreConfigured()) {
            return $this;
        }

        $settings = $this->app->make(SeoSuiteSettings::class);

        if (! $settings->pagespeed_audit_enabled || ! $settings->pagespeed_weekly_digest_enabled) {
            return $this;
        }

        $limit = max(1, $settings->pagespeed_scheduled_limit);

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) use ($limit): void {
            $schedule
                ->command('capell:seo-suite:pagespeed-audit', ['--notify' => true, '--limit' => $limit])
                ->weeklyOn(1, '06:00')
                ->withoutOverlapping()
                ->onOneServer();
        });

        return $this;
    }

    private function pageSpeedAuditsAreConfigured(): bool
    {
        $config = config('capell-seo-suite.pagespeed', []);

        if (! is_array($config)) {
            return false;
        }

        $apiKey = $config['api_key'] ?? null;

        return ($config['enabled'] ?? false) === true
            && is_string($apiKey)
            && trim($apiKey) !== '';
    }

    /**
     * @return array<int, string>
     */
    private function discoverMigrations(): array
    {
        $directory = realpath(__DIR__ . '/../../database/migrations');

        if ($directory === false) {
            return [];
        }

        $files = glob($directory . '/*.php');

        if ($files === false) {
            return [];
        }

        return array_map(
            static fn (string $path): string => pathinfo($path, PATHINFO_FILENAME),
            $files,
        );
    }

    private function registerModels(): self
    {
        CapellCore::registerModels([
            AIGenerationHistory::class,
            AiCreatorContext::class,
            AiCreatorSession::class,
            AiDiscoverySiteProfile::class,
            AiDiscoveryPageProfile::class,
            AiDiscoveryCrawlerRule::class,
            AiDiscoverySnapshot::class,
            BrokenLink::class,
            PageSpeedAuditRun::class,
            PageSpeedAuditResult::class,
            PageSeoSnapshot::class,
            SearchConsoleUrlMetric::class,
            SearchConsoleQueryMetric::class,
        ]);

        return $this;
    }

    private function registerModelRelations(): self
    {
        Page::resolveRelationUsing(
            'seoSnapshots',
            fn (Page $page): HasMany => $page->hasMany(PageSeoSnapshot::class, 'page_id'),
        );

        Page::resolveRelationUsing(
            'pageSpeedAuditResults',
            fn (Page $page): HasMany => $page->hasMany(PageSpeedAuditResult::class, 'page_id'),
        );

        Page::resolveRelationUsing(
            'latestMobilePageSpeedAuditResult',
            fn (Page $page): HasOne => $page->hasOne(PageSpeedAuditResult::class, 'page_id')
                ->where('strategy', 'mobile')
                ->latestOfMany('fetched_at'),
        );

        Page::resolveRelationUsing(
            'latestDesktopPageSpeedAuditResult',
            fn (Page $page): HasOne => $page->hasOne(PageSpeedAuditResult::class, 'page_id')
                ->where('strategy', 'desktop')
                ->latestOfMany('fetched_at'),
        );

        return $this;
    }

    /**
     * @return EloquentCollection<int, Model>
     */
    private function defaultPageSpeedDigestRecipients(): EloquentCollection
    {
        $userModel = config('auth.providers.users.model');

        if (! is_string($userModel) || ! is_a($userModel, Model::class, true)) {
            return new EloquentCollection;
        }

        $superAdminRole = (string) config('capell.roles.super_admin', 'super_admin');

        if (! method_exists($userModel, 'roles')) {
            return new EloquentCollection;
        }

        return $userModel::query()
            ->whereHas('roles', function (Builder $query) use ($superAdminRole): Builder {
                $query->where('name', $superAdminRole);

                return $this->whereGlobalRoleAssignment($query);
            })
            ->get()
            ->values();
    }

    private function whereGlobalRoleAssignment(Builder $query): Builder
    {
        $tableNames = config('permission.table_names', []);
        $modelHasRolesTable = is_array($tableNames) && is_string($tableNames['model_has_roles'] ?? null)
            ? $tableNames['model_has_roles']
            : 'model_has_roles';
        $teamColumnConfig = config('permission.column_names.team_foreign_key', 'team_id');
        $teamColumn = is_string($teamColumnConfig) && $teamColumnConfig !== ''
            ? $teamColumnConfig
            : 'team_id';

        if (! Schema::hasColumn($modelHasRolesTable, $teamColumn)) {
            return $query;
        }

        return $query->whereNull($modelHasRolesTable . '.' . $teamColumn);
    }
}
