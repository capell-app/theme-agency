<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Data\ContactActivityData;
use Capell\Contacts\Data\ContactIdentityData;
use Capell\Contacts\Data\ContactSourceRecordData;
use Capell\Contacts\Data\ContactSourceSyncResultData;
use Capell\Contacts\Enums\ContactActivityType;
use Capell\Contacts\Enums\LeadStatus;
use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\ContactActivity;
use Capell\Contacts\Models\Lead;
use Capell\Contacts\Models\Organisation;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

final class SyncContactSourceRecordAction
{
    use AsAction;

    public function handle(
        ContactSourceRecordData $record,
        ?Model $source = null,
        ?Organisation $organisation = null,
    ): ContactSourceSyncResultData {
        $contact = FindOrCreateContactAction::run(
            new ContactIdentityData(
                siteId: $record->siteId,
                email: $record->email,
                phone: $record->phone,
                firstName: $record->firstName,
                lastName: $record->lastName,
                displayName: $record->displayName,
                sourceKey: trim($record->sourceKey),
                sourceIdentifier: $record->sourceIdentifier,
                profile: $this->profile($record),
                seenAt: $record->occurredAt,
            ),
            $source,
        );

        if ($record->tags !== null && $record->tags !== []) {
            $contact = TagContactAction::run($contact, $record->tags);
        }

        $lead = $this->createLead($record, $contact, $source, $organisation);
        $activity = $this->recordActivity($record, $contact, $source, $lead, $organisation);

        return new ContactSourceSyncResultData(
            contact: $contact->fresh() ?? $contact,
            lead: $lead,
            activity: $activity,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function profile(ContactSourceRecordData $record): array
    {
        return array_replace_recursive($record->profile ?? [], [
            'sources' => [
                trim($record->sourceKey) => [
                    'identifier' => $record->sourceIdentifier,
                    'last_seen_at' => $record->occurredAt?->toISOString(),
                ],
            ],
        ]);
    }

    private function createLead(
        ContactSourceRecordData $record,
        Contact $contact,
        ?Model $source,
        ?Organisation $organisation,
    ): ?Lead {
        if ($record->leadTitle === null || trim($record->leadTitle) === '') {
            return null;
        }

        $lead = new Lead([
            'site_id' => $record->siteId,
            'title' => $record->leadTitle,
            'status' => $record->leadStatus ?? LeadStatus::New,
            'context' => [
                'source_key' => trim($record->sourceKey),
                'source_identifier' => $record->sourceIdentifier,
                'payload' => $record->activityPayload,
            ],
            'captured_at' => $record->occurredAt ?? now(),
        ]);
        $lead->contact()->associate($contact);

        if ($organisation instanceof Organisation) {
            $lead->organisation()->associate($organisation);
        }

        if ($source instanceof Model) {
            $lead->source()->associate($source);
        }

        $lead->save();

        return $lead;
    }

    private function recordActivity(
        ContactSourceRecordData $record,
        Contact $contact,
        ?Model $source,
        ?Lead $lead,
        ?Organisation $organisation,
    ): ?ContactActivity {
        if (! $record->activityType instanceof ContactActivityType) {
            return null;
        }

        return RecordContactActivityAction::run(
            contact: $contact,
            activityData: new ContactActivityData(
                type: $record->activityType,
                summary: $record->activitySummary,
                payload: array_replace([
                    'source_key' => trim($record->sourceKey),
                    'source_identifier' => $record->sourceIdentifier,
                ], $record->activityPayload ?? []),
                occurredAt: $record->occurredAt,
            ),
            subject: $source,
            lead: $lead,
            organisation: $organisation,
        );
    }
}
