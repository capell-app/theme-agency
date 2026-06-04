<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\SeoSuite\Actions\BuildCrawlerPreviewReportAction;
use Capell\SeoSuite\Actions\BuildMarketplaceStructuredDataFreshnessWarningsAction;
use Capell\SeoSuite\Actions\BuildSchemaTemplateReportAction;
use Capell\SeoSuite\Actions\BuildSeoSuiteDoctorReportAction;
use Capell\SeoSuite\Actions\ClearAiDiscoveryCacheAction;
use Capell\SeoSuite\Actions\RefreshStaleAiDiscoveryMarkdownAction;
use Capell\SeoSuite\Actions\SyncAiDiscoveryPageProfilesAction;
use Capell\SeoSuite\Filament\Pages\AiDiscoveryPage;
use Capell\SeoSuite\Filament\Pages\BrokenLinksPage;
use Capell\SeoSuite\Filament\Pages\NotFoundUrlsPage;
use Capell\SeoSuite\Filament\Pages\SearchRankingsPage;
use Capell\SeoSuite\Filament\Pages\SeoAuditPage;
use Capell\SeoSuite\Filament\Pages\TranslationCoveragePage;
use Capell\SeoSuite\Manifest\AiDiscoveryPageContribution;
use Capell\SeoSuite\Manifest\AiDiscoveryRoutesContribution;
use Capell\SeoSuite\Manifest\BrokenLinksPageContribution;
use Capell\SeoSuite\Manifest\NotFoundUrlsPageContribution;
use Capell\SeoSuite\Manifest\SearchRankingsPageContribution;
use Capell\SeoSuite\Manifest\SeoAuditPageContribution;
use Capell\SeoSuite\Manifest\TranslationCoveragePageContribution;
use Illuminate\Support\Facades\File;

it('declares implemented diagnostics commands tables and capabilities', function (): void {
    $manifest = json_decode(
        File::get(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['commands']['doctor'] ?? null)->toBe('capell:seo-suite-doctor')
        ->and($manifest['database']['requiredTables'] ?? [])->toContain(
            'ai_discovery_page_profiles',
            'ai_discovery_snapshots',
            'broken_links',
            'page_seo_snapshots',
            'page_speed_audit_runs',
            'search_console_url_metrics',
        )
        ->and($manifest['capabilities'] ?? [])->toContain(
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

    expect($manifest['actions'] ?? [])
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
    $manifest = json_decode(
        File::get(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['dependencies']['supports'] ?? [])->toContain(
        'capell-app/blog',
        'capell-app/publishing-studio',
        'capell-app/url-manager',
    )
        ->and($manifest['settings'] ?? [])->toContain(
            'Capell\\SeoSuite\\Settings\\AIOrchestratorSettings',
            'Capell\\SeoSuite\\Settings\\SeoSuiteSettings',
        )
        ->and($manifest['permissions'] ?? [])->toContain(
            'View:SeoAuditPage',
            'Manage:BrokenLinks',
            'Manage:AiDiscovery',
            'Manage:SeoSuiteSettings',
            'Use:AiCreator',
        )
        ->and($manifest['performance']['cacheSafety']['cacheable'] ?? false)->toBeTrue()
        ->and($manifest['performance']['cacheSafety']['invalidationSources'] ?? [])->toContain([
            'model' => 'Capell\\Core\\Models\\Page',
            'events' => ['saved', 'deleted'],
        ], [
            'model' => 'Capell\\SeoSuite\\Models\\AiDiscoverySiteProfile',
            'events' => ['saved', 'deleted'],
        ], [
            'model' => 'Capell\\SeoSuite\\Models\\AiDiscoveryPageProfile',
            'events' => ['saved', 'deleted'],
        ], [
            'model' => 'Capell\\SeoSuite\\Models\\AiDiscoveryCrawlerRule',
            'events' => ['saved', 'deleted'],
        ]);
});

it('declares implemented admin pages routes and no longer defers core seo suite surfaces', function (): void {
    $manifest = json_decode(
        File::get(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['description'])->not->toContain('planned generated-output diagnostics')
        ->and($manifest['marketplace']['summary'])->not->toContain('planned generated-output diagnostics')
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-page',
            'class' => SeoAuditPageContribution::class,
            'pageClass' => SeoAuditPage::class,
            'labelKey' => 'capell-seo-suite::generic.seo_audit',
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-page',
            'class' => BrokenLinksPageContribution::class,
            'pageClass' => BrokenLinksPage::class,
            'labelKey' => 'capell-admin::navigation.broken_links',
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-page',
            'class' => NotFoundUrlsPageContribution::class,
            'pageClass' => NotFoundUrlsPage::class,
            'labelKey' => 'capell-admin::navigation.not_found',
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-page',
            'class' => AiDiscoveryPageContribution::class,
            'pageClass' => AiDiscoveryPage::class,
            'labelKey' => 'capell-seo-suite::generic.ai_discovery',
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-page',
            'class' => TranslationCoveragePageContribution::class,
            'pageClass' => TranslationCoveragePage::class,
            'labelKey' => 'capell-admin::navigation.translation_coverage',
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-page',
            'class' => SearchRankingsPageContribution::class,
            'pageClass' => SearchRankingsPage::class,
            'labelKey' => 'capell-seo-suite::generic.search_rankings',
        ])
        ->and($manifest['contributes'])->toContain([
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
        ->and($manifest['contributionTraceability']['deferredContributions'])->not->toContain('admin-page', 'model', 'route');

    foreach ($manifest['contributes'] as $contribution) {
        $class = $contribution['class'] ?? null;

        expect(is_string($class) ? class_implements($class) : [])->toContain(ExtensionContribution::class);
    }
});
