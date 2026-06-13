<?php

declare(strict_types=1);

use Capell\Diagnostics\Actions\Health\RunExtensionHealthChecksAction;
use Capell\Diagnostics\Data\Health\ExtensionHealthReportData;
use Capell\Diagnostics\Data\Health\HealthCheckResultData;
use Capell\Diagnostics\Enums\HealthCheckImplementationStatus;
use Capell\Diagnostics\Tests\Fixtures\Health\FailingFixtureHealthCheck;
use Capell\Diagnostics\Tests\Fixtures\Health\PassingFixtureHealthCheck;
use Capell\Diagnostics\Tests\Fixtures\Health\StubFixtureHealthCheck;
use Capell\Diagnostics\Tests\Fixtures\Health\ThrowingFixtureHealthCheck;
use Illuminate\Support\Facades\File;

/**
 * Writes a single package directory containing a capell.json that declares the
 * provided health-check entries, then runs the action against it.
 *
 * @param  list<array{key: string, label: string, class: string, severity: string, surface?: string}>  $healthChecks
 * @param  array<string, mixed>  $manifest
 */
function runFixtureHealthChecks(array $healthChecks, array $manifest = []): ExtensionHealthReportData
{
    $packagesPath = sys_get_temp_dir() . '/capell_health_checks_' . uniqid();
    $packagePath = $packagesPath . '/fixture-package';
    File::ensureDirectoryExists($packagePath);

    File::put($packagePath . '/capell.json', json_encode([
        'name' => 'capell-app/fixture-package',
        'slug' => 'fixture-package',
        'healthChecks' => $healthChecks,
        ...$manifest,
    ], JSON_THROW_ON_ERROR));

    try {
        return (new RunExtensionHealthChecksAction($packagesPath))->handle();
    } finally {
        File::deleteDirectory($packagesPath);
    }
}

function firstFixtureHealthCheck(ExtensionHealthReportData $report): HealthCheckResultData
{
    $check = $report->checks->toCollection()->first();

    throw_unless($check instanceof HealthCheckResultData, RuntimeException::class, 'Expected the fixture report to contain a health check result.');

    return $check;
}

function fixtureHealthCheckByKey(ExtensionHealthReportData $report, string $key): HealthCheckResultData
{
    $check = $report->checks->toCollection()->keyBy('key')->get($key);

    if (! $check instanceof HealthCheckResultData) {
        throw new RuntimeException(sprintf('Expected fixture health check [%s] to exist.', $key));
    }

    return $check;
}

it('reports an implemented passing health check as passing', function (): void {
    $report = runFixtureHealthChecks([
        ['key' => 'fixture.passing', 'label' => 'Passing', 'class' => PassingFixtureHealthCheck::class, 'severity' => 'warning'],
    ]);

    $check = firstFixtureHealthCheck($report);

    expect($report->declaredCount)->toBe(1)
        ->and($report->implementedCount)->toBe(1)
        ->and($report->executedCount)->toBe(1)
        ->and($report->passedCount)->toBe(1)
        ->and($report->failedCount)->toBe(0)
        ->and($check)->toBeInstanceOf(HealthCheckResultData::class)
        ->and($check->implementationStatus)->toBe(HealthCheckImplementationStatus::Implemented)
        ->and($check->surface)->toBe('shared')
        ->and($check->coverage)->toBe(['manifest-validity'])
        ->and($check->passed)->toBeTrue()
        ->and($check->message)->toBe((string) __('capell-diagnostics::package.health_check_assertions_passed', [
            'total' => 1,
        ]));
});

it('adds manifest-derived coverage dimensions to each health check result', function (): void {
    $report = runFixtureHealthChecks([
        ['key' => 'fixture.passing', 'label' => 'Passing', 'class' => PassingFixtureHealthCheck::class, 'severity' => 'warning', 'surface' => 'frontend'],
    ], [
        'providers' => [
            'runtime' => ['Capell\\Fixture\\FixtureServiceProvider'],
        ],
        'database' => [
            'requiredTables' => ['fixture_records'],
        ],
        'settings' => [
            'class' => 'Capell\\Fixture\\Settings\\FixtureSettings',
        ],
        'permissions' => ['fixture.manage'],
        'security' => [
            'publicSurface' => [
                'routeNames' => ['capell-fixture.public'],
            ],
        ],
    ]);

    $check = firstFixtureHealthCheck($report);

    expect($check->surface)->toBe('frontend')
        ->and($check->coverage)->toBe([
            'admin-permissions',
            'manifest-validity',
            'provider-registration',
            'public-route-security',
            'required-migrations',
            'required-settings',
        ]);
});

it('reports an implemented failing health check as failing', function (): void {
    $report = runFixtureHealthChecks([
        ['key' => 'fixture.failing', 'label' => 'Failing', 'class' => FailingFixtureHealthCheck::class, 'severity' => 'critical'],
    ]);

    $check = firstFixtureHealthCheck($report);

    expect($report->executedCount)->toBe(1)
        ->and($report->failedCount)->toBe(1)
        ->and($report->passedCount)->toBe(0)
        ->and($check->implementationStatus)->toBe(HealthCheckImplementationStatus::Implemented)
        ->and($check->passed)->toBeFalse()
        ->and($check->failed())->toBeTrue()
        ->and($check->message)->toBe((string) __('capell-diagnostics::package.health_check_assertions_failed', [
            'failed' => 1,
            'total' => 2,
            'failures' => 'Required route registered',
        ]));
});

it('surfaces the declared severity of each check', function (): void {
    $report = runFixtureHealthChecks([
        ['key' => 'fixture.failing', 'label' => 'Failing', 'class' => FailingFixtureHealthCheck::class, 'severity' => 'critical'],
        ['key' => 'fixture.passing', 'label' => 'Passing', 'class' => PassingFixtureHealthCheck::class, 'severity' => 'warning'],
    ]);

    $failingCheck = fixtureHealthCheckByKey($report, 'fixture.failing');
    $passingCheck = fixtureHealthCheckByKey($report, 'fixture.passing');

    expect($failingCheck->severity)->toBe('critical')
        ->and($passingCheck->severity)->toBe('warning');
});

it('classifies a contract-only class as a stub and does not execute it', function (): void {
    $report = runFixtureHealthChecks([
        ['key' => 'fixture.stub', 'label' => 'Stub', 'class' => StubFixtureHealthCheck::class, 'severity' => 'warning'],
    ]);

    $check = firstFixtureHealthCheck($report);

    expect($report->stubCount)->toBe(1)
        ->and($report->implementedCount)->toBe(0)
        ->and($report->executedCount)->toBe(0)
        ->and($check->implementationStatus)->toBe(HealthCheckImplementationStatus::Stub)
        ->and($check->passed)->toBeNull()
        ->and($check->message)->toBe((string) __('capell-diagnostics::package.health_check_stub_no_assertions'));
});

it('classifies a missing or non-contract class as broken', function (): void {
    $report = runFixtureHealthChecks([
        ['key' => 'fixture.broken', 'label' => 'Broken', 'class' => 'Capell\\Diagnostics\\Does\\Not\\Exist', 'severity' => 'critical'],
    ]);

    $check = firstFixtureHealthCheck($report);

    expect($report->brokenCount)->toBe(1)
        ->and($report->executedCount)->toBe(0)
        ->and($check->implementationStatus)->toBe(HealthCheckImplementationStatus::Broken)
        ->and($check->passed)->toBeNull()
        ->and($check->message)->toBe((string) __('capell-diagnostics::package.health_check_broken_missing_class'));
});

it('degrades gracefully when an implemented check throws', function (): void {
    $report = runFixtureHealthChecks([
        ['key' => 'fixture.throws', 'label' => 'Throws', 'class' => ThrowingFixtureHealthCheck::class, 'severity' => 'warning'],
    ]);

    $check = firstFixtureHealthCheck($report);

    expect($report->failedCount)->toBe(1)
        ->and($check->implementationStatus)->toBe(HealthCheckImplementationStatus::Implemented)
        ->and($check->passed)->toBeFalse()
        ->and($check->message)->toBe((string) __('capell-diagnostics::package.health_check_threw', [
            'message' => 'check exploded',
        ]));
});

it('rolls up implemented, stub and broken counts across declared checks', function (): void {
    $report = runFixtureHealthChecks([
        ['key' => 'fixture.passing', 'label' => 'Passing', 'class' => PassingFixtureHealthCheck::class, 'severity' => 'warning'],
        ['key' => 'fixture.stub', 'label' => 'Stub', 'class' => StubFixtureHealthCheck::class, 'severity' => 'warning'],
        ['key' => 'fixture.broken', 'label' => 'Broken', 'class' => 'Missing\\Class', 'severity' => 'critical'],
    ]);

    expect($report->declaredCount)->toBe(3)
        ->and($report->implementedCount)->toBe(1)
        ->and($report->stubCount)->toBe(1)
        ->and($report->brokenCount)->toBe(1);
});

it('calculates a severity rollup and score for failed and incomplete checks', function (): void {
    $report = runFixtureHealthChecks([
        ['key' => 'fixture.passing', 'label' => 'Passing', 'class' => PassingFixtureHealthCheck::class, 'severity' => 'warning'],
        ['key' => 'fixture.failing', 'label' => 'Failing', 'class' => FailingFixtureHealthCheck::class, 'severity' => 'critical'],
        ['key' => 'fixture.stub', 'label' => 'Stub', 'class' => StubFixtureHealthCheck::class, 'severity' => 'warning'],
    ]);

    expect($report->overallStatus)->toBe('critical')
        ->and($report->worstSeverity)->toBe('critical')
        ->and($report->healthScore)->toBe(65);
});
