<?php

declare(strict_types=1);

namespace Capell\Experiments\Actions;

use Capell\Experiments\Data\ExperimentGoalEventData;
use Capell\Experiments\Models\ExperimentAllocation;
use Capell\Experiments\Models\ExperimentGoal;
use Capell\Experiments\Models\ExperimentGoalEvent;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordGoalEventAction
{
    use AsAction;

    public function handle(ExperimentAllocation $allocation, ExperimentGoal $goal, ExperimentGoalEventData $data): ExperimentGoalEvent
    {
        return ExperimentGoalEvent::query()->create([
            'experiment_id' => $allocation->experiment_id,
            'experiment_variant_id' => $allocation->experiment_variant_id,
            'experiment_goal_id' => $goal->id,
            'experiment_allocation_id' => $allocation->id,
            'event_key' => $data->eventKey,
            'value_amount' => $data->valueAmount ?? $goal->value_amount,
            'occurred_at' => $data->occurredAt ?? CarbonImmutable::now(),
            'metadata' => $data->metadata,
        ]);
    }
}
