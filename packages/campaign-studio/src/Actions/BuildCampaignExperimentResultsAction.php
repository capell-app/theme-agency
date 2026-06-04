<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Actions;

use Capell\CampaignStudio\Data\CampaignExperimentResultsData;
use Capell\CampaignStudio\Data\CampaignExperimentVariantResultData;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\Experiments\Actions\BuildWinnerReportAction;
use Capell\Experiments\Data\WinnerVariantReportData;
use Capell\Experiments\Enums\ExperimentSubjectType;
use Capell\Experiments\Models\Experiment;
use Capell\Experiments\Models\ExperimentVariant;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildCampaignExperimentResultsAction
{
    use AsAction;

    public function handle(CampaignGroup $campaignGroup): ?CampaignExperimentResultsData
    {
        if (! $this->experimentsPackageIsAvailable()) {
            return null;
        }

        /** @var Experiment|null $experiment */
        $experiment = Experiment::query()
            ->where('subject_type', ExperimentSubjectType::Campaign)
            ->where('subject_class', CampaignGroup::class)
            ->where('subject_id', $campaignGroup->getKey())
            ->first();

        if (! $experiment instanceof Experiment) {
            return null;
        }

        $report = BuildWinnerReportAction::run($experiment);
        /** @var Collection<int, ExperimentVariant> $variants */
        $variants = $experiment->variants()->get()->keyBy('id');
        $controlRate = $this->controlConversionRate($report->variants, $variants);
        $winningVariantId = $report->winningVariantId;

        return new CampaignExperimentResultsData(
            experimentId: $report->experimentId,
            goalId: $report->goalId,
            totalAllocations: $report->totalAllocations,
            totalConversions: $report->totalConversions,
            winningVariantKey: $this->winningVariantKey($report->variants, $winningVariantId),
            variants: array_values(array_map(
                fn (WinnerVariantReportData $variantReport): CampaignExperimentVariantResultData => $this->variantResult($variantReport, $variants, $controlRate, $winningVariantId),
                $report->variants,
            )),
        );
    }

    private function experimentsPackageIsAvailable(): bool
    {
        return class_exists(BuildWinnerReportAction::class)
            && class_exists(Experiment::class);
    }

    /**
     * @param  list<WinnerVariantReportData>  $variantReports
     * @param  Collection<int, ExperimentVariant>  $variants
     */
    private function controlConversionRate(array $variantReports, Collection $variants): ?float
    {
        foreach ($variantReports as $variantReport) {
            $variant = $variants->get($variantReport->variantId);

            if ($variant instanceof ExperimentVariant && $variant->is_control) {
                return $variantReport->conversionRate;
            }
        }

        return null;
    }

    /**
     * @param  Collection<int, ExperimentVariant>  $variants
     */
    private function variantResult(WinnerVariantReportData $variantReport, Collection $variants, ?float $controlRate, ?int $winningVariantId): CampaignExperimentVariantResultData
    {
        $variant = $variants->get($variantReport->variantId);
        $isControl = $variant instanceof ExperimentVariant && $variant->is_control;

        return new CampaignExperimentVariantResultData(
            variantKey: $variantReport->variantKey,
            variantName: $variantReport->variantName,
            isControl: $isControl,
            allocations: $variantReport->allocations,
            conversions: $variantReport->conversions,
            conversionRate: round($variantReport->conversionRate * 100, 2),
            liftPercent: $this->liftPercent($variantReport->conversionRate, $controlRate, $isControl),
            isWinner: $winningVariantId !== null && $variantReport->variantId === $winningVariantId,
        );
    }

    /**
     * @param  list<WinnerVariantReportData>  $variantReports
     */
    private function winningVariantKey(array $variantReports, ?int $winningVariantId): ?string
    {
        if ($winningVariantId === null) {
            return null;
        }

        foreach ($variantReports as $variantReport) {
            if ($variantReport->variantId === $winningVariantId) {
                return $variantReport->variantKey;
            }
        }

        return null;
    }

    private function liftPercent(float $conversionRate, ?float $controlRate, bool $isControl): ?float
    {
        if ($isControl || $controlRate === null || $controlRate <= 0.0) {
            return null;
        }

        return round((($conversionRate - $controlRate) / $controlRate) * 100, 2);
    }
}
