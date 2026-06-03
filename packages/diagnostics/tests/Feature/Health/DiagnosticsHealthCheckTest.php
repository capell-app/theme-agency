<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Diagnostics\Health\DiagnosticsHealthCheck;

it('exposes runnable diagnostics rather than only the contract method', function (): void {
    $results = DiagnosticsHealthCheck::runDiagnostics();

    expect($results)->not->toBeEmpty()
        ->and($results->every(fn (DoctorCheckResultData $result): bool => $result->label !== ''))->toBeTrue();
});

it('asserts its declared system-health-widget capability', function (): void {
    $labels = DiagnosticsHealthCheck::runDiagnostics()
        ->map(fn (DoctorCheckResultData $result): string => $result->label)
        ->all();

    expect($labels)->toContain('System health widgets')
        ->and($labels)->toContain('Package catalog discovery');
});

it('passes() reflects the conjunction of its assertions', function (): void {
    $expected = DiagnosticsHealthCheck::runDiagnostics()
        ->every(fn (DoctorCheckResultData $result): bool => $result->passed);

    expect(DiagnosticsHealthCheck::passed())->toBe($expected);
});
