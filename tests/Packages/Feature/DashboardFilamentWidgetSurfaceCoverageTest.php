<?php

declare(strict_types=1);

use Capell\CampaignStudio\Actions\BuildCampaignOverviewStatsAction;
use Capell\CampaignStudio\Actions\BuildTopCampaignStudioQueryAction;
use Capell\CampaignStudio\Actions\BuildTopLandingPagesQueryAction;
use Capell\CampaignStudio\Data\Dashboard\CampaignConversionSummaryData;
use Capell\CampaignStudio\Data\Dashboard\CampaignLandingPageSummaryData;
use Capell\CampaignStudio\Filament\Widgets\CampaignOverviewStatsFilamentWidget;
use Capell\CampaignStudio\Filament\Widgets\TopCampaignStudioFilamentWidget;
use Capell\CampaignStudio\Filament\Widgets\TopLandingPagesFilamentWidget;
use Capell\Diagnostics\Actions\DashboardReports\BuildQueueOperationsStatsAction;
use Capell\Diagnostics\Data\QueueOperationsStatsData;
use Capell\Diagnostics\Filament\Widgets\QueueOperationsStatsFilamentWidget;
use Capell\GA4Reports\Actions\BuildGA4ReportsOverviewAction;
use Capell\GA4Reports\Data\GA4ReportsOverviewData;
use Capell\GA4Reports\Filament\Widgets\GA4ReportsOverviewStatsFilamentWidget;
use Capell\HtmlCache\Actions\Dashboard\BuildHtmlCacheDashboardStatsAction;
use Capell\HtmlCache\Actions\Dashboard\BuildHtmlCacheStaleQueueRowsAction;
use Capell\HtmlCache\Actions\Dashboard\BuildHtmlCacheUrlRowsAction;
use Capell\HtmlCache\Data\Dashboard\HtmlCacheDashboardStatsData;
use Capell\HtmlCache\Filament\Widgets\CacheCoverageUrlsFilamentWidget;
use Capell\HtmlCache\Filament\Widgets\HtmlCacheOverviewFilamentWidget;
use Capell\HtmlCache\Filament\Widgets\HtmlCacheStaleQueueFilamentWidget;
use Capell\Insights\Actions\BuildInsightsOverviewStatsAction;
use Capell\Insights\Actions\BuildLiveInsightsStatsAction;
use Capell\Insights\Actions\BuildPopularPagesQueryAction;
use Capell\Insights\Actions\BuildRecentJourneysQueryAction;
use Capell\Insights\Actions\BuildTopActionsQueryAction;
use Capell\Insights\Actions\BuildTrendingPagesQueryAction;
use Capell\Insights\Filament\Widgets\InsightsOverviewStatsFilamentWidget;
use Capell\Insights\Filament\Widgets\LiveInsightsStatsFilamentWidget;
use Capell\Insights\Filament\Widgets\PopularPagesFilamentWidget;
use Capell\Insights\Filament\Widgets\RecentJourneysFilamentWidget;
use Capell\Insights\Filament\Widgets\TopActionsFilamentWidget;
use Capell\Insights\Filament\Widgets\TrendingPagesFilamentWidget;
use Capell\LoginAudit\Actions\BuildLoginAuditsQueryAction;
use Capell\LoginAudit\Filament\Widgets\LoginAuditsFilamentWidget;
use Capell\LoginAudit\Models\LoginAudit;
use Capell\Search\Actions\BuildTopSearchesQueryAction;
use Capell\Search\Actions\BuildTrendingSearchesQueryAction;
use Capell\Search\Actions\BuildZeroResultSearchesQueryAction;
use Capell\Search\Data\SearchTermSummaryData;
use Capell\Search\Filament\Widgets\SearchOverviewStatsFilamentWidget;
use Capell\Search\Filament\Widgets\TopSearchesFilamentWidget;
use Capell\Search\Filament\Widgets\TrendingSearchesFilamentWidget;
use Capell\Search\Filament\Widgets\ZeroResultSearchesFilamentWidget;
use Capell\SeoSuite\Actions\Dashboard\BuildAiDiscoveryCoverageStatsAction;
use Capell\SeoSuite\Actions\Dashboard\BuildSearchConsoleDashboardStatsAction;
use Capell\SeoSuite\Actions\Dashboard\BuildSeoIntelligenceRowsAction;
use Capell\SeoSuite\Actions\Dashboard\BuildSeoOpportunityRowsAction;
use Capell\SeoSuite\Data\Dashboard\SearchConsoleDashboardStatsData;
use Capell\SeoSuite\Filament\Widgets\AiDiscoveryCoverageFilamentWidget;
use Capell\SeoSuite\Filament\Widgets\SearchConsoleOverviewFilamentWidget;
use Capell\SeoSuite\Filament\Widgets\SearchIntelligenceFilamentWidget;
use Capell\SeoSuite\Filament\Widgets\SeoOpportunitiesFilamentWidget;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

it('builds dashboard table widgets from their package query actions', function (): void {
    app()->instance(BuildGA4ReportsOverviewAction::class, new class
    {
        public function handle(): GA4ReportsOverviewData
        {
            return new GA4ReportsOverviewData(
                totalUsers: 345,
                sessions: 234,
                screenPageViews: 1234,
                eventCount: 56,
                conversions: 12,
                engagementRate: 0.456,
                averageSessionDuration: 98.4,
            );
        }
    });

    app()->instance(BuildTopCampaignStudioQueryAction::class, new class
    {
        /**
         * @return Collection<int, CampaignConversionSummaryData>
         */
        public function handle(int $limit, mixed $startsAt, mixed $endsAt): Collection
        {
            expect($limit)->toBe(5);

            return collect([
                new CampaignConversionSummaryData(
                    campaignGroupId: 10,
                    campaignName: 'Spring launch',
                    conversions: 9,
                    visits: 120,
                    conversionRate: 7.5,
                ),
            ]);
        }
    });

    app()->instance(BuildSeoIntelligenceRowsAction::class, new class
    {
        /**
         * @return Collection<int, array{id: string, type: string, query: string, url: string, message: string, priority: int, impressions: int, clicks: int, ctr: float, average_position: string}>
         */
        public function handle(int $limit): Collection
        {
            expect($limit)->toBe(5);

            return collect([
                [
                    'id' => 'seo-row-1',
                    'type' => 'Opportunity',
                    'query' => 'cms coverage',
                    'url' => 'https://example.test/cms',
                    'message' => 'Improve search result.',
                    'priority' => 90,
                    'impressions' => 500,
                    'clicks' => 25,
                    'ctr' => 5.0,
                    'average_position' => '4.3',
                ],
            ]);
        }
    });

    $ga4Widget = new GA4ReportsOverviewStatsFilamentWidget;
    $campaignWidget = new TopCampaignStudioFilamentWidget;
    $seoWidget = new SearchIntelligenceFilamentWidget;

    $ga4Records = invokeDashboardFilamentWidgetMethod($ga4Widget, 'getRecords');
    $campaignRecords = invokeDashboardFilamentWidgetMethod($campaignWidget, 'records');
    $seoTable = $seoWidget->table(packageDashboardFilamentWidgetTable());
    $seoRecords = ($seoTable->getDataSource())();

    expect($ga4Widget->table(packageDashboardFilamentWidgetTable())->getColumns())->toHaveCount(3)
        ->and($ga4Records)->toBeInstanceOf(Collection::class)
        ->and($ga4Records->pluck('value')->all())->toBe(['1,234', '234', '345', '56', '45.6%', '98.4s', '12'])
        ->and($campaignWidget->table(packageDashboardFilamentWidgetTable())->getColumns())->toHaveCount(4)
        ->and($campaignRecords)->toBeInstanceOf(Collection::class)
        ->and($campaignRecords->first())->toMatchArray([
            'campaign' => 'Spring launch',
            'visits' => 120,
            'conversions' => 9,
            'conversion_rate' => '7.5%',
        ])
        ->and($seoTable->getColumns())->toHaveCount(5)
        ->and($seoRecords)->toHaveCount(1)
        ->and($seoRecords->first()['query'])->toBe('cms coverage');
});

it('builds campaign dashboard overview and landing page widgets from package actions', function (): void {
    app()->instance(BuildCampaignOverviewStatsAction::class, new class
    {
        /**
         * @return array<string, int|float>
         */
        public function handle(mixed $startsAt = null, mixed $endsAt = null): array
        {
            return [
                'active_campaign-studio' => 3,
                'conversions' => 42,
                'conversion_rate' => 12.5,
            ];
        }
    });

    app()->instance(BuildTopLandingPagesQueryAction::class, new class
    {
        /**
         * @return Collection<int, CampaignLandingPageSummaryData>
         */
        public function handle(int $limit, mixed $startsAt, mixed $endsAt): Collection
        {
            expect($limit)->toBe(5);

            return collect([
                new CampaignLandingPageSummaryData(
                    landingPageId: 17,
                    landingPageName: 'Pricing campaign page',
                    campaignName: 'Expansion',
                    conversions: 11,
                ),
            ]);
        }
    });

    $stats = invokeDashboardFilamentWidgetMethod(new CampaignOverviewStatsFilamentWidget, 'getStats');
    $landingPagesTable = (new TopLandingPagesFilamentWidget)->table(packageDashboardFilamentWidgetTable());
    $landingPages = packageDashboardFilamentWidgetRecords($landingPagesTable);

    expect($stats)->toHaveCount(3)
        ->and((string) $stats[0]->getValue())->toBe('3')
        ->and((string) $stats[1]->getValue())->toBe('42')
        ->and((string) $stats[2]->getValue())->toBe('12.5%')
        ->and($landingPagesTable->getColumns())->toHaveCount(3)
        ->and($landingPages->first())->toMatchArray([
            'id' => 17,
            'landing_page' => 'Pricing campaign page',
            'campaign' => 'Expansion',
            'conversions' => 11,
        ]);
});

it('builds insights dashboard table widgets from their analytics actions', function (): void {
    app()->instance(BuildInsightsOverviewStatsAction::class, new class
    {
        /**
         * @return Collection<int, array{id: string, label: string, value: int}>
         */
        public function handle(mixed $window): Collection
        {
            return collect([
                ['id' => 'views', 'label' => 'Page views', 'value' => 120],
            ]);
        }
    });
    app()->instance(BuildPopularPagesQueryAction::class, new class
    {
        /**
         * @return Collection<int, array{path: string, page_views: int, unique_visits: int, clicks: int}>
         */
        public function handle(mixed $window, int $limit): Collection
        {
            expect($limit)->toBe(5);

            return collect([
                ['path' => '/home', 'page_views' => 120, 'unique_visits' => 80, 'clicks' => 12],
            ]);
        }
    });
    app()->instance(BuildTrendingPagesQueryAction::class, new class
    {
        /**
         * @return Collection<int, array{path: string, current_page_views: int, previous_page_views: int, change: int, change_percentage: float}>
         */
        public function handle(mixed $window, int $limit): Collection
        {
            expect($limit)->toBe(5);

            return collect([
                ['path' => '/pricing', 'current_page_views' => 60, 'previous_page_views' => 30, 'change' => 30, 'change_percentage' => 100.0],
            ]);
        }
    });
    app()->instance(BuildTopActionsQueryAction::class, new class
    {
        /**
         * @return Collection<int, array{action: string, events: int}>
         */
        public function handle(mixed $window, int $limit): Collection
        {
            expect($limit)->toBe(5);

            return collect([
                ['action' => 'cta.clicked', 'events' => 33],
            ]);
        }
    });
    app()->instance(BuildRecentJourneysQueryAction::class, new class
    {
        /**
         * @return Collection<int, array{id: string, visit: string, steps: int, last_path: string}>
         */
        public function handle(int $limit, mixed $window): Collection
        {
            expect($limit)->toBe(5);

            return collect([
                ['id' => 'visit-1', 'visit' => 'Visitor 1', 'steps' => 4, 'last_path' => '/checkout'],
            ]);
        }
    });
    app()->instance(BuildLiveInsightsStatsAction::class, new class
    {
        /**
         * @return Collection<int, array{metric: string, value: string}>
         */
        public function handle(int $minutes): Collection
        {
            expect($minutes)->toBe(15);

            return collect([
                ['metric' => 'Active visitors', 'value' => '9'],
            ]);
        }
    });

    $overview = packageDashboardFilamentWidgetRecords((new InsightsOverviewStatsFilamentWidget)->table(packageDashboardFilamentWidgetTable()));
    $popularPages = packageDashboardFilamentWidgetRecords((new PopularPagesFilamentWidget)->table(packageDashboardFilamentWidgetTable()));
    $trendingPages = packageDashboardFilamentWidgetRecords((new TrendingPagesFilamentWidget)->table(packageDashboardFilamentWidgetTable()));
    $topActions = packageDashboardFilamentWidgetRecords((new TopActionsFilamentWidget)->table(packageDashboardFilamentWidgetTable()));
    $journeys = packageDashboardFilamentWidgetRecords((new RecentJourneysFilamentWidget)->table(packageDashboardFilamentWidgetTable()));
    $liveStatsWidget = new LiveInsightsStatsFilamentWidget;
    $liveStats = packageDashboardFilamentWidgetRecords($liveStatsWidget->table(packageDashboardFilamentWidgetTable()));

    expect($overview->first())->toMatchArray(['id' => 'views', 'value' => 120])
        ->and($popularPages->first())->toMatchArray(['id' => 'popular-page-0', 'path' => '/home'])
        ->and($trendingPages->first())->toMatchArray(['id' => 'trending-page-0', 'change' => 30])
        ->and($topActions->first())->toMatchArray(['id' => 'top-action-0', 'event_name' => 'cta.clicked'])
        ->and($journeys->first())->toMatchArray(['id' => 'journey-visit-1', 'last_path' => '/checkout'])
        ->and($liveStats->first())->toMatchArray(['metric' => 'Active visitors', 'value' => '9'])
        ->and($liveStatsWidget->getPollingInterval())->toBe('30s');
});

it('builds search dashboard widgets from top trending and zero result search actions', function (): void {
    app()->instance(BuildTopSearchesQueryAction::class, new class
    {
        /**
         * @return Collection<int, SearchTermSummaryData>
         */
        public function handle(mixed $window, ?int $limit = null): Collection
        {
            return collect($limit === null ? [
                new SearchTermSummaryData('cms', 'cms', 10, 100),
                new SearchTermSummaryData('support', 'support', 5, 20),
            ] : [
                new SearchTermSummaryData('cms', 'cms', 10, 100),
            ]);
        }
    });
    app()->instance(BuildTrendingSearchesQueryAction::class, new class
    {
        /**
         * @return Collection<int, SearchTermSummaryData>
         */
        public function handle(mixed $window, int $limit): Collection
        {
            expect($limit)->toBe(5);

            return collect([
                new SearchTermSummaryData('pricing', 'pricing', 8, 30, 75.5),
            ]);
        }
    });
    app()->instance(BuildZeroResultSearchesQueryAction::class, new class
    {
        /**
         * @return Collection<int, SearchTermSummaryData>
         */
        public function handle(mixed $window, ?int $limit = null): Collection
        {
            return collect([
                new SearchTermSummaryData('missing docs', 'missing docs', 3, 0),
            ]);
        }
    });

    $overview = invokeDashboardFilamentWidgetMethod(new SearchOverviewStatsFilamentWidget, 'getViewData');
    $topSearches = packageDashboardFilamentWidgetRecords((new TopSearchesFilamentWidget)->table(packageDashboardFilamentWidgetTable()));
    $trendingSearches = packageDashboardFilamentWidgetRecords((new TrendingSearchesFilamentWidget)->table(packageDashboardFilamentWidgetTable()));
    $zeroResults = packageDashboardFilamentWidgetRecords((new ZeroResultSearchesFilamentWidget)->table(packageDashboardFilamentWidgetTable()));

    expect($overview)->toMatchArray([
        'totalSearches' => 15,
        'uniqueQueries' => 2,
        'totalResults' => 120,
        'zeroResultRate' => 20.0,
    ])
        ->and($topSearches->first())->toMatchArray(['id' => 'top-search-0', 'query' => 'cms', 'searches' => 10])
        ->and($trendingSearches->first())->toMatchArray(['id' => 'trending-search-0', 'trendPercentage' => 75.5])
        ->and($zeroResults->first())->toMatchArray(['id' => 'zero-result-search-0', 'query' => 'missing docs']);
});

it('builds html cache seo and diagnostics dashboard widgets from operational reports', function (): void {
    app()->instance(BuildHtmlCacheDashboardStatsAction::class, new class
    {
        public function handle(): HtmlCacheDashboardStatsData
        {
            return new HtmlCacheDashboardStatsData(
                pageUrls: 100,
                cachedPageUrls: 80,
                uncachedPageUrls: 20,
                coverageRate: 80.0,
                trackedCachedUrls: 160,
                stalePending: 4,
                staleFailed: 2,
                cachedTrafficCoverageRate: 91.5,
            );
        }
    });
    app()->instance(BuildHtmlCacheUrlRowsAction::class, new class
    {
        /**
         * @return Collection<int, array{state: string, url: string, site: string, hits: int, last_seen: string}>
         */
        public function handle(string $mode, int $limit): Collection
        {
            expect($mode)->toBe('coverage')
                ->and($limit)->toBe(6);

            return collect([
                ['state' => 'cached', 'url' => '/about', 'site' => 'Main', 'hits' => 15, 'last_seen' => 'today'],
            ]);
        }
    });
    app()->instance(BuildHtmlCacheStaleQueueRowsAction::class, new class
    {
        /**
         * @return Collection<int, array{url: string, status: string, attempts: int, reason: string, updated: string}>
         */
        public function handle(int $limit): Collection
        {
            expect($limit)->toBe(5);

            return collect([
                ['url' => '/old', 'status' => 'pending', 'attempts' => 1, 'reason' => 'page updated', 'updated' => 'today'],
            ]);
        }
    });
    app()->instance(BuildSearchConsoleDashboardStatsAction::class, new class
    {
        public function handle(): SearchConsoleDashboardStatsData
        {
            return new SearchConsoleDashboardStatsData(
                clicks: 1200,
                impressions: 45000,
                ctr: 2.7,
                averagePosition: 8.4,
                risingPages: 6,
                decliningPages: 2,
                windowStart: '2026-05-01',
                windowEnd: '2026-05-28',
                mixedWindows: false,
            );
        }
    });
    app()->instance(BuildAiDiscoveryCoverageStatsAction::class, new class
    {
        /**
         * @return array<string, int>
         */
        public function handle(): array
        {
            return [
                'included' => 70,
                'excluded' => 5,
                'missing_summary' => 3,
                'stale_markdown' => 2,
            ];
        }
    });
    app()->instance(BuildSeoOpportunityRowsAction::class, new class
    {
        /**
         * @return Collection<int, array{page: string, score: int, critical_count: int, warning_count: int, notices: int}>
         */
        public function handle(int $limit): Collection
        {
            expect($limit)->toBe(5);

            return collect([
                ['page' => 'Home', 'score' => 72, 'critical_count' => 1, 'warning_count' => 2, 'notices' => 3],
            ]);
        }
    });
    app()->instance(BuildQueueOperationsStatsAction::class, new class
    {
        public function handle(): QueueOperationsStatsData
        {
            return new QueueOperationsStatsData(
                totalJobs: 1000,
                succeededJobs: 940,
                failedJobs: 10,
                runningJobs: 2,
                pendingJobs: 48,
                averageRuntimeSeconds: 12,
                dailyTotals: [100, 200],
                dailyFailures: [1, 2],
            );
        }
    });

    $cacheStats = invokeDashboardFilamentWidgetMethod(new HtmlCacheOverviewFilamentWidget, 'getStats');
    $coverageRows = packageDashboardFilamentWidgetRecords((new CacheCoverageUrlsFilamentWidget)->table(packageDashboardFilamentWidgetTable()));
    $staleRows = packageDashboardFilamentWidgetRecords((new HtmlCacheStaleQueueFilamentWidget)->table(packageDashboardFilamentWidgetTable()));
    $searchConsoleStats = invokeDashboardFilamentWidgetMethod(new SearchConsoleOverviewFilamentWidget, 'getStats');
    $aiCoverageStats = invokeDashboardFilamentWidgetMethod(new AiDiscoveryCoverageFilamentWidget, 'getStats');
    $seoRows = packageDashboardFilamentWidgetRecords((new SeoOpportunitiesFilamentWidget)->table(packageDashboardFilamentWidgetTable()));
    $queueStats = invokeDashboardFilamentWidgetMethod(new QueueOperationsStatsFilamentWidget, 'getStats');

    expect($cacheStats)->toHaveCount(6)
        ->and((string) $cacheStats[0]->getValue())->toBe('80.0%')
        ->and($coverageRows->first())->toMatchArray(['state' => 'cached', 'url' => '/about'])
        ->and($staleRows->first())->toMatchArray(['url' => '/old', 'status' => 'pending'])
        ->and((string) $searchConsoleStats[0]->getValue())->toBe('1,200')
        ->and((string) $searchConsoleStats[3]->getValue())->toBe('8.4')
        ->and((string) $aiCoverageStats[0]->getValue())->toBe('70')
        ->and($seoRows->first())->toMatchArray(['page' => 'Home', 'score' => 72])
        ->and((string) $queueStats[0]->getValue())->toBe('1,000')
        ->and((string) $queueStats[3]->getValue())->toBe('48');
});

it('builds the login audit dashboard widget from the audit query action', function (): void {
    app()->instance(BuildLoginAuditsQueryAction::class, new class
    {
        /**
         * @return Builder<LoginAudit>
         */
        public function handle(): Builder
        {
            return LoginAudit::query();
        }
    });

    $widget = new LoginAuditsFilamentWidget;
    $query = invokeDashboardFilamentWidgetMethod($widget, 'getTableQuery');
    $table = $widget->table(packageDashboardFilamentWidgetTable());

    expect($query->getModel())->toBeInstanceOf(LoginAudit::class)
        ->and($table->getColumns())->toHaveCount(4)
        ->and(packageDashboardFilamentWidgetActionNames($table->getHeaderActions()))->toContain('view-all');
});

function packageDashboardFilamentWidgetTable(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldIgnoreMissing();
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null)->byDefault();
    $livewire->shouldReceive('getTableFilterState')->andReturn([])->byDefault();
    $livewire->shouldReceive('isTableLoaded')->andReturnTrue()->byDefault();
    $livewire->shouldReceive('getTableArguments')->andReturn([])->byDefault();

    return Table::make($livewire);
}

function invokeDashboardFilamentWidgetMethod(object $widget, string $methodName): mixed
{
    $method = new ReflectionMethod($widget::class, $methodName);

    return $method->invoke($widget);
}

function packageDashboardFilamentWidgetRecords(Table $table): Collection
{
    $dataSource = $table->getDataSource();

    return $dataSource();
}

/**
 * @param  array<array-key, mixed>  $actions
 * @return array<int, string>
 */
function packageDashboardFilamentWidgetActionNames(array $actions): array
{
    return collect($actions)
        ->flatten()
        ->filter(fn (mixed $action): bool => is_object($action) && method_exists($action, 'getName'))
        ->map(fn (object $action): string => $action->getName())
        ->values()
        ->all();
}
