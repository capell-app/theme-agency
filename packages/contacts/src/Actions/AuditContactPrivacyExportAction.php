<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Data\ContactActivityData;
use Capell\Contacts\Enums\ContactActivityType;
use Capell\Contacts\Models\Contact;
use Lorisleiva\Actions\Concerns\AsAction;

final class AuditContactPrivacyExportAction
{
    use AsAction;

    /**
     * @return array{
     *     contact: array<string, mixed>,
     *     organisations: list<array<string, mixed>>,
     *     leads: list<array<string, mixed>>,
     *     activities: list<array<string, mixed>>
     * }
     */
    public function handle(Contact $contact, ?string $requestedBy = null): array
    {
        $export = BuildContactPrivacyExportAction::run($contact);

        RecordContactActivityAction::run(
            contact: $contact,
            activityData: new ContactActivityData(
                type: ContactActivityType::PrivacyExport,
                summary: __('capell-contacts::generic.privacy.export_activity_summary'),
                payload: [
                    'requested_by' => $requestedBy,
                    'sections' => [
                        'organisations' => count($export['organisations']),
                        'leads' => count($export['leads']),
                        'activities' => count($export['activities']),
                    ],
                ],
            ),
        );

        return $export;
    }
}
