<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions\Health;

use Capell\Diagnostics\Data\Health\ExtensionHealthReportData;
use Capell\Diagnostics\Data\Health\HealthCheckResultData;
use Capell\Diagnostics\Models\DiagnosticsHealthSnapshot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static DiagnosticsHealthSnapshot|null run(ExtensionHealthReportData $report, CarbonImmutable|null $recordedAt = null)
 */
final class RecordExtensionHealthReportAction
{
    use AsAction;

    public function handle(ExtensionHealthReportData $report, ?CarbonImmutable $recordedAt = null): ?DiagnosticsHealthSnapshot
    {
        if (! Schema::hasTable('diagnostics_health_snapshots')) {
            return null;
        }

        return DiagnosticsHealthSnapshot::query()->create([
            'overall_status' => $report->overallStatus,
            'health_score' => $report->healthScore,
            'worst_severity' => $report->worstSeverity,
            'declared_count' => $report->declaredCount,
            'implemented_count' => $report->implementedCount,
            'stub_count' => $report->stubCount,
            'broken_count' => $report->brokenCount,
            'executed_count' => $report->executedCount,
            'passed_count' => $report->passedCount,
            'failed_count' => $report->failedCount,
            'checks' => $this->checksPayload($report),
            'recorded_at' => $recordedAt ?? CarbonImmutable::now(),
        ]);
    }

    /**
     * @return list<array{package: string, key: string, severity: string, implementation: string, passed: bool|null, message: string|null}>
     */
    private function checksPayload(ExtensionHealthReportData $report): array
    {
        $payload = [];

        foreach ($report->checks as $check) {
            if (! $check instanceof HealthCheckResultData) {
                continue;
            }

            $payload[] = [
                'package' => $check->packageName,
                'key' => $check->key,
                'severity' => $check->severity,
                'implementation' => $check->implementationStatus->value,
                'passed' => $check->passed,
                'message' => $check->message,
            ];
        }

        return $payload;
    }
}
