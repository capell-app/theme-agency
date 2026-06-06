<?php

declare(strict_types=1);

use Capell\Admin\Contracts\Extenders\PageTableExtender;
use Capell\Core\Models\Page;
use Capell\DashboardReports\Filament\Extenders\DashboardReportsPageTableExtender;
use Capell\DashboardReports\Filament\Widgets\PublishingTrendChartWidget;
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

    $widget = new PublishingTrendChartWidget;
    $data = (new ReflectionMethod($widget, 'getData'))->invoke($widget);

    throw_unless(is_array($data), RuntimeException::class, 'Publishing trend chart data must be an array.');

    expect($data)->toHaveKeys(['datasets', 'labels'])
        ->and($data['datasets'])->toHaveCount(2)
        ->and($data['datasets'][0]['label'])->toBe(__('capell-dashboard-reports::dashboard.chart_published_pages'))
        ->and($data['datasets'][1]['label'])->toBe(__('capell-dashboard-reports::dashboard.chart_scheduled_pages'))
        ->and($data['datasets'][0]['borderColor'])->toBe('rgb(var(--primary-600) / 1)')
        ->and($data['datasets'][0]['backgroundColor'])->toBe('rgb(var(--primary-500) / 0.12)')
        ->and($data['datasets'][1]['borderColor'])->toBe('rgb(var(--warning-600) / 1)')
        ->and($data['datasets'][1]['backgroundColor'])->toBe('rgb(var(--warning-500) / 0.12)')
        ->and($data['datasets'][0]['data'])->toHaveCount(7)
        ->and($data['datasets'][1]['data'])->toHaveCount(7)
        ->and($data['labels'])->toHaveCount(7);
});

it('registers the content health page-table filter extender', function (): void {
    $extenders = collect(app()->tagged(PageTableExtender::TAG))
        ->map(fn (PageTableExtender $extender): string => $extender::class);

    expect($extenders)->toContain(DashboardReportsPageTableExtender::class);
});
