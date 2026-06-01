<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Data\ContactActivityData;
use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\ContactActivity;
use Capell\Contacts\Models\Lead;
use Capell\Contacts\Models\Organisation;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordContactActivityAction
{
    use AsAction;

    public function handle(
        Contact $contact,
        ContactActivityData $activityData,
        ?Model $subject = null,
        ?Lead $lead = null,
        ?Organisation $organisation = null,
    ): ContactActivity {
        $activity = new ContactActivity([
            'site_id' => $contact->site_id,
            'type' => $activityData->type,
            'summary' => $activityData->summary,
            'payload' => $activityData->payload,
            'occurred_at' => $activityData->occurredAt?->toImmutable() ?? CarbonImmutable::now(),
        ]);

        $activity->contact()->associate($contact);

        if ($lead instanceof Lead) {
            $activity->lead()->associate($lead);
        }

        if ($organisation instanceof Organisation) {
            $activity->organisation()->associate($organisation);
        }

        if ($subject instanceof Model) {
            $activity->subject()->associate($subject);
        }

        $activity->save();

        return $activity;
    }
}
