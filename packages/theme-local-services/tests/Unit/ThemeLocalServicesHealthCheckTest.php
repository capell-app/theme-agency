<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\ThemeStudio\LocalServices\Health\ThemeLocalServicesHealthCheck;

it('runs real diagnostics returning check results', function (): void {
    $results = ThemeLocalServicesHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(3)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when the Local Services theme files and manifest are wired', function (): void {
    $results = ThemeLocalServicesHealthCheck::runDiagnostics();

    expect(ThemeLocalServicesHealthCheck::passed())->toBeTrue()
        ->and($results->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue();
});

it('fails the package file diagnostic when required files are missing', function (): void {
    $check = new ThemeLocalServicesHealthCheck(sys_get_temp_dir() . '/missing-theme-local-services-health-root');

    expect($check->missingRequiredPackageFiles())->toContain('capell.json')
        ->and($check->packageFilesCheck()->passed)->toBeFalse()
        ->and($check->marketplaceManifestCheck()->passed)->toBeFalse();
});
