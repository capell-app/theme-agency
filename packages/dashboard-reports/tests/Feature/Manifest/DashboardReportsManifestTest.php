<?php

declare(strict_types=1);

use Capell\DashboardReports\Tests\DashboardReportsTestCase;
use Illuminate\Support\Facades\File;

uses(DashboardReportsTestCase::class);

it('declares committed marketplace gallery assets for every required screenshot capture target', function (): void {
    $packagePath = dirname(__DIR__, 3);
    $manifest = json_decode(File::get($packagePath . '/capell.json'), true, flags: JSON_THROW_ON_ERROR);
    $screenshotContract = json_decode(File::get($packagePath . '/docs/screenshots.json'), true, flags: JSON_THROW_ON_ERROR);

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

        expect($path)->toStartWith('docs/assets/marketplace/')
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

        $id = $contractEntry['id'] ?? null;

        throw_unless(is_string($id), RuntimeException::class, 'Required Dashboard Reports screenshot contract entries must have string ids.');

        $requiredMarketplaceAssetPaths[] = 'docs/assets/marketplace/' . $id . '.svg';
    }

    expect($marketplaceScreenshotPaths)
        ->toContain('docs/assets/marketplace/extension-card.jpg')
        ->toContain(...$requiredMarketplaceAssetPaths);
});

it('keeps marketplace and package descriptions focused on shipped dashboard report behavior', function (): void {
    $packagePath = dirname(__DIR__, 3);
    $manifest = json_decode(File::get($packagePath . '/capell.json'), true, flags: JSON_THROW_ON_ERROR);
    $composer = json_decode(File::get($packagePath . '/composer.json'), true, flags: JSON_THROW_ON_ERROR);
    $packageTranslations = require $packagePath . '/resources/lang/en/package.php';
    $docsIndex = File::get($packagePath . '/docs/README.md');

    $expectedSummary = "At-a-glance content-health and publishing-activity widgets for the Capell admin dashboard \u{2014} spot scheduled, expired, stale, and URL-less pages without opening a single resource.";
    $expectedPackageDescription = 'Content-health and publishing-activity widgets for Capell admin dashboards.';
    $expectedDocsDescription = 'Dashboard Reports provides content-health and publishing-activity widgets for Capell admin dashboards.';

    expect($manifest['marketplace']['summary'] ?? null)->toBe($expectedSummary)
        ->and($composer['description'] ?? null)->toBe($expectedSummary)
        ->and($packageTranslations['description'] ?? null)->toBe($expectedPackageDescription)
        ->and($docsIndex)->toContain($expectedDocsDescription)
        ->and($manifest['description'] ?? null)->toContain('Content Health widget flags scheduled, expired, stale, and URL-less pages')
        ->and($manifest['description'] ?? null)->toContain('Publishing Trend chart tracks published-vs-scheduled activity')
        ->and($manifest['description'] ?? null)->not->toContain('generic CMS reporting widgets')
        ->and($composer['description'] ?? null)->not->toContain('generic CMS reporting widgets')
        ->and($packageTranslations['description'] ?? null)->not->toContain('Generic CMS reporting widgets')
        ->and($docsIndex)->not->toContain('generic CMS reporting widgets');
});
