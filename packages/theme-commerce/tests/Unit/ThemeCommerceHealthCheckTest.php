<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Commerce\CommerceThemeServiceProvider;
use Capell\ThemeStudio\Commerce\Health\ThemeCommerceHealthCheck;

it('passes commerce diagnostics when the theme is installed and booted', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(CommerceThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $this->app->instance(ThemeRegistry::class, $registry);

    (new CommerceThemeServiceProvider($this->app))->boot($registry);

    $diagnostics = ThemeCommerceHealthCheck::runDiagnostics();

    expect($diagnostics)->toHaveCount(3)
        ->and($diagnostics->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue()
        ->and($diagnostics->pluck('label')->all())->toBe([
            'Theme Commerce Studio definition',
            'Theme Commerce render views',
            'Theme Commerce vendor assets',
        ])
        ->and($diagnostics->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue()
        ->and(ThemeCommerceHealthCheck::passed())->toBeTrue();
});

it('fails commerce diagnostics when the theme definition is not registered', function (): void {
    CapellCore::clearPackages();
    $this->app->instance(ThemeRegistry::class, new ThemeRegistry);

    $definitionCheck = ThemeCommerceHealthCheck::runDiagnostics()
        ->first(static fn (DoctorCheckResultData $result): bool => $result->label === 'Theme Commerce Studio definition');

    throw_unless($definitionCheck instanceof DoctorCheckResultData, RuntimeException::class, 'Commerce theme definition diagnostic was not returned.');

    expect($definitionCheck->passed)->toBeFalse()
        ->and(ThemeCommerceHealthCheck::passed())->toBeFalse();
});
