<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\Education\EducationThemeServiceProvider;
use Capell\ThemeStudio\Education\Health\ThemeEducationHealthCheck;

uses(PackagesTestCase::class);

it('passes education diagnostics when the theme is installed and booted', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(EducationThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $this->app->instance(ThemeRegistry::class, $registry);

    (new EducationThemeServiceProvider($this->app))->boot($registry);

    $diagnostics = ThemeEducationHealthCheck::runDiagnostics();

    expect($diagnostics)->toHaveCount(3)
        ->and($diagnostics->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue()
        ->and($diagnostics->pluck('label')->all())->toBe([
            'Theme Education Studio definition',
            'Theme Education render views',
            'Theme Education vendor assets',
        ])
        ->and($diagnostics->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue()
        ->and(ThemeEducationHealthCheck::passed())->toBeTrue();
});

it('fails education diagnostics when the theme definition is not registered', function (): void {
    CapellCore::clearPackages();
    $this->app->instance(ThemeRegistry::class, new ThemeRegistry);

    $definitionCheck = ThemeEducationHealthCheck::runDiagnostics()
        ->first(static fn (DoctorCheckResultData $result): bool => $result->label === 'Theme Education Studio definition');

    throw_unless($definitionCheck instanceof DoctorCheckResultData, RuntimeException::class, 'Education theme definition diagnostic was not returned.');

    expect($definitionCheck->passed)->toBeFalse()
        ->and(ThemeEducationHealthCheck::passed())->toBeFalse();
});
