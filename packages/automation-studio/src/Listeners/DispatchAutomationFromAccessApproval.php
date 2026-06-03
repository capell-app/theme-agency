<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Listeners;

use Capell\AutomationStudio\Actions\QueueAutomationTriggerAction;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Illuminate\Database\Eloquent\Model;

final class DispatchAutomationFromAccessApproval
{
    public function __construct(
        private readonly QueueAutomationTriggerAction $queueAutomationTrigger,
    ) {}

    public function handle(object $event): void
    {
        $registration = $this->modelProperty($event, 'registration');

        $this->queueAutomationTrigger->handle(new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::AccessApproved,
            sourceType: 'access-gate.registration',
            sourceId: $registration instanceof Model ? (string) $registration->getKey() : null,
            payload: [
                'registration_id' => $registration instanceof Model ? $registration->getKey() : null,
                'email' => $registration instanceof Model ? $registration->getAttribute('email') : null,
                'area_id' => $registration instanceof Model ? $registration->getAttribute('area_id') : null,
            ],
        ), siteId: $this->integerAttribute($registration, 'site_id'), deduplicationKey: $this->deduplicationKey($registration));
    }

    private function modelProperty(object $event, string $property): ?Model
    {
        $value = get_object_vars($event)[$property] ?? null;

        return $value instanceof Model ? $value : null;
    }

    private function integerAttribute(?Model $model, string $attribute): ?int
    {
        $value = $model instanceof Model ? $model->getAttribute($attribute) : null;

        return is_int($value) || is_string($value) && is_numeric($value) ? (int) $value : null;
    }

    private function deduplicationKey(?Model $model): ?string
    {
        if (! $model instanceof Model) {
            return null;
        }

        return implode(':', ['access-gate.registration', (string) $model->getKey()]);
    }
}
