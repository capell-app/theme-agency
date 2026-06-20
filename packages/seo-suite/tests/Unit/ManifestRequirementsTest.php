<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionFilamentWidget;
use Capell\Core\Contracts\Extensions\RegistersExtensionSetting;
use Capell\Core\Contracts\Extensions\RunsScheduledExtensionJob;
use Capell\Core\Models\Page;
use Capell\SeoSuite\Actions\BuildCrawlerPreviewReportAction;
use Capell\SeoSuite\Actions\BuildMarketplaceStructuredDataFreshnessWarningsAction;
use Capell\SeoSuite\Actions\BuildSchemaTemplateReportAction;
use Capell\SeoSuite\Actions\BuildSeoSuiteDoctorReportAction;
use Capell\SeoSuite\Actions\ClearAiDiscoveryCacheAction;
use Capell\SeoSuite\Actions\RefreshStaleAiDiscoveryMarkdownAction;
use Capell\SeoSuite\Actions\SyncAiDiscoveryPageProfilesAction;
use Capell\SeoSuite\Console\Commands\ClearAiCacheCommand;
use Capell\SeoSuite\Console\Commands\DoctorCommand;
use Capell\SeoSuite\Console\Commands\InstallCommand;
use Capell\SeoSuite\Console\Commands\MonitorAiUsageCommand;
use Capell\SeoSuite\Console\Commands\PageSpeedAuditCommand;
use Capell\SeoSuite\Console\Commands\RefreshAiDiscoveryMarkdownCommand;
use Capell\SeoSuite\Console\Commands\SetupCommand;
use Capell\SeoSuite\Console\Commands\SyncSearchConsoleCommand;
use Capell\SeoSuite\Console\Commands\TestOpenAiConnectionCommand;
use Capell\SeoSuite\Filament\Pages\AiDiscoveryPage;
use Capell\SeoSuite\Filament\Pages\BrokenLinksPage;
use Capell\SeoSuite\Filament\Pages\NotFoundUrlsPage;
use Capell\SeoSuite\Filament\Pages\SearchRankingsPage;
use Capell\SeoSuite\Filament\Pages\SeoAuditPage;
use Capell\SeoSuite\Filament\Pages\TranslationCoveragePage;
use Capell\SeoSuite\Filament\Widgets\AiDiscoveryCoverageFilamentWidget;
use Capell\SeoSuite\Filament\Widgets\SearchConsoleOverviewFilamentWidget;
use Capell\SeoSuite\Filament\Widgets\SearchIntelligenceFilamentWidget;
use Capell\SeoSuite\Filament\Widgets\SearchMovementFilamentWidget;
use Capell\SeoSuite\Filament\Widgets\SeoOpportunitiesFilamentWidget;
use Capell\SeoSuite\Filament\Widgets\TopSearchPagesFilamentWidget;
use Capell\SeoSuite\Health\SeoSuiteHealthCheck;
use Capell\SeoSuite\Manifest\AiDiscoveryPageContribution;
use Capell\SeoSuite\Manifest\AiDiscoveryRoutesContribution;
use Capell\SeoSuite\Manifest\BrokenLinksPageContribution;
use Capell\SeoSuite\Manifest\NotFoundUrlsPageContribution;
use Capell\SeoSuite\Manifest\SearchRankingsPageContribution;
use Capell\SeoSuite\Manifest\SeoAuditPageContribution;
use Capell\SeoSuite\Manifest\SeoSuiteConsoleCommandsContribution;
use Capell\SeoSuite\Manifest\SeoSuiteDashboardFilamentWidgetsContribution;
use Capell\SeoSuite\Manifest\SeoSuiteHealthContribution;
use Capell\SeoSuite\Manifest\SeoSuiteModelsContribution;
use Capell\SeoSuite\Manifest\SeoSuitePageSpeedScheduleContribution;
use Capell\SeoSuite\Manifest\SeoSuiteSettingsContribution;
use Capell\SeoSuite\Manifest\TranslationCoveragePageContribution;
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
use Capell\SeoSuite\Settings\AIOrchestratorSettings;
use Capell\SeoSuite\Settings\SeoSuiteSettings;

it('declares implemented diagnostics commands tables and capabilities', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');

    expect(data_get($manifest, 'commands.install'))->toBe('capell:seo-suite-install')
        ->and(data_get($manifest, 'commands.setup'))->toBe('capell:seo-suite-setup')
        ->and(data_get($manifest, 'commands.doctor'))->toBe('capell:seo-suite-doctor')
        ->and(data_get($manifest, 'commands.pageSpeedAudit'))->toBe('capell:seo-suite:pagespeed-audit')
        ->and(data_get($manifest, 'commands.refreshAiDiscoveryMarkdown'))->toBe('capell:seo-suite:refresh-ai-discovery-markdown')
        ->and(data_get($manifest, 'commands.syncSearchConsole'))->toBe('capell:seo-suite-sync-search-console')
        ->and(data_get($manifest, 'database.requiredTables', []))->toContain(
            'ai_discovery_page_profiles',
            'ai_discovery_snapshots',
            'broken_links',
            'page_seo_snapshots',
            'page_speed_audit_runs',
            'search_console_url_metrics',
        )
        ->and(data_get($manifest, 'capabilities', []))->toContain(
            'seo-suite-doctor',
            'seo-suite-public-output-leak-scanning',
            'seo-suite-structured-data-audit',
            'seo-suite-ai-discovery-coverage',
            'seo-suite-stale-output-regeneration',
            'seo-suite-site-discovery-registry',
            'seo-suite-marketplace-structured-data-freshness',
            'seo-suite-crawler-preview-report',
            'seo-suite-pagespeed-audits',
            'seo-suite-pagespeed-digest',
            'seo-suite-search-console-rankings',
        );

    expect(data_get($manifest, 'actions', []))
        ->toHaveKey(
            'buildCrawlerPreviewReport',
            BuildCrawlerPreviewReportAction::class,
        )
        ->toHaveKey(
            'buildSeoSuiteDoctorReport',
            BuildSeoSuiteDoctorReportAction::class,
        )
        ->toHaveKey(
            'buildSchemaTemplateReport',
            BuildSchemaTemplateReportAction::class,
        )
        ->toHaveKey(
            'buildMarketplaceStructuredDataFreshnessWarnings',
            BuildMarketplaceStructuredDataFreshnessWarningsAction::class,
        )
        ->toHaveKey(
            'clearAiDiscoveryCache',
            ClearAiDiscoveryCacheAction::class,
        )
        ->toHaveKey(
            'refreshStaleAiDiscoveryMarkdown',
            RefreshStaleAiDiscoveryMarkdownAction::class,
        )
        ->toHaveKey(
            'syncAiDiscoveryPageProfiles',
            SyncAiDiscoveryPageProfilesAction::class,
        );
});

it('declares settings permissions supported integrations and cache invalidation accurately', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');

    expect(data_get($manifest, 'dependencies.supports', []))->toContain(
        'capell-app/blog',
        'capell-app/publishing-studio',
        'capell-app/url-manager',
    )
        ->and(data_get($manifest, 'dependencies.requires', []))->toContain('capell-app/core')
        ->and(data_get($manifest, 'settings', []))->toContain(
            AIOrchestratorSettings::class,
            SeoSuiteSettings::class,
        )
        ->and(data_get($manifest, 'permissions', []))->toContain(
            'View:SeoAuditPage',
            'Manage:BrokenLinks',
            'Manage:AiDiscovery',
            'Manage:SeoSuiteSettings',
            'Use:AiCreator',
        )
        ->and(data_get($manifest, 'performance.cacheSafety.cacheable', false))->toBeTrue()
        ->and(data_get($manifest, 'performance.cacheSafety.invalidationSources', []))->toContain([
            'model' => Page::class,
            'events' => ['saved', 'deleted'],
        ], [
            'model' => AiDiscoverySiteProfile::class,
            'events' => ['saved', 'deleted'],
        ], [
            'model' => AiDiscoveryPageProfile::class,
            'events' => ['saved', 'deleted'],
        ], [
            'model' => AiDiscoveryCrawlerRule::class,
            'events' => ['saved', 'deleted'],
        ])
        ->and(data_get($manifest, 'security.publicSurface.auth'))->toBe('public')
        ->and(data_get($manifest, 'security.publicSurface.routeNames', []))->toContain(
            'capell-frontend.llms-txt',
            'capell-frontend.llms-full-txt',
            'capell-frontend.robots-txt',
            'capell-frontend.page-markdown-home',
            'capell-frontend.page-markdown',
        );
});

it('declares implemented admin pages routes and no longer defers core seo suite surfaces', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');
    $contributes = data_get($manifest, 'contributes');

    throw_unless(is_array($contributes), RuntimeException::class, 'Expected SEO Suite manifest contributions.');

    expect(data_get($manifest, 'description'))->not->toContain('planned generated-output diagnostics')
        ->and(data_get($manifest, 'marketplace.summary'))->not->toContain('planned generated-output diagnostics')
        ->and($contributes)->toContain([
            'type' => 'admin-page',
            'class' => SeoAuditPageContribution::class,
            'pageClass' => SeoAuditPage::class,
            'labelKey' => 'capell-seo-suite::generic.seo_audit',
        ])
        ->and($contributes)->toContain([
            'type' => 'admin-page',
            'class' => BrokenLinksPageContribution::class,
            'pageClass' => BrokenLinksPage::class,
            'labelKey' => 'capell-admin::navigation.broken_links',
        ])
        ->and($contributes)->toContain([
            'type' => 'admin-page',
            'class' => NotFoundUrlsPageContribution::class,
            'pageClass' => NotFoundUrlsPage::class,
            'labelKey' => 'capell-admin::navigation.not_found',
        ])
        ->and($contributes)->toContain([
            'type' => 'admin-page',
            'class' => AiDiscoveryPageContribution::class,
            'pageClass' => AiDiscoveryPage::class,
            'labelKey' => 'capell-seo-suite::generic.ai_discovery',
        ])
        ->and($contributes)->toContain([
            'type' => 'admin-page',
            'class' => TranslationCoveragePageContribution::class,
            'pageClass' => TranslationCoveragePage::class,
            'labelKey' => 'capell-admin::navigation.translation_coverage',
        ])
        ->and($contributes)->toContain([
            'type' => 'admin-page',
            'class' => SearchRankingsPageContribution::class,
            'pageClass' => SearchRankingsPage::class,
            'labelKey' => 'capell-seo-suite::generic.search_rankings',
        ])
        ->and($contributes)->toContain([
            'type' => 'route',
            'class' => AiDiscoveryRoutesContribution::class,
            'routes' => [
                'capell-frontend.llms-txt',
                'capell-frontend.llms-full-txt',
                'capell-frontend.robots-txt',
                'capell-frontend.page-markdown-home',
                'capell-frontend.page-markdown',
            ],
        ])
        ->and($contributes)->toContain([
            'type' => 'model',
            'class' => SeoSuiteModelsContribution::class,
            'modelClasses' => [
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
            ],
        ])
        ->and($contributes)->toContain([
            'type' => 'dashboard-widget',
            'class' => SeoSuiteDashboardFilamentWidgetsContribution::class,
            'widgetClasses' => [
                SearchConsoleOverviewFilamentWidget::class,
                TopSearchPagesFilamentWidget::class,
                SearchMovementFilamentWidget::class,
                SeoOpportunitiesFilamentWidget::class,
                SearchIntelligenceFilamentWidget::class,
                AiDiscoveryCoverageFilamentWidget::class,
            ],
        ])
        ->and($contributes)->toContain([
            'type' => 'scheduled-job',
            'class' => SeoSuitePageSpeedScheduleContribution::class,
            'command' => 'capell:seo-suite:pagespeed-audit --notify',
            'name' => 'capell-seo-suite-pagespeed-audit',
            'frequency' => 'weeklyOn:1:06:00',
            'enabledWhen' => 'capell-seo-suite.pagespeed.enabled and seo_suite.pagespeed_audit_enabled',
        ])
        ->and($contributes)->toContain([
            'type' => 'console-command',
            'class' => SeoSuiteConsoleCommandsContribution::class,
            'commands' => [
                'capell:admin-clear-ai-cache',
                'capell:seo-suite-doctor',
                'capell:seo-suite-install',
                'capell:admin-monitor-ai-usage',
                'capell:seo-suite:pagespeed-audit',
                'capell:seo-suite:refresh-ai-discovery-markdown',
                'capell:seo-suite-setup',
                'capell:seo-suite-sync-search-console',
                'capell:admin-test-openai',
            ],
            'commandClasses' => [
                ClearAiCacheCommand::class,
                DoctorCommand::class,
                InstallCommand::class,
                MonitorAiUsageCommand::class,
                PageSpeedAuditCommand::class,
                RefreshAiDiscoveryMarkdownCommand::class,
                SetupCommand::class,
                SyncSearchConsoleCommand::class,
                TestOpenAiConnectionCommand::class,
            ],
        ])
        ->and($contributes)->toContain([
            'type' => 'setting',
            'class' => SeoSuiteSettingsContribution::class,
            'settingsClasses' => [
                AIOrchestratorSettings::class,
                SeoSuiteSettings::class,
            ],
            'settingsGroups' => ['ai-orchestrator', 'seo_suite', 'frontend'],
        ])
        ->and($contributes)->toContain([
            'type' => 'health-check',
            'class' => SeoSuiteHealthContribution::class,
            'checkClass' => SeoSuiteHealthCheck::class,
        ])
        ->and(data_get($manifest, 'contributionTraceability.deferredContributions'))->toBe([]);

    foreach ($contributes as $contribution) {
        throw_unless(is_array($contribution), RuntimeException::class, 'Expected SEO Suite manifest contribution to be an array.');

        $class = $contribution['class'] ?? null;

        expect(is_string($class) ? class_implements($class) : [])->toContain(ExtensionContribution::class);
    }

    expect(class_implements(SeoSuiteDashboardFilamentWidgetsContribution::class))->toContain(RegistersExtensionFilamentWidget::class)
        ->and(class_implements(SeoSuitePageSpeedScheduleContribution::class))->toContain(RunsScheduledExtensionJob::class)
        ->and(class_implements(SeoSuiteSettingsContribution::class))->toContain(RegistersExtensionSetting::class)
        ->and(class_implements(SeoSuiteHealthContribution::class))->toContain(ChecksExtensionHealth::class);
});
