<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Actions\Concerns\CoercesContactSourceValues;
use Capell\Contacts\Data\ContactSourceRecordData;
use Capell\Contacts\Data\ContactSourceSyncResultData;
use Capell\Contacts\Enums\ContactActivityType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

final class SyncEventRegistrationContactAction
{
    use AsAction;
    use CoercesContactSourceValues;

    public function handle(object $event): ?ContactSourceSyncResultData
    {
        $registration = $event->registration ?? null;

        if (! $registration instanceof Model) {
            return null;
        }

        $registrationId = $this->intValue($registration->getKey());
        $occurrence = $this->relatedModel($registration, 'occurrence');
        $eventModel = $occurrence instanceof Model ? $this->relatedModel($occurrence, 'event') : null;
        $siteId = $this->intValue($eventModel?->getAttribute('site_id'));

        if ($registrationId === null || $siteId === null) {
            return null;
        }

        $eventName = $this->stringValue($eventModel?->getAttribute('name')) ?? __('capell-contacts::generic.events.unknown_event');
        $status = $this->stringValue($registration->getAttribute('status')?->value ?? $registration->getAttribute('status'));
        $payload = $this->payload($registration);
        $registeredAt = $registration->getAttribute('registered_at');

        return SyncContactSourceRecordAction::run(
            new ContactSourceRecordData(
                siteId: $siteId,
                sourceKey: 'events',
                sourceIdentifier: 'registration-' . $registrationId,
                email: $this->stringValue($registration->getAttribute('email')),
                phone: $this->stringValue($registration->getAttribute('phone')),
                displayName: $this->stringValue($registration->getAttribute('name')),
                profile: [
                    'events' => [
                        'event_id' => $this->intValue($eventModel?->getKey()),
                        'event_name' => $eventName,
                        'occurrence_id' => $this->intValue($occurrence?->getKey()),
                        'registration_id' => $registrationId,
                        'quantity' => $this->intValue($registration->getAttribute('quantity')),
                        'status' => $status,
                    ],
                ],
                tags: array_values(array_filter(['events', $this->stringValue($eventModel?->getAttribute('uuid'))])),
                activityType: ContactActivityType::EventRegistration,
                activitySummary: __('capell-contacts::generic.events.activity_summary', ['event' => $eventName]),
                activityPayload: [
                    'event_id' => $this->intValue($eventModel?->getKey()),
                    'event_name' => $eventName,
                    'occurrence_id' => $this->intValue($occurrence?->getKey()),
                    'registration_id' => $registrationId,
                    'quantity' => $this->intValue($registration->getAttribute('quantity')),
                    'status' => $status,
                    'payload_fields' => array_keys($payload),
                ],
                occurredAt: $registeredAt instanceof CarbonInterface ? $registeredAt : null,
            ),
            $registration,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Model $registration): array
    {
        $payload = $registration->getAttribute('payload');

        return is_array($payload) ? Arr::where($payload, fn (mixed $value, string|int $key): bool => is_string($key)) : [];
    }
}
