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

    public function handle(
        Experiment $experiment,
        ?ExperimentGoal $goal = null,
        int $minimumSampleSize = 100,
        float $confidenceLevel = 0.95,
    ): WinnerReportData {
        $minimumSampleSize = max(1, $minimumSampleSize);
        $confidenceLevel = max(0.5, min(0.999, $confidenceLevel));

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

        $controlVariant = $variants->first(static fn (ExperimentVariant $variant): bool => (bool) $variant->is_control);

        $variantReports = $variants->map(function (ExperimentVariant $variant) use ($allocationCounts, $conversionCounts, $controlVariant, $minimumSampleSize): WinnerVariantReportData {
            $allocations = (int) ($allocationCounts[$variant->id] ?? 0);
            $conversions = (int) ($conversionCounts[$variant->id] ?? 0);

            return new WinnerVariantReportData(
                variantId: $variant->id,
                variantKey: $variant->key,
                variantName: $variant->name,
                allocations: $allocations,
                conversions: $conversions,
                conversionRate: $this->conversionRate($conversions, $allocations),
                isBaseline: $controlVariant instanceof ExperimentVariant && $variant->id === $controlVariant->id,
                meetsSampleSize: $allocations >= $minimumSampleSize,
            );
        })->values();

        /** @var WinnerVariantReportData|null $baseline */
        $baseline = $variantReports->first(static fn (WinnerVariantReportData $report): bool => $report->isBaseline)
            ?? $variantReports->first();

        if ($baseline instanceof WinnerVariantReportData) {
            $baseline->isBaseline = true;
        }

        $alpha = 1.0 - $confidenceLevel;

        $variantReports = $variantReports->map(function (WinnerVariantReportData $report) use ($baseline, $alpha, $minimumSampleSize): WinnerVariantReportData {
            $this->applySignificance($report, $baseline, $alpha, $minimumSampleSize);

            return $report;
        });

        /** @var WinnerVariantReportData|null $winner */
        $winner = $variantReports
            ->filter(static fn (WinnerVariantReportData $report): bool => $report->isStatisticallySignificant)
            ->sortByDesc(static fn (WinnerVariantReportData $report): float => $report->conversionRate)
            ->first();

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
            minimumSampleSize: $minimumSampleSize,
            confidenceLevel: $confidenceLevel,
            isStatisticallySignificant: $winner instanceof WinnerVariantReportData,
        );
    }

    private function conversionRate(int $conversions, int $allocations): float
    {
        return $allocations > 0 ? round($conversions / $allocations, 6) : 0.0;
    }

    private function applySignificance(
        WinnerVariantReportData $report,
        ?WinnerVariantReportData $baseline,
        float $alpha,
        int $minimumSampleSize,
    ): void {
        $report->meetsSampleSize = $report->allocations >= $minimumSampleSize;

        if (! $baseline instanceof WinnerVariantReportData || $report->variantId === $baseline->variantId) {
            return;
        }

        if ($baseline->conversionRate > 0.0) {
            $report->lift = round(($report->conversionRate - $baseline->conversionRate) / $baseline->conversionRate, 6);
        }

        if (! $report->meetsSampleSize || $baseline->allocations < $minimumSampleSize) {
            return;
        }

        if ($report->conversionRate <= $baseline->conversionRate) {
            return;
        }

        $report->pValue = $this->twoProportionPValue(
            baselineConversions: $baseline->conversions,
            baselineAllocations: $baseline->allocations,
            candidateConversions: $report->conversions,
            candidateAllocations: $report->allocations,
        );
        $report->isStatisticallySignificant = $report->pValue <= $alpha;
    }

    private function twoProportionPValue(
        int $baselineConversions,
        int $baselineAllocations,
        int $candidateConversions,
        int $candidateAllocations,
    ): float {
        $pooledRate = ($baselineConversions + $candidateConversions) / ($baselineAllocations + $candidateAllocations);
        $standardError = sqrt($pooledRate * (1.0 - $pooledRate) * ((1.0 / $baselineAllocations) + (1.0 / $candidateAllocations)));

        if ($standardError <= 0.0) {
            return 1.0;
        }

        $zScore = (($candidateConversions / $candidateAllocations) - ($baselineConversions / $baselineAllocations)) / $standardError;

        return round(2.0 * (1.0 - $this->normalCdf(abs($zScore))), 6);
    }

    private function normalCdf(float $value): float
    {
        $coefficient = 1.0 / (1.0 + (0.2316419 * abs($value)));
        $density = 0.3989422804014327 * exp(-($value * $value) / 2.0);
        $probability = $density * $coefficient * (
            0.319381530
            + $coefficient * (
                -0.356563782
                + $coefficient * (
                    1.781477937
                    + $coefficient * (
                        -1.821255978
                        + $coefficient * 1.330274429
                    )
                )
            )
        );

        return $value >= 0.0 ? 1.0 - $probability : $probability;
    }
}
