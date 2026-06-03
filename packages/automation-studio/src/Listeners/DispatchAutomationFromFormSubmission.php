<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Listeners;

use Capell\AutomationStudio\Actions\QueueAutomationTriggerAction;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Enums\AutomationTriggerType;
use Illuminate\Database\Eloquent\Model;

final class DispatchAutomationFromFormSubmission
{
    public function __construct(
        private readonly QueueAutomationTriggerAction $queueAutomationTrigger,
    ) {}

    public function handle(object $event): void
    {
        $form = $this->modelProperty($event, 'form');
        $submission = $this->modelProperty($event, 'submission');
        $payload = $this->payload($event, $submission);
        $formKey = $form instanceof Model ? $form->getAttribute('handle') : null;

        $this->queueAutomationTrigger->handle(new AutomationTriggerEventData(
            triggerType: AutomationTriggerType::FormSubmitted,
            sourceType: 'form-builder.form',
            sourceId: is_string($formKey) && $formKey !== '' ? $formKey : ($form instanceof Model ? (string) $form->getKey() : null),
            payload: [
                ...$payload,
                'form_id' => $form instanceof Model ? $form->getKey() : null,
                'form_handle' => is_string($formKey) ? $formKey : null,
                'submission_id' => $submission instanceof Model ? $submission->getKey() : null,
            ],
        ), siteId: $this->siteId($form, $submission), deduplicationKey: $this->deduplicationKey($form, $submission));
    }

    private function modelProperty(object $event, string $property): ?Model
    {
        $value = get_object_vars($event)[$property] ?? null;

        return $value instanceof Model ? $value : null;
    }

    private function siteId(?Model $form, ?Model $submission): ?int
    {
        return $this->integerAttribute($submission, 'site_id') ?? $this->integerAttribute($form, 'site_id');
    }

    private function integerAttribute(?Model $model, string $attribute): ?int
    {
        $value = $model instanceof Model ? $model->getAttribute($attribute) : null;

        return is_int($value) || is_string($value) && is_numeric($value) ? (int) $value : null;
    }

    private function deduplicationKey(?Model $form, ?Model $submission): ?string
    {
        if ($submission instanceof Model) {
            return implode(':', ['form-builder.submission', (string) $submission->getKey()]);
        }

        if ($form instanceof Model) {
            return implode(':', ['form-builder.form', (string) $form->getKey()]);
        }

        return null;
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
