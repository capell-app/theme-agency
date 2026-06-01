<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Contacts\Actions\SyncContactSourceRecordAction;
use Capell\Contacts\Data\ContactSourceRecordData;
use Capell\Contacts\Data\ContactSourceSyncResultData;
use Capell\Contacts\Enums\ContactActivityType;
use Capell\Newsletter\Models\Subscriber;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsAction;

final class SyncNewsletterSubscriberContactAction
{
    use AsAction;

    public function handle(Subscriber $subscriber): ?ContactSourceSyncResultData
    {
        if (! $this->contactsAvailable()) {
            return null;
        }

        return SyncContactSourceRecordAction::run(
            new ContactSourceRecordData(
                siteId: (int) $subscriber->site_id,
                sourceKey: 'newsletter',
                sourceIdentifier: 'subscriber-' . $subscriber->getKey(),
                email: $subscriber->email,
                firstName: $subscriber->first_name,
                lastName: $subscriber->last_name,
                displayName: $this->displayName($subscriber),
                profile: [
                    'newsletter' => [
                        'subscriber_id' => (int) $subscriber->getKey(),
                        'status' => $subscriber->status->value,
                        'source_form_id' => $subscriber->source_form_id,
                        'source_form_handle' => $subscriber->source_form_handle,
                    ],
                ],
                tags: array_values(array_filter(['newsletter', $subscriber->source_form_handle])),
                activityType: ContactActivityType::NewsletterSubscription,
                activitySummary: __('capell-newsletter::generic.contacts.activity_summary', [
                    'status' => $subscriber->status->getLabel(),
                ]),
                activityPayload: [
                    'subscriber_id' => (int) $subscriber->getKey(),
                    'status' => $subscriber->status->value,
                    'source_form_id' => $subscriber->source_form_id,
                    'source_form_handle' => $subscriber->source_form_handle,
                ],
                occurredAt: $subscriber->updated_at,
            ),
            $subscriber,
        );
    }

    private function contactsAvailable(): bool
    {
        return class_exists(SyncContactSourceRecordAction::class)
            && class_exists(ContactSourceRecordData::class)
            && Schema::hasTable('contacts')
            && Schema::hasTable('contact_activities');
    }

    private function displayName(Subscriber $subscriber): ?string
    {
        $name = trim(implode(' ', array_filter([
            $subscriber->first_name,
            $subscriber->last_name,
        ])));

        return $name !== '' ? $name : null;
    }
}
