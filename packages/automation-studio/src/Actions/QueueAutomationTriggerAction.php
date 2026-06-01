<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Actions;

use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Jobs\DispatchQueuedAutomationTriggerJob;
use Illuminate\Support\Facades\Queue;
use Lorisleiva\Actions\Concerns\AsAction;

final class QueueAutomationTriggerAction
{
    use AsAction;

    public function handle(AutomationTriggerEventData $event, ?int $siteId = null, ?string $idempotencyKey = null): string
    {
        $resolvedIdempotencyKey = $idempotencyKey ?? $this->idempotencyKey($event, $siteId);

        Queue::push(new DispatchQueuedAutomationTriggerJob(
            event: $event,
            idempotencyKey: $resolvedIdempotencyKey,
            siteId: $siteId,
        ));

        return $resolvedIdempotencyKey;
    }

    private function idempotencyKey(AutomationTriggerEventData $event, ?int $siteId): string
    {
        return hash('sha256', json_encode([
            'site_id' => $siteId,
            'trigger_type' => $event->triggerType->value,
            'source_type' => $event->sourceType,
            'source_id' => $event->sourceId,
            'payload' => $event->payload,
            'occurred_at' => $event->occurredAt?->toIso8601String(),
        ], JSON_THROW_ON_ERROR));
    }
}
