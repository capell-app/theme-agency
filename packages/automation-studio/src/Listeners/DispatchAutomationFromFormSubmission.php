<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Listeners;

use Capell\AutomationStudio\Actions\DispatchAutomationTriggerAction;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Illuminate\Database\Eloquent\Model;

final class DispatchAutomationFromFormSubmission
{
    public function __construct(
        private readonly DispatchAutomationTriggerAction $dispatchAutomationTrigger,
    ) {}

    public function handle(object $event): void
    {
        $form = $this->modelProperty($event, 'form');
        $submission = $this->modelProperty($event, 'submission');
        $payload = $this->payload($event, $submission);
        $formKey = $form instanceof Model ? $form->getAttribute('handle') : null;

        $this->dispatchAutomationTrigger->handle(new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.form',
            sourceId: is_string($formKey) && $formKey !== '' ? $formKey : ($form instanceof Model ? (string) $form->getKey() : null),
            payload: [
                ...$payload,
                'form_id' => $form instanceof Model ? $form->getKey() : null,
                'form_handle' => is_string($formKey) ? $formKey : null,
                'submission_id' => $submission instanceof Model ? $submission->getKey() : null,
            ],
        ));
    }

    private function modelProperty(object $event, string $property): ?Model
    {
        $value = get_object_vars($event)[$property] ?? null;

        return $value instanceof Model ? $value : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(object $event, ?Model $submission): array
    {
        $payload = get_object_vars($event)['payload'] ?? null;

        if (is_array($payload)) {
            return $payload;
        }

        $storedPayload = $submission instanceof Model ? $submission->getAttribute('payload') : null;

        if (is_array($storedPayload)) {
            return $storedPayload;
        }

        if (is_object($storedPayload) && method_exists($storedPayload, 'toArray')) {
            $arrayPayload = $storedPayload->toArray();

            return is_array($arrayPayload) ? $arrayPayload : [];
        }

        return [];
    }
}
