<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Experiments\Health\ExperimentsHealthCheck;
use Illuminate\Support\Facades\Schema;

it('passes the health check when every experiment table is present', function (): void {
    expect(ExperimentsHealthCheck::passed())->toBeTrue();

    $diagnostics = ExperimentsHealthCheck::runDiagnostics();

    expect($diagnostics)->toHaveCount(2)
        ->and($diagnostics->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue()
        ->and($diagnostics->first()?->remediation)->toBeNull();
});

it('fails the health check when a required experiment table is missing', function (): void {
    Schema::drop('experiment_goal_events');

    $diagnostics = ExperimentsHealthCheck::runDiagnostics();
    $storageTablesResult = $diagnostics->first();

    expect(ExperimentsHealthCheck::passed())->toBeFalse()
        ->and($storageTablesResult?->passed)->toBeFalse()
        ->and($storageTablesResult?->message)->toContain('experiment_goal_events')
        ->and($storageTablesResult?->remediation)->not->toBeNull();
});
