<?php

declare(strict_types=1);

use Capell\Diagnostics\Actions\Health\BuildExtensionHealthTrendAction;
use Capell\Diagnostics\Actions\Health\RecordExtensionHealthReportAction;
use Capell\Diagnostics\Data\Health\ExtensionHealthReportData;
use Capell\Diagnostics\Data\Health\HealthCheckResultData;
use Capell\Diagnostics\Enums\HealthCheckImplementationStatus;
use Capell\Diagnostics\Models\DiagnosticsHealthSnapshot;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\DataCollection;

it('records extension health report snapshots with per-check payloads', function (): void {
    $report = extensionHealthReportForSnapshot(score: 82, status: 'degraded');

    $snapshot = RecordExtensionHealthReportAction::run($report, CarbonImmutable::parse('2026-06-07 10:30:00'));

    expect($snapshot)->toBeInstanceOf(DiagnosticsHealthSnapshot::class)
        ->and($snapshot?->overall_status)->toBe('degraded')
        ->and($snapshot?->health_score)->toBe(82)
        ->and($snapshot?->checks)->toHaveCount(1)
        ->and($snapshot?->checks[0]['package'])->toBe('capell-app/example')
        ->and($snapshot?->recorded_at?->toDateTimeString())->toBe('2026-06-07 10:30:00');
});

it('builds trend data from the latest previous health snapshot', function (): void {
    DiagnosticsHealthSnapshot::query()->create([
        'overall_status' => 'degraded',
        'health_score' => 70,
        'worst_severity' => 'warning',
        'declared_count' => 3,
        'implemented_count' => 3,
        'stub_count' => 0,
        'broken_count' => 0,
        'executed_count' => 3,
        'passed_count' => 2,
        'failed_count' => 1,
        'checks' => [],
        'recorded_at' => CarbonImmutable::parse('2026-06-07 09:00:00'),
    ]);

    $trend = BuildExtensionHealthTrendAction::run(extensionHealthReportForSnapshot(score: 95, status: 'healthy'));

    expect($trend->currentStatus)->toBe('healthy')
        ->and($trend->currentScore)->toBe(95)
        ->and($trend->previousStatus)->toBe('degraded')
        ->and($trend->previousScore)->toBe(70)
        ->and($trend->scoreDelta)->toBe(25)
        ->and($trend->recordedAt)->toContain('2026-06-07');
});

function extensionHealthReportForSnapshot(int $score, string $status): ExtensionHealthReportData
{
    return new ExtensionHealthReportData(
        declaredCount: 1,
        implementedCount: 1,
        stubCount: 0,
        brokenCount: 0,
        executedCount: 1,
        passedCount: 1,
        failedCount: 0,
        checks: HealthCheckResultData::collect([
            new HealthCheckResultData(
                packageName: 'capell-app/example',
                key: 'example.health',
                label: 'Example health',
                className: 'ExampleHealthCheck',
                severity: 'critical',
                implementationStatus: HealthCheckImplementationStatus::Implemented,
                passed: true,
                message: 'All good.',
            ),
        ], DataCollection::class),
        overallStatus: $status,
        healthScore: $score,
        worstSeverity: null,
    );
}
