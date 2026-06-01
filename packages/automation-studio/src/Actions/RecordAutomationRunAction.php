<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Actions;

use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationRunStatus;
use Capell\AutomationStudio\Models\AutomationRule;
use Capell\AutomationStudio\Models\AutomationRun;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordAutomationRunAction
{
    use AsAction;

    public function handle(
        AutomationTriggerEventData $event,
        ?AutomationRule $rule = null,
        ?AutomationRuleActionData $action = null,
        ?AutomationActionResultData $result = null,
        ?int $siteId = null,
        ?string $idempotencyKey = null,
        int $attemptNumber = 1,
        ?int $maxAttempts = null,
    ): AutomationRun {
        $finishedAt = CarbonImmutable::now();

        $attributes = [
            'automation_rule_id' => $rule?->getKey(),
            'site_id' => $siteId ?? $rule?->site_id,
            'rule_key' => $rule?->key,
            'action_key' => $action?->key,
            'trigger_type' => $event->triggerType,
            'action_type' => $action?->type,
            'source_type' => $event->sourceType,
            'source_id' => $event->sourceId,
            'idempotency_key' => $idempotencyKey,
            'attempt_number' => $attemptNumber,
            'max_attempts' => $maxAttempts,
            'queued_at' => $idempotencyKey === null ? null : $event->occurredAt ?? $finishedAt,
            'status' => $result === null
                ? AutomationRunStatus::Pending
                : ($result->success ? AutomationRunStatus::Succeeded : AutomationRunStatus::Failed),
            'message' => $result?->message,
            'payload' => $event->payload,
            'context' => $result?->context ?? [],
            'started_at' => $event->occurredAt ?? $finishedAt,
            'finished_at' => $result === null ? null : $finishedAt,
        ];

        if ($idempotencyKey === null) {
            return AutomationRun::query()->create($attributes);
        }

        /** @var AutomationRun $automationRun */
        $automationRun = AutomationRun::query()->updateOrCreate([
            'idempotency_key' => $idempotencyKey,
        ], $attributes);

        return $automationRun;
    }
}
