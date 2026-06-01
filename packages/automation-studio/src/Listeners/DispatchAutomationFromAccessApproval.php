<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Listeners;

use Capell\AutomationStudio\Actions\DispatchAutomationTriggerAction;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Illuminate\Database\Eloquent\Model;

final class DispatchAutomationFromAccessApproval
{
    public function __construct(
        private readonly DispatchAutomationTriggerAction $dispatchAutomationTrigger,
    ) {}

    public function handle(object $event): void
    {
        $registration = $this->modelProperty($event, 'registration');

        $this->dispatchAutomationTrigger->handle(new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::AccessApproved,
            sourceType: 'access-gate.registration',
            sourceId: $registration instanceof Model ? (string) $registration->getKey() : null,
            payload: [
                'registration_id' => $registration instanceof Model ? $registration->getKey() : null,
                'email' => $registration instanceof Model ? $registration->getAttribute('email') : null,
                'area_id' => $registration instanceof Model ? $registration->getAttribute('area_id') : null,
            ],
        ));
    }

    private function modelProperty(object $event, string $property): ?Model
    {
        $value = get_object_vars($event)[$property] ?? null;

        return $value instanceof Model ? $value : null;
    }
}
