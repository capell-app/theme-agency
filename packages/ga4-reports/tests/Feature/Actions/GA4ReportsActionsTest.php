<?php

declare(strict_types=1);

use Capell\GA4Reports\Actions\BuildGA4ReportsDigestAction;
use Capell\GA4Reports\Actions\BuildGA4ReportsOverviewAction;
use Capell\GA4Reports\Actions\BuildGA4ReportsTrendAction;
use Capell\GA4Reports\Actions\BuildTopGA4ReportsPagesAction;
use Capell\GA4Reports\Actions\ExportGA4ReportsDigestCsvAction;
use Capell\GA4Reports\Actions\PersistGA4ReportsDailyMetricAction;
use Capell\GA4Reports\Actions\PersistGA4ReportsPageMetricAction;
use Capell\GA4Reports\Actions\SyncGA4ReportsMetricsAction;
use Capell\GA4Reports\Contracts\GA4ReportsDataClientInterface;
use Capell\GA4Reports\Data\GA4ReportsDailyMetricData;
use Capell\GA4Reports\Data\GA4ReportsPageMetricData;
use Capell\GA4Reports\Data\GA4ReportsWindowData;
use Capell\GA4Reports\Models\GA4ReportsDailyMetric;
use Capell\GA4Reports\Models\GA4ReportsPageMetric;
use Capell\GA4Reports\Models\GA4ReportsSyncRun;
use Capell\GA4Reports\Settings\GA4ReportsSettings;
use Capell\GA4Reports\Tests\Fakes\FakeGA4ReportsDataClient;
use Capell\GA4Reports\Tests\GA4ReportsTestCase;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

uses(GA4ReportsTestCase::class);

function configureGA4ReportsSettings(): void
{
    $settings = new GA4ReportsSettings;
    $settings->enabled = true;
    $settings->property_id = '123456789';
    $settings->credentials_path = '/tmp/ga4-reports.json';
    $settings->sync_days = 2;
    $settings->route_slug = 'ga4-reports';

    app()->instance(GA4ReportsSettings::class, $settings);
}

/**
 * @return list<list<string>>
 */
function ga4ReportsCsvRows(string $csv): array
{
    $rows = [];

    foreach (explode("\n", trim($csv)) as $row) {
        $fields = [];

        foreach (str_getcsv($row) as $field) {
            $fields[] = $field ?? '';
        }

        $rows[] = $fields;
    }

    return $rows;
}

it('syncs GA4 metrics idempotently into local reporting tables', function (): void {
    Date::setTestNow(Date::create(2026, 5, 5, 12, 0, 0));
    configureGA4ReportsSettings();

    app()->instance(GA4ReportsDataClientInterface::class, new FakeGA4ReportsDataClient(
        configured: true,
        dailyMetrics: [
            new GA4ReportsDailyMetricData(
                propertyId: '123456789',
                metricDate: CarbonImmutable::parse('2026-05-04'),
                totalUsers: 10,
                sessions: 20,
                screenPageViews: 30,
                engagedSessions: 15,
                engagementRate: 0.75,
                averageSessionDuration: 42.5,
                eventCount: 80,
                conversions: 4,
            ),
        ],
        pageMetrics: [
            new GA4ReportsPageMetricData(
                propertyId: '123456789',
                metricDate: CarbonImmutable::parse('2026-05-04'),
                pagePath: '/about',
                pageTitle: 'About',
                totalUsers: 8,
                sessions: 12,
                screenPageViews: 22,
                eventCount: 40,
                conversions: 2,
            ),
        ],
    ));

    $firstResult = SyncGA4ReportsMetricsAction::run();
    $secondResult = SyncGA4ReportsMetricsAction::run();

    Date::setTestNow();

    expect($firstResult->synced)->toBeTrue()
        ->and($secondResult->synced)->toBeTrue()
        ->and(GA4ReportsDailyMetric::query()->count())->toBe(1)
        ->and(GA4ReportsPageMetric::query()->count())->toBe(1)
        ->and(GA4ReportsSyncRun::query()->where('status', 'succeeded')->count())->toBe(2)
        ->and(GA4ReportsDailyMetric::query()->first()?->screen_page_views)->toBe(30)
        ->and(GA4ReportsPageMetric::query()->first()?->page_path)->toBe('/about');
});

it('persists GA4 metrics through natural keys', function (): void {
    $firstDailyMetric = PersistGA4ReportsDailyMetricAction::run(new GA4ReportsDailyMetricData(
        propertyId: '123456789',
        metricDate: CarbonImmutable::parse('2026-05-04'),
        totalUsers: 10,
        sessions: 20,
        screenPageViews: 30,
        engagedSessions: 15,
        engagementRate: 0.75,
        averageSessionDuration: 42.5,
        eventCount: 80,
        conversions: 4,
    ));
    $updatedDailyMetric = PersistGA4ReportsDailyMetricAction::run(new GA4ReportsDailyMetricData(
        propertyId: '123456789',
        metricDate: CarbonImmutable::parse('2026-05-04'),
        totalUsers: 11,
        sessions: 21,
        screenPageViews: 31,
        engagedSessions: 16,
        engagementRate: 0.8,
        averageSessionDuration: 43.5,
        eventCount: 81,
        conversions: 5,
    ));

    $firstPageMetric = PersistGA4ReportsPageMetricAction::run(new GA4ReportsPageMetricData(
        propertyId: '123456789',
        metricDate: CarbonImmutable::parse('2026-05-04'),
        pagePath: '/about',
        pageTitle: 'About',
        totalUsers: 8,
        sessions: 12,
        screenPageViews: 22,
        eventCount: 40,
        conversions: 2,
    ));
    $updatedPageMetric = PersistGA4ReportsPageMetricAction::run(new GA4ReportsPageMetricData(
        propertyId: '123456789',
        metricDate: CarbonImmutable::parse('2026-05-04'),
        pagePath: '/about',
        pageTitle: 'About Us',
        totalUsers: 9,
        sessions: 13,
        screenPageViews: 23,
        eventCount: 41,
        conversions: 3,
    ));

    expect($updatedDailyMetric->getKey())->toBe($firstDailyMetric->getKey())
        ->and($updatedDailyMetric->screen_page_views)->toBe(31)
        ->and($updatedPageMetric->getKey())->toBe($firstPageMetric->getKey())
        ->and($updatedPageMetric->page_title)->toBe('About Us')
        ->and(GA4ReportsDailyMetric::query()->count())->toBe(1)
        ->and(GA4ReportsPageMetric::query()->count())->toBe(1);
});

it('skips GA4 sync when the property window is already locked', function (): void {
    Date::setTestNow(Date::create(2026, 5, 5, 12, 0, 0));
    configureGA4ReportsSettings();

    app()->instance(GA4ReportsDataClientInterface::class, new FakeGA4ReportsDataClient(configured: true));

    $lock = Cache::lock('capell-ga4-reports:sync:123456789:2026-05-03:2026-05-04', 3600);
    $lock->get();

    try {
        $result = SyncGA4ReportsMetricsAction::run();
    } finally {
        $lock->release();
        Date::setTestNow();
    }

    expect($result->synced)->toBeFalse()
        ->and($result->message)->toBe(__('capell-ga4-reports::sync.already_running'))
        ->and(GA4ReportsSyncRun::query()->count())->toBe(0);
});

it('builds overview trend and top page data from local tables only', function (): void {
    configureGA4ReportsSettings();

    GA4ReportsDailyMetric::query()->create([
        'property_id' => '123456789',
        'metric_date' => '2026-05-03',
        'total_users' => 5,
        'sessions' => 10,
        'screen_page_views' => 20,
        'engaged_sessions' => 6,
        'engagement_rate' => 0.6,
        'average_session_duration' => 20,
        'event_count' => 40,
        'conversions' => 1,
    ]);
    GA4ReportsDailyMetric::query()->create([
        'property_id' => '123456789',
        'metric_date' => '2026-05-04',
        'total_users' => 8,
        'sessions' => 20,
        'screen_page_views' => 35,
        'engaged_sessions' => 14,
        'engagement_rate' => 0.7,
        'average_session_duration' => 50,
        'event_count' => 60,
        'conversions' => 3,
    ]);
    GA4ReportsPageMetric::query()->create([
        'property_id' => '123456789',
        'metric_date' => '2026-05-04',
        'page_path' => '/about',
        'page_title' => 'About',
        'total_users' => 8,
        'sessions' => 20,
        'screen_page_views' => 35,
        'event_count' => 60,
        'conversions' => 3,
    ]);

    $window = new GA4ReportsWindowData(
        startsAt: CarbonImmutable::parse('2026-05-03'),
        endsAt: CarbonImmutable::parse('2026-05-04'),
        propertyId: '123456789',
    );

    $overview = BuildGA4ReportsOverviewAction::run($window);
    $trend = BuildGA4ReportsTrendAction::run($window);
    $topPages = BuildTopGA4ReportsPagesAction::run($window);

    expect($overview->screenPageViews)->toBe(55)
        ->and($overview->sessions)->toBe(30)
        ->and($overview->totalUsers)->toBe(13)
        ->and($overview->conversions)->toBe(4)
        ->and($overview->engagementRate)->toBe(0.6667)
        ->and($overview->averageSessionDuration)->toBe(40.0)
        ->and($trend)->toHaveCount(2)
        ->and($trend[0]->screenPageViews)->toBe(20)
        ->and($topPages)->toHaveCount(1)
        ->and($topPages[0]->pagePath)->toBe('/about')
        ->and($topPages[0]->screenPageViews)->toBe(35);
});

it('builds GA4 digest data and exports it as CSV', function (): void {
    configureGA4ReportsSettings();

    GA4ReportsDailyMetric::query()->create([
        'property_id' => '123456789',
        'metric_date' => '2026-05-03',
        'total_users' => 5,
        'sessions' => 10,
        'screen_page_views' => 20,
        'engaged_sessions' => 6,
        'engagement_rate' => 0.6,
        'average_session_duration' => 20,
        'event_count' => 40,
        'conversions' => 1,
    ]);
    GA4ReportsPageMetric::query()->create([
        'property_id' => '123456789',
        'metric_date' => '2026-05-03',
        'page_path' => '/pricing',
        'page_title' => 'Pricing',
        'total_users' => 5,
        'sessions' => 10,
        'screen_page_views' => 20,
        'event_count' => 40,
        'conversions' => 1,
    ]);

    $window = new GA4ReportsWindowData(
        startsAt: CarbonImmutable::parse('2026-05-03'),
        endsAt: CarbonImmutable::parse('2026-05-03'),
        propertyId: '123456789',
    );

    $digest = BuildGA4ReportsDigestAction::run($window, 5);
    $csvRows = ga4ReportsCsvRows(ExportGA4ReportsDigestCsvAction::run($window, 5));

    expect($digest?->overview->screenPageViews)->toBe(20)
        ->and($digest?->topPages)->toHaveCount(1)
        ->and($csvRows[0])->toBe(['section', 'label', 'value', 'sessions', 'total_users', 'conversions', 'extra'])
        ->and($csvRows)->toContain(['overview', 'screen_page_views', '20', '', '', '', ''])
        ->and($csvRows)->toContain(['top_page', '/pricing', '20', '10', '5', '1', 'Pricing']);
});

it('returns an empty sync result when GA4 is not configured', function (): void {
    app()->instance(GA4ReportsDataClientInterface::class, new FakeGA4ReportsDataClient(configured: false));

    $result = SyncGA4ReportsMetricsAction::run();

    expect($result->synced)->toBeFalse()
        ->and(GA4ReportsSyncRun::query()->count())->toBe(0);
});

it('records a failed sync run when GA4 fetches fail', function (): void {
    configureGA4ReportsSettings();

    app()->instance(GA4ReportsDataClientInterface::class, new FakeGA4ReportsDataClient(
        configured: true,
        shouldFail: true,
    ));

    $result = SyncGA4ReportsMetricsAction::run();
    $syncRun = GA4ReportsSyncRun::query()->first();

    expect($result->synced)->toBeFalse()
        ->and($syncRun)->toBeInstanceOf(GA4ReportsSyncRun::class)
        ->and($syncRun?->status)->toBe('failed')
        ->and($syncRun?->error_message)->toBe('GA4 client failed.')
        ->and($syncRun?->finished_at)->not->toBeNull();
});

it('redacts credential values from persisted GA4 sync failures', function (): void {
    configureGA4ReportsSettings();

    $credentialsPath = tempnam(sys_get_temp_dir(), 'ga4-reports-credentials-');
    expect($credentialsPath)->toBeString();

    file_put_contents($credentialsPath, json_encode([
        'client_email' => 'analytics-service@example.test',
        'private_key_id' => 'private-key-id-secret',
        'private_key' => 'fixture-private-key-secret',
        'client_id' => 'client-id-secret',
        'token_uri' => 'https://oauth2.example.test/token',
    ], JSON_THROW_ON_ERROR));

    /** @var GA4ReportsSettings $settings */
    $settings = app(GA4ReportsSettings::class);
    $settings->credentials_path = $credentialsPath;

    app()->instance(GA4ReportsDataClientInterface::class, new FakeGA4ReportsDataClient(
        configured: true,
        shouldFail: true,
        failureMessage: 'Authorization: Bearer access-token-secret private_key=private-key-secret path=' . $credentialsPath,
    ));

    try {
        $result = SyncGA4ReportsMetricsAction::run();
        $syncRun = GA4ReportsSyncRun::query()->first();
    } finally {
        unlink($credentialsPath);
    }

    expect($result->synced)->toBeFalse()
        ->and($syncRun?->error_message)->toContain('Authorization: Bearer [redacted]')
        ->and($syncRun?->error_message)->toContain('private_key=[redacted]')
        ->and($syncRun?->error_message)->not->toContain('access-token-secret')
        ->and($syncRun?->error_message)->not->toContain('private-key-secret')
        ->and($syncRun?->error_message)->not->toContain($credentialsPath);
});
