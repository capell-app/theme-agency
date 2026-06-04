<?php

declare(strict_types=1);

use Capell\Admin\Contracts\Dashboard\ContentHealthDataProvider;
use Capell\Admin\Support\Dashboard\NullContentHealthDataProvider;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Facades\CapellCore;
use Capell\DashboardReports\Health\DashboardReportsHealthCheck;
use Capell\DashboardReports\Providers\DashboardReportsServiceProvider;
use Capell\DashboardReports\Tests\DashboardReportsTestCase;

uses(DashboardReportsTestCase::class);

it('runs real diagnostics for dashboard reports installation health', function (): void {
    $results = DashboardReportsHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(5)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue()
        ->and(DashboardReportsHealthCheck::passed())->toBeTrue()
        ->and($results->first()?->label)->toBe(__('capell-dashboard-reports::dashboard.health_package_installed_label'))
        ->and($results->first()?->message)->toBe(__('capell-dashboard-reports::dashboard.health_package_installed_passed'));
});

it('fails when only the admin null content health provider is bound', function (): void {
    app()->instance(ContentHealthDataProvider::class, new NullContentHealthDataProvider);

    $check = new DashboardReportsHealthCheck;
    $result = $check->contentHealthProviderCheck();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe(__('capell-dashboard-reports::dashboard.health_content_health_provider_failed'))
        ->and($result->remediation)->toBe(__('capell-dashboard-reports::dashboard.health_content_health_provider_remediation'))
        ->and(DashboardReportsHealthCheck::passed())->toBeFalse();
});

it('fails when the package is not marked installed', function (): void {
    CapellCore::forcePackageInstalled(DashboardReportsServiceProvider::$packageName, false);

    $check = new DashboardReportsHealthCheck;
    $result = $check->packageInstalledCheck();

    expect($result->passed)->toBeFalse()
        ->and(DashboardReportsHealthCheck::passed())->toBeFalse();
});
