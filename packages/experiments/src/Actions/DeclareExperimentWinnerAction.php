<?php

declare(strict_types=1);

namespace Capell\Experiments\Actions;

use Capell\Experiments\Enums\ExperimentStatus;
use Capell\Experiments\Models\Experiment;
use Capell\Experiments\Models\ExperimentGoal;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

final class DeclareExperimentWinnerAction
{
    use AsAction;

    public function handle(
        Experiment $experiment,
        ?ExperimentGoal $goal = null,
        ?CarbonInterface $declaredAt = null,
        bool $endExperiment = true,
    ): Experiment {
        $report = BuildWinnerReportAction::run($experiment, $goal);

        if ($report->winningVariantId === null || $report->totalAllocations === 0) {
            throw ValidationException::withMessages([
                'experiment' => __('capell-experiments::generic.errors.no_winner'),
            ]);
        }

        $declaredAt = $declaredAt instanceof CarbonInterface
            ? CarbonImmutable::instance($declaredAt)
            : CarbonImmutable::now();

        $experiment->winning_variant_id = $report->winningVariantId;
        $experiment->winner_declared_at = $declaredAt;
        $experiment->metadata = array_replace_recursive($experiment->metadata ?? [], [
            'winner_report' => $report->toArray(),
        ]);

        if ($endExperiment) {
            $experiment->status = ExperimentStatus::Ended;
            $experiment->ends_at ??= $declaredAt;
        }

        $experiment->save();

        return $experiment->fresh(['winningVariant']) ?? $experiment;
    }
}
