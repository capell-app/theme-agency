<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Jobs;

use Capell\AutomationStudio\Actions\DispatchAutomationTriggerAction;
use Capell\AutomationStudio\Actions\LoadPersistedAutomationRulesAction;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class DispatchQueuedAutomationTriggerJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 3600;

    public function __construct(
        public readonly AutomationTriggerEventData $event,
        public readonly string $idempotencyKey,
        public readonly ?int $siteId = null,
    ) {}

    public function handle(
        LoadPersistedAutomationRulesAction $loadRules,
        DispatchAutomationTriggerAction $dispatchAutomationTrigger,
    ): void {
        $loadRules->handle($this->siteId);

        $dispatchAutomationTrigger->handle(
            event: $this->event,
            idempotencyKey: $this->idempotencyKey,
            siteId: $this->siteId,
            attemptNumber: $this->attempts(),
            maxAttempts: $this->tries,
        );
    }

    public function uniqueId(): string
    {
        return $this->idempotencyKey;
    }
}
