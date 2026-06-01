<?php

declare(strict_types=1);

namespace Capell\Experiments\Actions;

use Capell\Experiments\Data\WinnerReportData;
use Capell\Experiments\Data\WinnerVariantReportData;
use Capell\Experiments\Models\Experiment;
use Capell\Experiments\Models\ExperimentGoal;
use Capell\Experiments\Models\ExperimentVariant;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildWinnerReportAction
{
    use AsAction;

    public function handle(Experiment $experiment, ?ExperimentGoal $goal = null): WinnerReportData
    {
        $goal ??= $experiment->goals()
            ->where('is_active', true)
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->first();

        /** @var Collection<int, ExperimentVariant> $variants */
        $variants = $experiment->variants()->orderBy('sort_order')->get();

        $allocationCounts = $experiment
            ->allocations()
            ->selectRaw('experiment_variant_id, count(*) as aggregate_count')
            ->groupBy('experiment_variant_id')
            ->pluck('aggregate_count', 'experiment_variant_id');

        $conversionQuery = $experiment
            ->goalEvents()
            ->selectRaw('experiment_variant_id, count(*) as aggregate_count')
            ->groupBy('experiment_variant_id');

        if ($goal !== null) {
            $conversionQuery->where('experiment_goal_id', $goal->id);
        }

        $conversionCounts = $conversionQuery->pluck('aggregate_count', 'experiment_variant_id');

        $winner = null;
        $variantReports = $variants->map(function (ExperimentVariant $variant) use ($allocationCounts, $conversionCounts, &$winner): WinnerVariantReportData {
            $allocations = (int) ($allocationCounts[$variant->id] ?? 0);
            $conversions = (int) ($conversionCounts[$variant->id] ?? 0);
            $conversionRate = $allocations > 0 ? round($conversions / $allocations, 6) : 0.0;
            $report = new WinnerVariantReportData(
                variantId: $variant->id,
                variantKey: $variant->key,
                variantName: $variant->name,
                allocations: $allocations,
                conversions: $conversions,
                conversionRate: $conversionRate,
            );

            if (! $winner instanceof WinnerVariantReportData || $report->conversionRate > $winner->conversionRate) {
                $winner = $report;
            }

            return $report;
        })->values();

        $variantReports = $variantReports->map(function (WinnerVariantReportData $report) use ($winner): WinnerVariantReportData {
            $report->isWinner = $winner instanceof WinnerVariantReportData && $report->variantId === $winner->variantId;

            return $report;
        });

        return new WinnerReportData(
            experimentId: $experiment->id,
            goalId: $goal?->id,
            totalAllocations: (int) $allocationCounts->sum(),
            totalConversions: (int) $conversionCounts->sum(),
            winningVariantId: $winner?->variantId,
            winningVariantKey: $winner?->variantKey,
            variants: array_values($variantReports->all()),
        );
    }
}
