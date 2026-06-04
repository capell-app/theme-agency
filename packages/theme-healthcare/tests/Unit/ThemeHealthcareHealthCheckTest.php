<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Healthcare\Health\ThemeHealthcareHealthCheck;
use Capell\ThemeStudio\Healthcare\HealthcareThemeServiceProvider;

it('passes healthcare diagnostics when the theme is installed and booted', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(HealthcareThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $this->app->instance(ThemeRegistry::class, $registry);

    (new HealthcareThemeServiceProvider($this->app))->boot($registry);

    $diagnostics = ThemeHealthcareHealthCheck::runDiagnostics();

    expect($diagnostics)->toHaveCount(3)
        ->and($diagnostics->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue()
        ->and($diagnostics->pluck('label')->all())->toBe([
            'Theme Healthcare Studio definition',
            'Theme Healthcare render views',
            'Theme Healthcare vendor assets',
        ])
        ->and($diagnostics->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue()
        ->and(ThemeHealthcareHealthCheck::passed())->toBeTrue();
});

it('fails healthcare diagnostics when the theme definition is not registered', function (): void {
    CapellCore::clearPackages();
    $this->app->instance(ThemeRegistry::class, new ThemeRegistry);

    $definitionCheck = ThemeHealthcareHealthCheck::runDiagnostics()
        ->first(static fn (DoctorCheckResultData $result): bool => $result->label === 'Theme Healthcare Studio definition');

    throw_unless($definitionCheck instanceof DoctorCheckResultData, RuntimeException::class, 'Healthcare theme definition diagnostic was not returned.');

    expect($definitionCheck->passed)->toBeFalse()
        ->and(ThemeHealthcareHealthCheck::passed())->toBeFalse();
});
