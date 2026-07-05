<?php

declare(strict_types=1);

use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\NightShift\Health\ThemeNightShiftHealthCheck;
use Capell\ThemeStudio\NightShift\NightShiftThemeServiceProvider;

it('defines the night-shift renderer contract', function (): void {
    $definition = NightShiftThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-night-shift')
        ->and($definition->key)->toBe(NightShiftThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Night Shift')
        ->and($definition->description)->toContain('Near-black surfaces')
        ->and($definition->tags)->toContain('Dark SaaS', 'Product System', 'Workflows', 'Automation', 'Security')
        ->and($definition->bestFit)->toContain('Product-led SaaS', 'Workflow platforms', 'Technical operations tools')
        ->and($definition->assets)->toBe(['css' => 'vendor/capell/themes/night-shift.css'])
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('night-shift')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeNightShiftHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('registers Night Shift as definition-only with no legacy renderer', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(NightShiftThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new NightShiftThemeServiceProvider(app());
    $provider->boot($registry);

    expect($registry->hasRenderer(NightShiftThemeServiceProvider::THEME_KEY))->toBeFalse();

    CapellCore::clearPackages();
});

it('registers Night Shift theme assets when the package is installed', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(NightShiftThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new NightShiftThemeServiceProvider($this->app);
    $provider->boot($registry);

    $packageImports = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindImport)
        ->where('packageName', NightShiftThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    $packageSources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource)
        ->where('packageName', NightShiftThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    expect($packageImports)->toContain('resources/css/theme-night-shift.css')
        ->and($packageSources)->toContain('resources/views/**/*.blade.php');

    CapellCore::clearPackages();
});
