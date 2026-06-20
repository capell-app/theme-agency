<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('keeps every prompt-built theme marketplace-ready', function (string $themeKey, string $themeName, array $premiumSurfaces): void {
    $packageName = 'theme-' . $themeKey;
    $packagePath = dirname(__DIR__, 3) . '/' . $packageName;
    $manifest = capell_json_file_array($packagePath . '/capell.json');
    $screenshots = capell_json_file_array($packagePath . '/docs/screenshots.json');
    $marketplaceScreenshotsValue = data_get($manifest, 'marketplace.screenshots', []);
    $screenshotEntriesValue = data_get($screenshots, 'entries', []);
    $marketplaceScreenshots = is_array($marketplaceScreenshotsValue) ? array_values($marketplaceScreenshotsValue) : [];
    $screenshotEntries = is_array($screenshotEntriesValue) ? array_values($screenshotEntriesValue) : [];
    $expectedSurfaces = [
        'homepage',
        'directory',
        'detail',
        'contact',
        'empty',
        'not-found',
        'cta',
        ...$premiumSurfaces,
    ];

    expect($manifest['kind'] ?? null)->toBe('theme')
        ->and($manifest['themeKey'] ?? null)->toBe($themeKey)
        ->and(data_get($manifest, 'product.tier'))->toBe('premium')
        ->and(data_get($manifest, 'commercial.proposedLicense'))->toBe('paid')
        ->and(data_get($manifest, 'commands.demo'))->toBe('capell:' . $packageName . '-demo')
        ->and(data_get($manifest, 'contributes'))->not->toBeEmpty()
        ->and($screenshotEntries)->toHaveCount(10)
        ->and($marketplaceScreenshots)->toHaveCount(11);

    $entrySurfaces = array_map(
        static function (mixed $entry) use ($themeKey): string {
            $entryId = data_get($entry, 'id');

            return str_replace($themeKey . '-', '', is_string($entryId) ? $entryId : '');
        },
        $screenshotEntries,
    );

    expect($entrySurfaces)->toEqual($expectedSurfaces)
        ->and(array_filter(
            $screenshotEntries,
            static fn (mixed $entry): bool => data_get($entry, 'required', false) === true,
        ))->toHaveCount(10)
        ->and(data_get($marketplaceScreenshots, '0.path'))->toBe('docs/assets/marketplace/extension-card.jpg');

    foreach ($marketplaceScreenshots as $marketplaceScreenshot) {
        $altText = data_get($marketplaceScreenshot, 'alt');
        $captionText = data_get($marketplaceScreenshot, 'caption');
        $screenshotRelativePath = data_get($marketplaceScreenshot, 'path');

        expect($altText)->toBeString()
            ->and(strlen(trim(is_string($altText) ? $altText : '')))->toBeGreaterThanOrEqual(12)
            ->and($captionText)->toBeString()
            ->and(strlen(trim(is_string($captionText) ? $captionText : '')))->toBeGreaterThanOrEqual(12)
            ->and(File::exists($packagePath . '/' . (is_string($screenshotRelativePath) ? $screenshotRelativePath : '')))->toBeTrue();
    }

    foreach ($screenshotEntries as $screenshotEntry) {
        $screenshotRelativePath = data_get($screenshotEntry, 'screenshotPath');
        $screenshotPath = dirname(__DIR__, 4) . '/' . (is_string($screenshotRelativePath) ? $screenshotRelativePath : '');

        expect(data_get($screenshotEntry, 'targetType'))->toBe('frontend-url')
            ->and(data_get($screenshotEntry, 'surface'))->toBe('frontend')
            ->and(data_get($screenshotEntry, 'target'))->toBeString()
            ->and(data_get($screenshotEntry, 'target'))->not->toBe('/')
            ->and(File::exists($screenshotPath))->toBeTrue()
            ->and(File::size($screenshotPath))->toBeGreaterThan(1024);
    }

    expect(File::get($packagePath . '/README.md'))->toContain('Marketplace assets')
        ->and(File::get($packagePath . '/docs/overview.md'))->toContain('Marketplace assets')
        ->and($entrySurfaces)->toContain($premiumSurfaces[0]);
})->with([
    ['ai-lab', 'AI Lab', ['model-suite', 'research-library', 'playground-preview']],
    ['api-platform', 'API Platform', ['quickstart', 'api-reference', 'status']],
    ['ai-agent', 'AI Agent', ['use-cases', 'integrations', 'roi']],
    ['aeo-analytics', 'AEO Analytics', ['dashboard', 'reports', 'integrations']],
    ['fintech-trust', 'Fintech Trust', ['verification-flow', 'security', 'compliance']],
    ['crypto-defi', 'Crypto DeFi', ['markets', 'how-it-works', 'audit']],
    ['quant-trading', 'Quant Trading', ['performance', 'strategies', 'risk']],
    ['devtool-oss', 'Devtool OSS', ['install', 'community', 'cloud']],
    ['robotics-hardware', 'Robotics Hardware', ['product', 'specs', 'preorder']],
    ['manufacturing', 'Manufacturing', ['capabilities', 'case-studies', 'rfq']],
    ['packaging-supplier', 'Packaging Supplier', ['products', 'sustainability', 'samples']],
    ['conference-event', 'Conference Event', ['agenda', 'tickets', 'speakers']],
    ['podcast-show', 'Podcast Show', ['episodes', 'guests', 'sponsors']],
    ['newsroom-magazine', 'Newsroom Magazine', ['front-page', 'contributors', 'newsletter']],
    ['design-studio', 'Design Studio', ['projects', 'studio', 'case-study']],
    ['product-studio', 'Product Studio', ['case-studies', 'engagements', 'stack']],
    ['personal-dev', 'Personal Dev', ['writing', 'projects', 'now']],
    ['creator-newsletter', 'Creator Newsletter', ['archive', 'author', 'subscribe']],
    ['law-firm', 'Law Firm', ['practice-areas', 'attorneys', 'consultation']],
    ['financial-advisory', 'Financial Advisory', ['services', 'advisors', 'calculators']],
    ['construction-trades', 'Construction Trades', ['services', 'projects', 'quote']],
    ['fitness-wellness', 'Fitness Wellness', ['classes', 'coaches', 'membership']],
    ['beauty-spa', 'Beauty & Spa', ['treatments', 'packages', 'booking']],
    ['travel-tourism', 'Travel Tourism', ['destinations', 'itineraries', 'enquiry']],
    ['automotive-dealer', 'Automotive Dealer', ['inventory', 'vehicle-detail', 'finance']],
    ['property-developer', 'Property Developer', ['developments', 'floorplans', 'location']],
    ['recruitment-jobs', 'Recruitment Jobs', ['jobs', 'employers', 'candidate-advice']],
    ['editorial-serif', 'Editorial Serif', ['essays', 'archive', 'about']],
]);
