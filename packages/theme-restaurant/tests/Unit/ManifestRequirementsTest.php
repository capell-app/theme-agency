<?php

declare(strict_types=1);

use Capell\ThemeStudio\Restaurant\RestaurantThemeServiceProvider;
use Illuminate\Support\Facades\File;

it('declares the required first-party restaurant theme manifest boundaries', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');
    $composer = capell_json_file_array(__DIR__ . '/../../composer.json');

    $overviewContents = file_get_contents(__DIR__ . '/../../docs/overview.md');
    $overview = $overviewContents === false ? '' : $overviewContents;
    $readmeContents = file_get_contents(__DIR__ . '/../../README.md');
    $readme = $readmeContents === false ? '' : $readmeContents;

    expect(data_get($manifest, 'themeKey'))->toBe('restaurant')
        ->and(data_get($manifest, 'product.group'))->toBe('Capell Themes')
        ->and(data_get($manifest, 'product.tier'))->toBe('premium')
        ->and(data_get($manifest, 'commercial.proposedLicense'))->toBe('paid')
        ->and($overview)->toContain('Product group:' . PHP_EOL . '**Capell Themes**')
        ->and($readme)->toContain('- Product group: `Capell Themes`')
        ->and(data_get($manifest, 'extends'))->toBe('default')
        ->and(data_get($manifest, 'dependencies.requires'))->toContain('capell-app/foundation-theme')
        ->and(data_get($manifest, 'dependencies.requires'))->toContain('capell-app/frontend')
        ->and(data_get($composer, 'require'))->toHaveKey('capell-app/foundation-theme')
        ->and(RestaurantThemeServiceProvider::definition()->extends)->toBe('default')
        ->and($overview)->toContain('runtime inheritance uses `extends: default`')
        ->and($overview)->toContain('requires `capell-app/foundation-theme`')
        ->and($overview)->toContain('capell-app/frontend')
        ->and($readme)->toContain('- Manifest extends: `default`')
        ->and($readme)->toContain('- Runtime extends: `default`')
        ->and(data_get($manifest, 'database.migrations'))->toBeFalse()
        ->and(data_get($manifest, 'providers.runtime'))->toContain(RestaurantThemeServiceProvider::class)
        ->and(data_get($manifest, 'commands.demo'))->toBe('capell:theme-restaurant-demo')
        ->and(data_get($manifest, 'performance.cacheSafety.cacheable'))->toBeTrue()
        ->and(data_get($manifest, 'performance.cacheSafety.variesBy'))->toBe(['site', 'locale'])
        ->and(data_get($manifest, 'performance.cacheSafety.invalidationSources'))->toHaveCount(4)
        ->and(data_get($manifest, 'performance.cacheSafety.invalidationSources.0.model'))->toBe('Capell\\Core\\Models\\Page')
        ->and($overview)->toContain('Theme Restaurant output is cacheable for public HTML')
        ->and($readme)->toContain('Restaurant sections do not query Bookings, Form Builder, Events, Blog, or SEO Suite directly')
        ->and(data_get($manifest, 'marketplace.summary'))->toBe('A premium hospitality theme for menu-led restaurants, bars, private dining venues, and event-led dining businesses.')
        ->and(data_get($manifest, 'marketplace.description'))->toBe(data_get($manifest, 'description'))
        ->and(data_get($composer, 'description'))->toBe(data_get($manifest, 'marketplace.summary'));
});

it('declares only restaurant marketplace screenshots that exist in the package', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');
    $screenshots = data_get($manifest, 'marketplace.screenshots', []);

    throw_unless(is_array($screenshots), RuntimeException::class, 'Theme Restaurant screenshots must be an array.');

    expect($screenshots)->toHaveCount(6);

    foreach ($screenshots as $screenshot) {
        throw_if(! is_array($screenshot) || ! is_string($screenshot['path'] ?? null), RuntimeException::class, 'Theme Restaurant screenshot path must be a string.');

        expect(File::exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
    }

    expect(collect($screenshots)->pluck('path')->all())->toBe([
        'docs/assets/marketplace/extension-card.svg',
        'docs/screenshots/restaurant-homepage-layout.png',
        'docs/screenshots/restaurant-menu-layout.png',
        'docs/screenshots/restaurant-reservation-layout.png',
        'docs/screenshots/restaurant-events-layout.png',
        'docs/screenshots/restaurant-private-dining-layout.png',
    ]);
});

it('requires committed restaurant screenshot outputs', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../docs/screenshots.json');
    $entries = data_get($manifest, 'entries', []);

    throw_unless(is_array($entries), RuntimeException::class, 'Theme Restaurant screenshot entries must be an array.');

    foreach ($entries as $entry) {
        throw_if(! is_array($entry) || ! is_string($entry['screenshotPath'] ?? null), RuntimeException::class, 'Theme Restaurant screenshot path must be a string.');

        expect($entry['required'] ?? null)->toBeTrue()
            ->and(str_starts_with($entry['screenshotPath'], 'packages/theme-restaurant/docs/screenshots/'))->toBeTrue()
            ->and(File::exists(dirname(__DIR__, 4) . '/' . $entry['screenshotPath']))->toBeTrue();
    }
});
