<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\LiquidGlass\Health\ThemeLiquidGlassHealthCheck;
use Capell\ThemeStudio\LiquidGlass\LiquidGlassThemeServiceProvider;

beforeEach(function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(LiquidGlassThemeServiceProvider::$packageName);

    $registry = resolve(ThemeRegistry::class);
    $registry->reset();

    (new LiquidGlassThemeServiceProvider($this->app))->boot($registry);
});

afterEach(function (): void {
    resolve(ThemeRegistry::class)->reset();
    CapellCore::clearPackages();
});

it('runs real diagnostics returning doctor check results', function (): void {
    $results = ThemeLiquidGlassHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(3)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when the theme registration, views, and marketplace screenshots are present', function (): void {
    $results = ThemeLiquidGlassHealthCheck::runDiagnostics();

    expect(ThemeLiquidGlassHealthCheck::passed())->toBeTrue()
        ->and($results->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue();
});

it('fails the theme definition check when the theme is not registered', function (): void {
    resolve(ThemeRegistry::class)->reset();

    $check = new ThemeLiquidGlassHealthCheck;

    expect($check->isThemeStudioDefinitionRegistered())->toBeFalse()
        ->and($check->themeStudioDefinitionCheck()->passed)->toBeFalse()
        ->and(ThemeLiquidGlassHealthCheck::passed())->toBeFalse();
});

it('fails the required views check when a package view is missing', function (): void {
    $check = new ThemeLiquidGlassHealthCheck;

    expect($check->missingRequiredViewFiles(['resources/views/sections/missing.blade.php']))
        ->toBe(['resources/views/sections/missing.blade.php'])
        ->and($check->requiredViewsCheck(['resources/views/sections/missing.blade.php'])->passed)->toBeFalse();
});

it('fails the marketplace screenshots check when a referenced image is missing', function (): void {
    $check = new ThemeLiquidGlassHealthCheck;

    expect($check->missingMarketplaceScreenshotFiles(['docs/assets/marketplace/missing.svg']))
        ->toBe(['docs/assets/marketplace/missing.svg'])
        ->and($check->marketplaceScreenshotsCheck(['docs/assets/marketplace/missing.svg'])->passed)->toBeFalse();
});
