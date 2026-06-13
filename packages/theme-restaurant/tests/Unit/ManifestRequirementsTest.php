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

    expect($manifest['themeKey'])->toBe('restaurant')
        ->and(data_get($manifest, 'product.group'))->toBe('Capell Themes')
        ->and(data_get($manifest, 'product.tier'))->toBe('premium')
        ->and(data_get($manifest, 'commercial.proposedLicense'))->toBe('paid')
        ->and($overview)->toContain('Product group:' . PHP_EOL . '**Capell Themes**')
        ->and($readme)->toContain('- Product group: `Capell Themes`')
        ->and($manifest['extends'])->toBe('default')
        ->and(data_get($manifest, 'dependencies.requires'))->toContain('capell-app/frontend')
        ->and(RestaurantThemeServiceProvider::definition()->extends)->toBe('default')
        ->and($overview)->toContain('runtime inheritance uses `extends: default`')
        ->and($overview)->toContain('requires `capell-app/frontend` for the built-in default fallback')
        ->and($readme)->toContain('- Manifest extends: `default`')
        ->and($readme)->toContain('- Runtime extends: `default`')
        ->and($manifest['database']['migrations'])->toBeFalse()
        ->and($manifest['providers']['runtime'])->toContain(RestaurantThemeServiceProvider::class)
        ->and($manifest['commands']['demo'])->toBe('capell:theme-restaurant-demo')
        ->and($manifest['marketplace']['summary'])->toBe('A premium hospitality theme for menu-led restaurants, bars, private dining venues, and event-led dining businesses.')
        ->and($manifest['marketplace']['description'])->toBe($manifest['description'])
        ->and($composer['description'])->toBe($manifest['marketplace']['summary']);
});

it('declares only restaurant marketplace screenshots that exist in the package', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');
    $screenshots = data_get($manifest, 'marketplace.screenshots', []);

    throw_unless(is_array($screenshots), RuntimeException::class, 'Theme Restaurant screenshots must be an array.');

    expect($screenshots)->toHaveCount(5);

    foreach ($screenshots as $screenshot) {
        throw_if(! is_array($screenshot) || ! is_string($screenshot['path'] ?? null), RuntimeException::class, 'Theme Restaurant screenshot path must be a string.');

        expect(File::exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
    }
});
