<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Corporate\CorporateThemeServiceProvider;
use Capell\ThemeStudio\Corporate\Health\ThemeCorporateHealthCheck;

beforeEach(function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CorporateThemeServiceProvider::$packageName);

    $registry = resolve(ThemeRegistry::class);
    $registry->reset();

    (new CorporateThemeServiceProvider($this->app))->boot($registry);
});

afterEach(function (): void {
    resolve(ThemeRegistry::class)->reset();
    CapellCore::clearPackages();
});

it('runs real diagnostics returning doctor check results', function (): void {
    $results = ThemeCorporateHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(3)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when the theme registration, views, and marketplace screenshots are present', function (): void {
    $results = ThemeCorporateHealthCheck::runDiagnostics();

    expect(ThemeCorporateHealthCheck::passed())->toBeTrue()
        ->and($results->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue();
});

it('fails the theme definition check when the theme is not registered', function (): void {
    resolve(ThemeRegistry::class)->reset();

    $check = new ThemeCorporateHealthCheck;

    expect($check->isThemeStudioDefinitionRegistered())->toBeFalse()
        ->and($check->themeStudioDefinitionCheck()->passed)->toBeFalse()
        ->and(ThemeCorporateHealthCheck::passed())->toBeFalse();
});

it('fails the required views check when a package view is missing', function (): void {
    $check = new ThemeCorporateHealthCheck;

    expect($check->missingRequiredViewFiles(['resources/views/sections/missing.blade.php']))
        ->toBe(['resources/views/sections/missing.blade.php'])
        ->and($check->requiredViewsCheck(['resources/views/sections/missing.blade.php'])->passed)->toBeFalse();
});

it('fails the marketplace screenshots check when a referenced image is missing', function (): void {
    $check = new ThemeCorporateHealthCheck;

    expect($check->missingMarketplaceScreenshotFiles(['docs/screenshots/missing.png']))
        ->toBe(['docs/screenshots/missing.png'])
        ->and($check->marketplaceScreenshotsCheck(['docs/screenshots/missing.png'])->passed)->toBeFalse();
});
