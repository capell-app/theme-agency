<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions\Health;

use Capell\Diagnostics\Data\Health\ExtensionHealthReportData;
use Capell\Diagnostics\Data\Health\ExtensionHealthTrendData;
use Capell\Diagnostics\Models\DiagnosticsHealthSnapshot;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildExtensionHealthTrendAction
{
    use AsAction;

    public function handle(ExtensionHealthReportData $report): ExtensionHealthTrendData
    {
        if (! Schema::hasTable('diagnostics_health_snapshots')) {
            return new ExtensionHealthTrendData(
                currentStatus: $report->overallStatus,
                currentScore: $report->healthScore,
            );
        }

        $previousSnapshot = DiagnosticsHealthSnapshot::query()
            ->latest('recorded_at')
            ->first();

        if (! $previousSnapshot instanceof DiagnosticsHealthSnapshot) {
            return new ExtensionHealthTrendData(
                currentStatus: $report->overallStatus,
                currentScore: $report->healthScore,
            );
        }

        return new ExtensionHealthTrendData(
            currentStatus: $report->overallStatus,
            currentScore: $report->healthScore,
            previousStatus: $previousSnapshot->overall_status,
            previousScore: $previousSnapshot->health_score,
            scoreDelta: $report->healthScore - $previousSnapshot->health_score,
            recordedAt: $previousSnapshot->recorded_at->toIso8601String(),
        );
    }
}
