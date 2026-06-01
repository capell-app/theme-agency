<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\ContactActivity;
use Capell\Contacts\Models\Lead;
use Capell\Contacts\Models\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildContactPrivacyExportAction
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
    public function handle(Contact $contact): array
    {
        $contact->loadMissing(['organisations', 'leads', 'activities']);

        return [
            'contact' => [
                'id' => $contact->getKey(),
                'site_id' => $contact->site_id,
                'source_key' => $contact->source_key,
                'source_identifier' => $contact->source_identifier,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'display_name' => $contact->display_name,
                'profile' => $contact->profile,
                'status' => $contact->status?->value,
                'first_seen_at' => $contact->first_seen_at?->toISOString(),
                'last_seen_at' => $contact->last_seen_at?->toISOString(),
            ],
            'organisations' => array_values($contact->organisations
                ->map(static function (Organisation $organisation): array {
                    $membership = $organisation->pivot;

                    return [
                        'id' => $organisation->getKey(),
                        'name' => $organisation->name,
                        'domain' => $organisation->domain,
                        'website' => $organisation->website,
                        'role' => $membership?->getAttribute('role'),
                        'is_primary' => (bool) $membership?->getAttribute('is_primary'),
                    ];
                })
                ->all()),
            'leads' => array_values($contact->leads
                ->map(static fn (Lead $lead): array => [
                    'id' => $lead->getKey(),
                    'title' => $lead->title,
                    'status' => $lead->status?->value,
                    'value_amount' => $lead->value_amount,
                    'currency' => $lead->currency,
                    'context' => $lead->context,
                    'captured_at' => $lead->captured_at?->toISOString(),
                    'qualified_at' => $lead->qualified_at?->toISOString(),
                    'closed_at' => $lead->closed_at?->toISOString(),
                ])
                ->all()),
            'activities' => array_values($contact->activities
                ->map(static fn (ContactActivity $activity): array => [
                    'id' => $activity->getKey(),
                    'type' => $activity->type?->value,
                    'summary' => $activity->summary,
                    'payload' => $activity->payload,
                    'occurred_at' => $activity->occurred_at?->toISOString(),
                ])
                ->all()),
        ];
    }
}
