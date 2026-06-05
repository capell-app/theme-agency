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

    expect($labels)->toContain((string) __('capell-diagnostics::package.health_check_system_health_widgets_label'))
        ->and($labels)->toContain((string) __('capell-diagnostics::package.health_check_package_catalog_label'));
});

it('can run a single declared assertion by manifest key', function (): void {
    $results = DiagnosticsHealthCheck::runDiagnostics('diagnostics.system-health-widgets');

    expect($results)->toHaveCount(1)
        ->and($results->first())->toBeInstanceOf(DoctorCheckResultData::class)
        ->and($results->first()?->label)->toBe((string) __('capell-diagnostics::package.health_check_system_health_widgets_label'));
});

it('maps each declared manifest key to one runnable assertion', function (): void {
    $expectedLabelsByKey = [
        'diagnostics.package-catalog' => (string) __('capell-diagnostics::package.health_check_package_catalog_label'),
        'diagnostics.manifest-health' => (string) __('capell-diagnostics::package.health_check_manifest_metadata_label'),
        'diagnostics.system-health-widgets' => (string) __('capell-diagnostics::package.health_check_system_health_widgets_label'),
        'diagnostics.queue-health' => (string) __('capell-diagnostics::package.health_check_queue_health_label'),
    ];

    foreach ($expectedLabelsByKey as $key => $expectedLabel) {
        $results = DiagnosticsHealthCheck::runDiagnostics($key);

        expect($results)->toHaveCount(1)
            ->and($results->first())->toBeInstanceOf(DoctorCheckResultData::class)
            ->and($results->first()?->label)->toBe($expectedLabel);
    }

    expect(DiagnosticsHealthCheck::runDiagnostics('diagnostics.unknown'))->toBeEmpty();
});

it('passes() reflects the conjunction of its assertions', function (): void {
    $expected = DiagnosticsHealthCheck::runDiagnostics()
        ->every(fn (DoctorCheckResultData $result): bool => $result->passed);

    expect(DiagnosticsHealthCheck::passed())->toBe($expected);
});
