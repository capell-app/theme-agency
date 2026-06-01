<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Support\Handlers;

use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Support\Handlers\Concerns\ResolvesContacts;
use Illuminate\Database\Eloquent\Model;
use Override;

final class CreateContactNoteAutomationActionHandler implements AutomationActionHandler
{
    use ResolvesContacts;

    #[Override]
    public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData
    {
        $activityDataClass = 'Capell\\Contacts\\Data\\ContactActivityData';
        $activityTypeClass = 'Capell\\Contacts\\Enums\\ContactActivityType';
        $recordActivityActionClass = 'Capell\\Contacts\\Actions\\RecordContactActivityAction';

        if (! class_exists($activityDataClass) || ! class_exists($activityTypeClass) || ! class_exists($recordActivityActionClass)) {
            return new AutomationActionResultData(
                success: false,
                message: $this->automationMessage('capell-automation-studio::generic.dispatcher.contacts_unavailable', 'Contacts is not available.'),
            );
        }

        $summary = $this->summary($event, $action);

        if ($summary === null) {
            return new AutomationActionResultData(
                success: false,
                message: $this->automationMessage('capell-automation-studio::generic.dispatcher.contact_note_required', 'A contact note summary is required.'),
            );
        }

        $resolvedContact = $this->resolveContact($event, $action);

        if (! ($resolvedContact['success'] ?? false) || ! ($resolvedContact['contact'] ?? null) instanceof Model) {
            return new AutomationActionResultData(
                success: false,
                message: (string) ($resolvedContact['message'] ?? 'Unable to resolve contact.'),
            );
        }

        $activity = $recordActivityActionClass::run(
            contact: $resolvedContact['contact'],
            activityData: new $activityDataClass(
                type: constant($activityTypeClass . '::Note'),
                summary: $summary,
                payload: $this->payload($event, $action),
            ),
        );

        return new AutomationActionResultData(
            success: true,
            context: [
                'contact_id' => $resolvedContact['contact']->getKey(),
                'activity_id' => $activity->getKey(),
            ],
        );
    }

    private function summary(AutomationTriggerEventData $event, AutomationRuleActionData $action): ?string
    {
        $summary = $action->settings['summary'] ?? $event->payload['summary'] ?? null;

        return is_string($summary) && trim($summary) !== '' ? trim($summary) : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(AutomationTriggerEventData $event, AutomationRuleActionData $action): array
    {
        $payload = $action->settings['payload'] ?? [];

        return is_array($payload)
            ? [...$event->payload, ...$payload]
            : $event->payload;
    }
}
