<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Listeners;

use BackedEnum;
use Capell\AutomationStudio\Actions\DispatchAutomationTriggerAction;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Illuminate\Database\Eloquent\Model;

final class DispatchAutomationFromWorkspaceStateChanged
{
    public function __construct(
        private readonly DispatchAutomationTriggerAction $dispatchAutomationTrigger,
    ) {}

    public function handle(object $event): void
    {
        $transition = get_object_vars($event)['transition'] ?? null;

        if ($transition !== 'published') {
            return;
        }

        $workspace = $this->modelProperty($event, 'workspace');

        $this->dispatchAutomationTrigger->handle(new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::PagePublished,
            sourceType: 'publishing-studio.workspace',
            sourceId: $workspace instanceof Model ? (string) $workspace->getKey() : null,
            payload: [
                'workspace_id' => $workspace instanceof Model ? $workspace->getKey() : null,
                'transition' => $transition,
                'status' => $this->statusValue(get_object_vars($event)['newStatus'] ?? null),
            ],
        ));
    }

    private function modelProperty(object $event, string $property): ?Model
    {
        $value = get_object_vars($event)[$property] ?? null;

        return $value instanceof Model ? $value : null;
    }

    private function statusValue(mixed $status): ?string
    {
        if ($status instanceof BackedEnum) {
            return is_string($status->value) ? $status->value : null;
        }

        return is_string($status) ? $status : null;
    }
}
