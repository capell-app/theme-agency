<?php

declare(strict_types=1);

use Capell\Core\Models\Site;
use Capell\SeoSuite\Actions\PersistSearchConsoleUrlMetricAction;
use Capell\SeoSuite\Actions\SyncSearchConsoleInsightsAction;
use Capell\SeoSuite\Contracts\SearchConsoleClientInterface;
use Capell\SeoSuite\Models\SearchConsoleQueryMetric;
use Capell\SeoSuite\Models\SearchConsoleUrlMetric;
use Capell\SeoSuite\Support\SearchConsole\NullSearchConsoleClient;

function syncSearchConsoleUrlMetric(?SearchConsoleUrlMetric $metric): SearchConsoleUrlMetric
{
    throw_unless($metric instanceof SearchConsoleUrlMetric, RuntimeException::class, 'Expected search console URL metric.');

    return $metric;
}

function syncSearchConsoleQueryMetric(?SearchConsoleQueryMetric $metric): SearchConsoleQueryMetric
{
    throw_unless($metric instanceof SearchConsoleQueryMetric, RuntimeException::class, 'Expected search console query metric.');

    return $metric;
}

it('returns unconfigured sync results without writing metrics', function (): void {
    $site = Site::factory()->create();

    app()->instance(SearchConsoleClientInterface::class, new NullSearchConsoleClient);

    $result = SyncSearchConsoleInsightsAction::run((int) $site->getKey());

    expect($result)->toBe([
        'synced' => 0,
        'query_synced' => 0,
        'configured' => false,
        'pages' => [],
    ])->and(SearchConsoleUrlMetric::query()->count())->toBe(0)
        ->and(SearchConsoleQueryMetric::query()->count())->toBe(0);
});

it('stores and queries declining search console url metrics', function (): void {
    $site = Site::factory()->create();

    PersistSearchConsoleUrlMetricAction::run(
        siteId: (int) $site->getKey(),
        url: 'https://example.com/a',
        windowStart: now()->subDays(28),
        windowEnd: now(),
        clicks: 10,
        impressions: 100,
        ctr: 0.10,
        averagePosition: 4.2,
        previousClicks: 30,
        previousImpressions: 180,
        previousCtr: 0.16,
        previousAveragePosition: 3.1,
    );

    $metric = SearchConsoleUrlMetric::query()
        ->decliningPages((int) $site->getKey(), 10)
        ->first();
    $metric = syncSearchConsoleUrlMetric($metric);

    expect($metric->url)->toBe('https://example.com/a')
        ->and($metric->click_delta)->toBe(-20);
});

it('only returns declining pages from the latest search console window', function (): void {
    $site = Site::factory()->create();
    $olderWindowStart = now()->subDays(60);
    $olderWindowEnd = now()->subDays(32);
    $latestWindowStart = now()->subDays(28);
    $latestWindowEnd = now();

    PersistSearchConsoleUrlMetricAction::run(
        siteId: (int) $site->getKey(),
        url: 'https://example.com/old-drop',
        windowStart: $olderWindowStart,
        windowEnd: $olderWindowEnd,
        clicks: 5,
        impressions: 50,
        ctr: 0.10,
        averagePosition: 5.2,
        previousClicks: 80,
        previousImpressions: 200,
        previousCtr: 0.40,
        previousAveragePosition: 2.1,
    );
    PersistSearchConsoleUrlMetricAction::run(
        siteId: (int) $site->getKey(),
        url: 'https://example.com/current-growth',
        windowStart: $latestWindowStart,
        windowEnd: $latestWindowEnd,
        clicks: 40,
        impressions: 120,
        ctr: 0.33,
        averagePosition: 3.2,
        previousClicks: 10,
        previousImpressions: 80,
        previousCtr: 0.12,
        previousAveragePosition: 4.6,
    );
    PersistSearchConsoleUrlMetricAction::run(
        siteId: (int) $site->getKey(),
        url: 'https://example.com/current-drop',
        windowStart: $latestWindowStart,
        windowEnd: $latestWindowEnd,
        clicks: 20,
        impressions: 100,
        ctr: 0.20,
        averagePosition: 4.2,
        previousClicks: 45,
        previousImpressions: 140,
        previousCtr: 0.32,
        previousAveragePosition: 3.1,
    );

    $urls = SearchConsoleUrlMetric::query()
        ->decliningPages((int) $site->getKey(), 10)
        ->pluck('url')
        ->all();

    expect($urls)->toBe(['https://example.com/current-drop']);
});

it('persists configured search console metric rows before returning declining pages', function (): void {
    $site = Site::factory()->create();
    $metricRows = [
        [
            'url' => 'https://example.com/a',
            'clicks' => 10,
            'impressions' => 100,
            'ctr' => 0.1,
            'average_position' => 4.2,
            'previous_clicks' => 30,
            'previous_impressions' => 180,
            'previous_ctr' => 0.16,
            'previous_average_position' => 3.1,
            'window_start' => now()->subDays(28)->toDateString(),
            'window_end' => now()->toDateString(),
        ],
    ];
    $queryMetricRows = [
        [
            'query' => 'Capell CMS',
            'url' => 'https://example.com/a',
            'clicks' => 2,
            'impressions' => 100,
            'ctr' => 0.02,
            'average_position' => 8.5,
            'previous_clicks' => 4,
            'previous_impressions' => 120,
            'previous_ctr' => 0.03,
            'previous_average_position' => 6.2,
            'window_start' => now()->subDays(28)->toDateString(),
            'window_end' => now()->toDateString(),
        ],
    ];

    app()->instance(SearchConsoleClientInterface::class, new readonly class($metricRows, $queryMetricRows) implements SearchConsoleClientInterface
    {
        /**
         * @param  array<int, array<string, mixed>>  $metricRows
         * @param  array<int, array<string, mixed>>  $queryMetricRows
         */
        public function __construct(private array $metricRows, private array $queryMetricRows) {}

        public function isConfigured(): bool
        {
            return true;
        }

        public function pageInsights(string $url): array
        {
            return [];
        }

        public function decliningPages(int $siteId, int $limit = 10): array
        {
            return [];
        }

        public function urlMetricRows(int $siteId, int $limit = 100): array
        {
            return array_slice($this->metricRows, 0, $limit);
        }

        public function queryMetricRows(int $siteId, int $limit = 100): array
        {
            return array_slice($this->queryMetricRows, 0, $limit);
        }
    });

    $result = SyncSearchConsoleInsightsAction::run((int) $site->getKey(), 10);
    $metric = syncSearchConsoleUrlMetric(SearchConsoleUrlMetric::query()->first());
    $queryMetric = syncSearchConsoleQueryMetric(SearchConsoleQueryMetric::query()->first());

    expect($metric->url)->toBe('https://example.com/a')
        ->and($metric->click_delta)->toBe(-20)
        ->and($queryMetric->query)->toBe('capell cms')
        ->and($queryMetric->click_delta)->toBe(-2)
        ->and($result)->toBe([
            'synced' => 1,
            'query_synced' => 1,
            'configured' => true,
            'pages' => [
                [
                    'url' => 'https://example.com/a',
                    'clicks' => 10,
                    'previous_clicks' => 30,
                    'click_delta' => -20,
                ],
            ],
        ]);
});

it('imports one hundred search console rows by default while keeping declining page results narrow', function (): void {
    $site = Site::factory()->create();
    $metricRows = [];
    $queryMetricRows = [];

    foreach (range(1, 12) as $index) {
        $metricRows[] = [
            'url' => 'https://example.com/page-' . $index,
            'clicks' => 10,
            'impressions' => 100,
            'ctr' => 0.1,
            'average_position' => 4.2,
            'previous_clicks' => 30,
            'previous_impressions' => 180,
            'previous_ctr' => 0.16,
            'previous_average_position' => 3.1,
            'window_start' => now()->subDays(28)->toDateString(),
            'window_end' => now()->toDateString(),
        ];
        $queryMetricRows[] = [
            'query' => 'Capell CMS ' . $index,
            'url' => 'https://example.com/page-' . $index,
            'clicks' => 2,
            'impressions' => 100,
            'ctr' => 0.02,
            'average_position' => 8.5,
            'previous_clicks' => 4,
            'previous_impressions' => 120,
            'previous_ctr' => 0.03,
            'previous_average_position' => 6.2,
            'window_start' => now()->subDays(28)->toDateString(),
            'window_end' => now()->toDateString(),
        ];
    }

    app()->instance(SearchConsoleClientInterface::class, new readonly class($metricRows, $queryMetricRows) implements SearchConsoleClientInterface
    {
        /**
         * @param  array<int, array<string, mixed>>  $metricRows
         * @param  array<int, array<string, mixed>>  $queryMetricRows
         */
        public function __construct(private array $metricRows, private array $queryMetricRows) {}

        public function isConfigured(): bool
        {
            return true;
        }

        public function pageInsights(string $url): array
        {
            return [];
        }

        public function decliningPages(int $siteId, int $limit = 10): array
        {
            return [];
        }

        public function urlMetricRows(int $siteId, int $limit = 100): array
        {
            return array_slice($this->metricRows, 0, $limit);
        }

        public function queryMetricRows(int $siteId, int $limit = 100): array
        {
            return array_slice($this->queryMetricRows, 0, $limit);
        }
    });

    $result = SyncSearchConsoleInsightsAction::run((int) $site->getKey());

    expect($result['synced'])->toBe(12)
        ->and($result['query_synced'])->toBe(12)
        ->and($result['pages'])->toHaveCount(10);
});
