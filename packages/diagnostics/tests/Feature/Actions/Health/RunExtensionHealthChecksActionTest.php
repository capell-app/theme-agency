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
 * @param  list<array{key: string, label: string, class: string, severity: string}>  $healthChecks
 */
function runFixtureHealthChecks(array $healthChecks): ExtensionHealthReportData
{
    $packagesPath = sys_get_temp_dir() . '/capell_health_checks_' . uniqid();
    $packagePath = $packagesPath . '/fixture-package';
    File::ensureDirectoryExists($packagePath);

    File::put($packagePath . '/capell.json', json_encode([
        'name' => 'capell-app/fixture-package',
        'slug' => 'fixture-package',
        'healthChecks' => $healthChecks,
    ], JSON_THROW_ON_ERROR));

    try {
        return (new RunExtensionHealthChecksAction(null, $packagesPath))->handle();
    } finally {
        File::deleteDirectory($packagesPath);
    }
}

it('reports an implemented passing health check as passing', function (): void {
    $report = runFixtureHealthChecks([
        ['key' => 'fixture.passing', 'label' => 'Passing', 'class' => PassingFixtureHealthCheck::class, 'severity' => 'warning'],
    ]);

    $check = $report->checks->toCollection()->first();

    expect($report->declaredCount)->toBe(1)
        ->and($report->implementedCount)->toBe(1)
        ->and($report->executedCount)->toBe(1)
        ->and($report->passedCount)->toBe(1)
        ->and($report->failedCount)->toBe(0)
        ->and($check)->toBeInstanceOf(HealthCheckResultData::class)
        ->and($check->implementationStatus)->toBe(HealthCheckImplementationStatus::Implemented)
        ->and($check->passed)->toBeTrue();
});

it('reports an implemented failing health check as failing', function (): void {
    $report = runFixtureHealthChecks([
        ['key' => 'fixture.failing', 'label' => 'Failing', 'class' => FailingFixtureHealthCheck::class, 'severity' => 'critical'],
    ]);

    $check = $report->checks->toCollection()->first();

    expect($report->executedCount)->toBe(1)
        ->and($report->failedCount)->toBe(1)
        ->and($report->passedCount)->toBe(0)
        ->and($check->implementationStatus)->toBe(HealthCheckImplementationStatus::Implemented)
        ->and($check->passed)->toBeFalse()
        ->and($check->failed())->toBeTrue()
        ->and($check->message)->toContain('Required route registered');
});

it('surfaces the declared severity of each check', function (): void {
    $report = runFixtureHealthChecks([
        ['key' => 'fixture.failing', 'label' => 'Failing', 'class' => FailingFixtureHealthCheck::class, 'severity' => 'critical'],
        ['key' => 'fixture.passing', 'label' => 'Passing', 'class' => PassingFixtureHealthCheck::class, 'severity' => 'warning'],
    ]);

    $bySeverity = $report->checks->toCollection()->keyBy('key');

    expect($bySeverity->get('fixture.failing')->severity)->toBe('critical')
        ->and($bySeverity->get('fixture.passing')->severity)->toBe('warning');
});

it('classifies a contract-only class as a stub and does not execute it', function (): void {
    $report = runFixtureHealthChecks([
        ['key' => 'fixture.stub', 'label' => 'Stub', 'class' => StubFixtureHealthCheck::class, 'severity' => 'warning'],
    ]);

    $check = $report->checks->toCollection()->first();

    expect($report->stubCount)->toBe(1)
        ->and($report->implementedCount)->toBe(0)
        ->and($report->executedCount)->toBe(0)
        ->and($check->implementationStatus)->toBe(HealthCheckImplementationStatus::Stub)
        ->and($check->passed)->toBeNull();
});

it('classifies a missing or non-contract class as broken', function (): void {
    $report = runFixtureHealthChecks([
        ['key' => 'fixture.broken', 'label' => 'Broken', 'class' => 'Capell\\Diagnostics\\Does\\Not\\Exist', 'severity' => 'critical'],
    ]);

    $check = $report->checks->toCollection()->first();

    expect($report->brokenCount)->toBe(1)
        ->and($report->executedCount)->toBe(0)
        ->and($check->implementationStatus)->toBe(HealthCheckImplementationStatus::Broken)
        ->and($check->passed)->toBeNull();
});

it('degrades gracefully when an implemented check throws', function (): void {
    $report = runFixtureHealthChecks([
        ['key' => 'fixture.throws', 'label' => 'Throws', 'class' => ThrowingFixtureHealthCheck::class, 'severity' => 'warning'],
    ]);

    $check = $report->checks->toCollection()->first();

    expect($report->failedCount)->toBe(1)
        ->and($check->implementationStatus)->toBe(HealthCheckImplementationStatus::Implemented)
        ->and($check->passed)->toBeFalse()
        ->and($check->message)->toContain('threw while running');
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
