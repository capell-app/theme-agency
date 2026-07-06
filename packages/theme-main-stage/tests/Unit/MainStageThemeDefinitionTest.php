<?php

declare(strict_types=1);

use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\MainStage\Health\ThemeMainStageHealthCheck;
use Capell\ThemeStudio\MainStage\MainStageThemeServiceProvider;

it('defines the main-stage renderer contract', function (): void {
    $definition = MainStageThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-main-stage')
        ->and($definition->key)->toBe(MainStageThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Main Stage')
        ->and($definition->description)->toContain('Poster-bold surfaces')
        ->and($definition->tags)->toContain('Events', 'Conference', 'Tickets', 'Agenda', 'Sponsors')
        ->and($definition->bestFit)->toContain('Conferences and summits', 'Multi-track events', 'Ticketed live events')
        ->and($definition->assets)->toBe(['css' => 'vendor/capell/themes/main-stage.css'])
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('main-stage')
        ->and($definition->presets[1]->key)->toBe('green-room')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeMainStageHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('registers Main Stage as definition-only with no legacy renderer', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(MainStageThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new MainStageThemeServiceProvider(app());
    $provider->boot($registry);

    expect($registry->hasRenderer(MainStageThemeServiceProvider::THEME_KEY))->toBeFalse();

    CapellCore::clearPackages();
});

it('registers Main Stage theme assets when the package is installed', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(MainStageThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new MainStageThemeServiceProvider($this->app);
    $provider->boot($registry);

    $packageImports = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindImport)
        ->where('packageName', MainStageThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    $packageSources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource)
        ->where('packageName', MainStageThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    expect($packageImports)->toContain('resources/css/theme-main-stage.css')
        ->and($packageSources)->toContain('resources/views/**/*.blade.php');

    CapellCore::clearPackages();
});
