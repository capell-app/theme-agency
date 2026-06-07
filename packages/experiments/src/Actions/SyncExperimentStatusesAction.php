<?php

declare(strict_types=1);

namespace Capell\Experiments\Actions;

use Capell\Experiments\Data\ExperimentStatusSyncResultData;
use Capell\Experiments\Enums\ExperimentStatus;
use Capell\Experiments\Models\Experiment;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class SyncExperimentStatusesAction
{
    use AsAction;

    public function handle(?DateTimeInterface $now = null): ExperimentStatusSyncResultData
    {
        $currentTime = CarbonImmutable::parse($now ?? CarbonImmutable::now());

        $expiredToEnded = Experiment::query()
            ->whereIn('status', [ExperimentStatus::Scheduled->value, ExperimentStatus::Active->value])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', $currentTime)
            ->update([
                'status' => ExperimentStatus::Ended->value,
                'updated_at' => $currentTime,
            ]);

        $scheduledToActive = Experiment::query()
            ->where('status', ExperimentStatus::Scheduled->value)
            ->whereNotNull('starts_at')
            ->where('starts_at', '<=', $currentTime)
            ->where(function (Builder $query) use ($currentTime): void {
                $query
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>', $currentTime);
            })
            ->update([
                'status' => ExperimentStatus::Active->value,
                'updated_at' => $currentTime,
            ]);

        return new ExperimentStatusSyncResultData(
            scheduledToActive: $scheduledToActive,
            expiredToEnded: $expiredToEnded,
        );
    }
}
