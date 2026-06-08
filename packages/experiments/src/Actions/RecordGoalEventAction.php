<?php

declare(strict_types=1);

namespace Capell\Experiments\Actions;

use Capell\Experiments\Data\ExperimentGoalEventData;
use Capell\Experiments\Models\Experiment;
use Capell\Experiments\Models\ExperimentAllocation;
use Capell\Experiments\Models\ExperimentGoal;
use Capell\Experiments\Models\ExperimentGoalEvent;
use Capell\Insights\Actions\RecordConversionAction;
use Capell\Insights\Models\InsightsEvent;
use Capell\Insights\Models\InsightsVisit;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordGoalEventAction
{
    use AsAction;

    public function handle(ExperimentAllocation $allocation, ExperimentGoal $goal, ExperimentGoalEventData $data): ExperimentGoalEvent
    {
        $attributes = [
            'experiment_id' => $allocation->experiment_id,
            'experiment_variant_id' => $allocation->experiment_variant_id,
            'experiment_goal_id' => $goal->id,
            'experiment_allocation_id' => $allocation->id,
            'event_key' => $data->eventKey,
        ];

        $values = [
            'value_amount' => $data->valueAmount ?? $goal->value_amount,
            'occurred_at' => $data->occurredAt ?? CarbonImmutable::now(),
            'metadata' => $data->metadata,
        ];

        $event = $data->eventKey === null
            ? ExperimentGoalEvent::query()->create([...$attributes, ...$values])
            : ExperimentGoalEvent::query()->firstOrCreate($attributes, $values);

        if ($event->wasRecentlyCreated) {
            $this->recordInsightsConversion($event, $allocation, $goal);
        }

        return $event;
    }

    private function recordInsightsConversion(ExperimentGoalEvent $event, ExperimentAllocation $allocation, ExperimentGoal $goal): void
    {
        if (! $this->insightsStorageIsAvailable()) {
            return;
        }

        $visitUuid = $this->insightsVisitUuid($allocation);

        if ($visitUuid === null) {
            return;
        }

        $experiment = $allocation->relationLoaded('experiment')
            ? $allocation->experiment
            : $allocation->experiment()->first();

        if (! $experiment instanceof Experiment) {
            return;
        }

        RecordConversionAction::run(
            visitUuid: $visitUuid,
            eventName: sprintf('experiment.%s.%s', $experiment->key, $goal->key),
            url: $this->eventUrl($allocation),
            label: trim($experiment->name . ': ' . $goal->name),
            sourcePackage: 'capell-app/experiments',
            value: $this->numericValue($event->value_amount),
            occurredAt: $event->occurred_at instanceof CarbonInterface
                ? $event->occurred_at->toIso8601String()
                : null,
        );
    }

    private function insightsVisitUuid(ExperimentAllocation $allocation): ?string
    {
        if ($allocation->source !== 'insights') {
            return null;
        }

        if (! is_string($allocation->external_id) || trim($allocation->external_id) === '') {
            return null;
        }

        return $allocation->external_id;
    }

    private function insightsStorageIsAvailable(): bool
    {
        if (! class_exists(RecordConversionAction::class)
            || ! class_exists(InsightsVisit::class)
            || ! class_exists(InsightsEvent::class)) {
            return false;
        }

        return Schema::hasTable((new InsightsVisit)->getTable())
            && Schema::hasTable((new InsightsEvent)->getTable());
    }

    private function eventUrl(ExperimentAllocation $allocation): string
    {
        $context = is_array($allocation->context) ? $allocation->context : [];
        $url = $context['url'] ?? $context['path'] ?? null;

        if (is_string($url) && trim($url) !== '') {
            return $url;
        }

        return '/';
    }

    private function numericValue(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
