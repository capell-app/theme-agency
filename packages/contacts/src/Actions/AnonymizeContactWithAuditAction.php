<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Data\ContactActivityData;
use Capell\Contacts\Enums\ContactActivityType;
use Capell\Contacts\Models\Contact;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Contact run(Contact $contact, ?string $requestedBy = null)
 */
final class AnonymizeContactWithAuditAction
{
    use AsAction;

    public function handle(Contact $contact, ?string $requestedBy = null): Contact
    {
        $anonymizedContact = AnonymizeContactAction::run($contact);

        RecordContactActivityAction::run(
            contact: $anonymizedContact,
            activityData: new ContactActivityData(
                type: ContactActivityType::PrivacyAnonymization,
                summary: __('capell-contacts::generic.privacy.anonymization_activity_summary'),
                payload: [
                    'requested_by' => $requestedBy,
                ],
            ),
        );

        return $anonymizedContact->refresh();
    }
}
