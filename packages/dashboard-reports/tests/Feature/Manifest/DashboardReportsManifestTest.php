<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\RegistersExtensionWidget;
use Capell\DashboardReports\Filament\Settings\Contributors\DashboardReportsDashboardSettingsContributor;
use Capell\DashboardReports\Filament\Widgets\ContentHealthWidget;
use Capell\DashboardReports\Filament\Widgets\PublishingTrendChartWidget;
use Capell\DashboardReports\Manifest\DashboardReportsDashboardWidgetsContribution;
use Capell\DashboardReports\Tests\DashboardReportsTestCase;
use Illuminate\Support\Facades\File;

uses(DashboardReportsTestCase::class);

it('declares committed marketplace gallery assets for every required screenshot capture target', function (): void {
    $packagePath = dirname(__DIR__, 3);
    $manifest = capell_json_file_array($packagePath . '/capell.json');
    $screenshotContract = capell_json_file_array($packagePath . '/docs/screenshots.json');

    $marketplace = $manifest['marketplace'] ?? [];
    $marketplaceScreenshots = $marketplace['screenshots'] ?? [];
    $contractEntries = $screenshotContract['entries'] ?? [];

    throw_unless(is_array($marketplace), RuntimeException::class, 'Dashboard Reports marketplace metadata must be an array.');
    throw_unless(is_array($marketplaceScreenshots), RuntimeException::class, 'Dashboard Reports marketplace screenshots must be an array.');
    throw_unless(is_array($contractEntries), RuntimeException::class, 'Dashboard Reports screenshot contract entries must be an array.');

    $marketplaceScreenshotPaths = [];

    foreach ($marketplaceScreenshots as $marketplaceScreenshot) {
        throw_unless(is_array($marketplaceScreenshot), RuntimeException::class, 'Dashboard Reports marketplace screenshot entries must be arrays.');

        $path = $marketplaceScreenshot['path'] ?? null;
        $alt = $marketplaceScreenshot['alt'] ?? null;
        $caption = $marketplaceScreenshot['caption'] ?? null;

        throw_unless(is_string($path), RuntimeException::class, 'Dashboard Reports marketplace screenshot paths must be strings.');
        throw_unless(is_string($alt), RuntimeException::class, 'Dashboard Reports marketplace screenshot alt text must be strings.');
        throw_unless(is_string($caption), RuntimeException::class, 'Dashboard Reports marketplace screenshot captions must be strings.');

        $marketplaceScreenshotPaths[] = $path;

        expect($path)->toMatch('/^docs\\/(assets\\/marketplace|screenshots)\\//')
            ->and(File::exists($packagePath . '/' . $path))->toBeTrue()
            ->and(strlen(trim($alt)))->toBeGreaterThanOrEqual(12)
            ->and(strlen(trim($caption)))->toBeGreaterThanOrEqual(12);
    }

    $requiredMarketplaceAssetPaths = [];

    foreach ($contractEntries as $contractEntry) {
        if (! is_array($contractEntry)) {
            continue;
        }

        if (($contractEntry['required'] ?? false) !== true) {
            continue;
        }

        $screenshotPath = $contractEntry['screenshotPath'] ?? null;

        throw_unless(is_string($screenshotPath), RuntimeException::class, 'Required Dashboard Reports screenshot contract entries must have screenshot paths.');

        $requiredMarketplaceAssetPaths[] = str_replace('packages/dashboard-reports/', '', $screenshotPath);
    }

    expect($marketplaceScreenshotPaths)
        ->toContain('docs/assets/marketplace/extension-card.jpg')
        ->toContain(...$requiredMarketplaceAssetPaths);
});

it('keeps marketplace and package descriptions focused on shipped dashboard report behavior', function (): void {
    $packagePath = dirname(__DIR__, 3);
    $manifest = capell_json_file_array($packagePath . '/capell.json');
    $composer = capell_json_file_array($packagePath . '/composer.json');
    $packageTranslations = require $packagePath . '/resources/lang/en/package.php';
    $docsIndex = File::get($packagePath . '/docs/README.md');

    $expectedSummary = "At-a-glance content-health and publishing-activity widgets for the Capell admin dashboard \u{2014} spot scheduled, expired, stale, and URL-less pages without opening a single resource.";
    $expectedPackageDescription = 'Content-health and publishing-activity widgets for Capell admin dashboards.';
    $expectedDocsDescription = 'Dashboard Reports provides content-health and publishing-activity widgets for Capell admin dashboards.';

    expect(data_get($manifest, 'marketplace.summary'))->toBe($expectedSummary)
        ->and($composer['description'] ?? null)->toBe($expectedSummary)
        ->and($packageTranslations['description'] ?? null)->toBe($expectedPackageDescription)
        ->and($docsIndex)->toContain($expectedDocsDescription)
        ->and(data_get($manifest, 'description'))->toContain('Content Health widget flags scheduled, expired, stale, and URL-less pages')
        ->and(data_get($manifest, 'description'))->toContain('Publishing Trend chart tracks published-vs-scheduled activity')
        ->and(data_get($manifest, 'description'))->not->toContain('generic CMS reporting widgets')
        ->and($composer['description'] ?? null)->not->toContain('generic CMS reporting widgets')
        ->and($packageTranslations['description'] ?? null)->not->toContain('Generic CMS reporting widgets')
        ->and($docsIndex)->not->toContain('generic CMS reporting widgets');
});

it('declares the dashboard report export command', function (): void {
    $packagePath = dirname(__DIR__, 3);
    $manifest = capell_json_file_array($packagePath . '/capell.json');

    expect(data_get($manifest, 'commands.export'))->toBe('capell:dashboard-reports:export');
});

it('declares the dashboard report widget registry capability', function (): void {
    $packagePath = dirname(__DIR__, 3);
    $manifest = capell_json_file_array($packagePath . '/capell.json');

    expect(data_get($manifest, 'capabilities'))->toContain('dashboard-report-widget-registry');
});

it('declares dashboard widget contribution metadata', function (): void {
    $packagePath = dirname(__DIR__, 3);
    $manifest = capell_json_file_array($packagePath . '/capell.json');
    $contributes = data_get($manifest, 'contributes', []);

    throw_unless(is_array($contributes), RuntimeException::class, 'Dashboard Reports contributions must be an array.');

    $contributions = collect($contributes);
    $dashboardWidgets = $contributions->firstWhere('class', DashboardReportsDashboardWidgetsContribution::class);

    throw_unless(is_array($dashboardWidgets), RuntimeException::class, 'Dashboard Reports widget contribution must be an array.');

    expect($dashboardWidgets)->toBeArray()
        ->and($dashboardWidgets['type'])->toBe('dashboard-widget')
        ->and($dashboardWidgets['dashboard'])->toBe('main')
        ->and($dashboardWidgets['widgetClasses'])->toBe([
            PublishingTrendChartWidget::class,
            ContentHealthWidget::class,
        ])
        ->and($dashboardWidgets['settingsContributor'])->toBe(DashboardReportsDashboardSettingsContributor::class)
        ->and(data_get($manifest, 'contributionTraceability.deferredContributions'))->toBe([])
        ->and(class_implements(DashboardReportsDashboardWidgetsContribution::class))->toContain(RegistersExtensionWidget::class);
});
