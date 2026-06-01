<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Data\ContactSourceRecordData;
use Capell\Contacts\Data\ContactSourceSyncResultData;
use Capell\Contacts\Enums\ContactActivityType;
use Capell\Contacts\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class SyncFormSubmissionContactAction
{
    use AsAction;

    public function handle(object $event): ?ContactSourceSyncResultData
    {
        $form = $event->form ?? null;
        $submission = $event->submission ?? null;

        if (! $form instanceof Model || ! $submission instanceof Model) {
            return null;
        }

        $siteId = $this->intValue($submission->getAttribute('site_id') ?? $form->getAttribute('site_id'));
        $submissionId = $this->intValue($submission->getKey());

        if ($siteId === null || $submissionId === null) {
            return null;
        }

        $payload = $this->payload($event, $submission);
        $formHandle = $this->stringValue($form->getAttribute('handle'));
        $formName = $this->stringValue($form->getAttribute('name'));
        $formLabel = $formName ?? $formHandle ?? __('capell-contacts::generic.form_builder.unknown_form');

        return SyncContactSourceRecordAction::run(
            new ContactSourceRecordData(
                siteId: $siteId,
                sourceKey: 'form_builder',
                sourceIdentifier: 'submission-' . $submissionId,
                email: $this->email($payload),
                phone: $this->firstString($payload, ['phone', 'telephone', 'mobile']),
                firstName: $this->firstString($payload, ['first_name', 'firstname', 'given_name']),
                lastName: $this->firstString($payload, ['last_name', 'lastname', 'family_name', 'surname']),
                displayName: $this->displayName($payload),
                profile: [
                    'form_builder' => [
                        'form_id' => $this->intValue($form->getKey()),
                        'form_handle' => $formHandle,
                        'form_name' => $formName,
                        'submission_id' => $submissionId,
                    ],
                ],
                tags: array_values(array_filter(['form_builder', $formHandle])),
                activityType: ContactActivityType::FormSubmission,
                activitySummary: __('capell-contacts::generic.form_builder.activity_summary', ['form' => $formLabel]),
                activityPayload: [
                    'form_id' => $this->intValue($form->getKey()),
                    'form_handle' => $formHandle,
                    'submission_id' => $submissionId,
                    'fields' => array_keys($payload),
                ],
                leadTitle: __('capell-contacts::generic.form_builder.lead_title', ['form' => $formLabel]),
                leadStatus: LeadStatus::New,
                occurredAt: $submission->getAttribute('submitted_at'),
            ),
            $submission,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(object $event, Model $submission): array
    {
        if (isset($event->payload) && is_array($event->payload)) {
            return $event->payload;
        }

        $payload = $submission->getAttribute('payload');
        $values = is_object($payload) && property_exists($payload, 'values') ? $payload->values : null;

        return is_array($values) ? $values : [];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function email(array $payload): ?string
    {
        foreach ($payload as $key => $value) {
            if (! str_contains(mb_strtolower($key), 'email')) {
                continue;
            }

            $email = $this->stringValue($value);

            if ($email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
                return $email;
            }
        }

        return null;
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
