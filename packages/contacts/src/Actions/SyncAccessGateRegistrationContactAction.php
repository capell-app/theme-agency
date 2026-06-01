<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Data\ContactSourceRecordData;
use Capell\Contacts\Data\ContactSourceSyncResultData;
use Capell\Contacts\Enums\ContactActivityType;
use Capell\Contacts\Enums\LeadStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class SyncAccessGateRegistrationContactAction
{
    use AsAction;

    public function handle(object $event): ?ContactSourceSyncResultData
    {
        $registration = $event->registration ?? null;

        if (! $registration instanceof Model) {
            return null;
        }

        $registrationId = $this->intValue($registration->getKey());
        $area = $this->area($registration);
        $siteId = $this->intValue($area?->getAttribute('site_id'));

        if ($registrationId === null || $siteId === null) {
            return null;
        }

        $fieldValues = $this->fieldValues($registration);
        $areaKey = $this->stringValue($area?->getAttribute('key'));
        $areaName = $this->stringValue($area?->getAttribute('name')) ?? __('capell-contacts::generic.access_gate.unknown_area');
        $occurredAt = $registration->getAttribute('approved_at') ?? $registration->getAttribute('requested_at');

        return SyncContactSourceRecordAction::run(
            new ContactSourceRecordData(
                siteId: $siteId,
                sourceKey: 'access_gate',
                sourceIdentifier: 'registration-' . $registrationId,
                email: $this->email($registration),
                phone: $this->firstString($fieldValues, ['phone', 'telephone', 'mobile']),
                firstName: $this->firstString($fieldValues, ['first_name', 'firstname', 'given_name']),
                lastName: $this->firstString($fieldValues, ['last_name', 'lastname', 'family_name', 'surname']),
                displayName: $this->displayName($fieldValues),
                profile: [
                    'access_gate' => [
                        'area_id' => $this->intValue($area?->getKey()),
                        'area_key' => $areaKey,
                        'area_name' => $areaName,
                        'registration_id' => $registrationId,
                        'requested_url' => $this->stringValue($registration->getAttribute('requested_url')),
                        'requested_host' => $this->stringValue($registration->getAttribute('requested_host')),
                    ],
                ],
                tags: array_values(array_filter(['access_gate', $areaKey])),
                activityType: ContactActivityType::AccessRegistration,
                activitySummary: __('capell-contacts::generic.access_gate.activity_summary', ['area' => $areaName]),
                activityPayload: [
                    'area_id' => $this->intValue($area?->getKey()),
                    'area_key' => $areaKey,
                    'registration_id' => $registrationId,
                    'requested_url' => $this->stringValue($registration->getAttribute('requested_url')),
                    'requested_host' => $this->stringValue($registration->getAttribute('requested_host')),
                    'fields' => array_keys($fieldValues),
                ],
                leadTitle: __('capell-contacts::generic.access_gate.lead_title', ['area' => $areaName]),
                leadStatus: LeadStatus::Open,
                occurredAt: $occurredAt instanceof CarbonInterface ? $occurredAt : null,
            ),
            $registration,
        );
    }

    private function area(Model $registration): ?Model
    {
        $loadedArea = $registration->relationLoaded('area') ? $registration->getRelation('area') : null;

        if ($loadedArea instanceof Model) {
            return $loadedArea;
        }

        if (! $registration->exists || ! method_exists($registration, 'area')) {
            return null;
        }

        $area = $registration->area()->first();

        return $area instanceof Model ? $area : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function fieldValues(Model $registration): array
    {
        $fieldValues = $registration->getAttribute('field_values');

        if (! is_array($fieldValues)) {
            return [];
        }

        return Collection::make($fieldValues)
            ->mapWithKeys(function (mixed $field, mixed $key): array {
                if (! is_string($key)) {
                    return [];
                }

                if (is_array($field) && array_key_exists('value', $field)) {
                    return [$key => $field['value']];
                }

                return [$key => $field];
            })
            ->all();
    }

    private function email(Model $registration): ?string
    {
        $email = $this->stringValue($registration->getAttribute('email_normalized'))
            ?? $this->stringValue($registration->getAttribute('email'));

        return $email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? $email : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $keys
     */
    private function firstString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $this->stringValue($payload[$key] ?? null);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function displayName(array $payload): ?string
    {
        $explicitName = $this->firstString($payload, ['name', 'full_name', 'display_name']);

        if ($explicitName !== null) {
            return $explicitName;
        }

        $name = Collection::make([
            $this->firstString($payload, ['first_name', 'firstname', 'given_name']),
            $this->firstString($payload, ['last_name', 'lastname', 'family_name', 'surname']),
        ])
            ->filter(fn (?string $value): bool => $value !== null)
            ->implode(' ');

        return $name !== '' ? $name : null;
    }

    private function stringValue(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function intValue(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
