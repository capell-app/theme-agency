<?php

declare(strict_types=1);

use Capell\Admin\Contracts\Extenders\PageTableExtender;
use Capell\Core\Models\Page;
use Capell\DashboardReports\Filament\Extenders\DashboardReportsPageTableExtender;
use Capell\DashboardReports\Filament\Widgets\PublishingTrendChartFilamentWidget;
use Capell\DashboardReports\Health\DashboardReportsHealthCheck;
use Capell\DashboardReports\Providers\DashboardReportsServiceProvider;
use Capell\DashboardReports\Tests\DashboardReportsTestCase;
use Carbon\CarbonImmutable;
use Spatie\LaravelPackageTools\Package;

uses(DashboardReportsTestCase::class);

it('declares package configuration and health compatibility', function (): void {
    $package = new Package;

    (new DashboardReportsServiceProvider(app()))->configurePackage($package);

    expect(DashboardReportsServiceProvider::$name)->toBe('capell-dashboard-reports')
        ->and(DashboardReportsServiceProvider::$packageName)->toBe('capell-app/dashboard-reports')
        ->and($package->name)->toBe('capell-dashboard-reports')
        ->and($package->viewNamespace)->toBe('capell-dashboard-reports')
        ->and(DashboardReportsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('builds publishing trend widget chart datasets from action data', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-03 12:00:00'));

    Page::factory()->published(CarbonImmutable::parse('2026-05-01 09:00:00'))->create();
    Page::factory()->pending()->create([
        'visible_from' => CarbonImmutable::parse('2026-05-04 09:00:00'),
    ]);

    $widget = new PublishingTrendChartFilamentWidget;
    $data = (new ReflectionMethod($widget, 'getData'))->invoke($widget);

    throw_unless(is_array($data), RuntimeException::class, 'Publishing trend chart data must be an array.');

    $chartData = dashboardReportsChartData($data);
    $datasets = dashboardReportsChartDatasets($chartData);
    $labels = is_array($chartData['labels'] ?? null) ? $chartData['labels'] : [];
    $publishedDataset = $datasets[0] ?? [];
    $scheduledDataset = $datasets[1] ?? [];

    expect($data)->toHaveKeys(['datasets', 'labels'])
        ->and($datasets)->toHaveCount(2)
        ->and($publishedDataset['label'] ?? null)->toBe(__('capell-dashboard-reports::dashboard.chart_published_pages'))
        ->and($scheduledDataset['label'] ?? null)->toBe(__('capell-dashboard-reports::dashboard.chart_scheduled_pages'))
        ->and($publishedDataset['borderColor'] ?? null)->toBe('rgb(var(--primary-600) / 1)')
        ->and($publishedDataset['backgroundColor'] ?? null)->toBe('rgb(var(--primary-500) / 0.12)')
        ->and($scheduledDataset['borderColor'] ?? null)->toBe('rgb(var(--warning-600) / 1)')
        ->and($scheduledDataset['backgroundColor'] ?? null)->toBe('rgb(var(--warning-500) / 0.12)')
        ->and($publishedDataset['data'] ?? [])->toHaveCount(7)
        ->and($scheduledDataset['data'] ?? [])->toHaveCount(7)
        ->and($labels)->toHaveCount(7);
});

/**
 * @param  array<mixed, mixed>  $data
 * @return array<string, mixed>
 */
function dashboardReportsChartData(array $data): array
{
    $chartData = [];

    foreach ($data as $key => $value) {
        if (is_string($key)) {
            $chartData[$key] = $value;
        }
    }

    return $chartData;
}

/**
 * @param  array<string, mixed>  $data
 * @return list<array<string, mixed>>
 */
function dashboardReportsChartDatasets(array $data): array
{
    $datasets = $data['datasets'] ?? [];

    if (! is_array($datasets)) {
        return [];
    }

    $normalizedDatasets = [];

    foreach ($datasets as $dataset) {
        if (! is_array($dataset)) {
            continue;
        }

        $normalizedDataset = [];

        foreach ($dataset as $key => $value) {
            if (is_string($key)) {
                $normalizedDataset[$key] = $value;
            }
        }

        $normalizedDatasets[] = $normalizedDataset;
    }

    return $normalizedDatasets;
}

it('registers the content health page-table filter extender', function (): void {
    $extenders = collect(app()->tagged(PageTableExtender::TAG))
        ->map(fn (PageTableExtender $extender): string => $extender::class);

    expect($extenders)->toContain(DashboardReportsPageTableExtender::class);
});
