<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Enums\ContactStatus;
use Capell\Contacts\Models\Contact;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

final class AnonymizeContactAction
{
    use AsAction;

    public function handle(Contact $contact): Contact
    {
        return DB::transaction(function () use ($contact): Contact {
            $contact->forceFill([
                'source_type' => null,
                'source_id' => null,
                'source_key' => null,
                'source_identifier' => null,
                'email' => null,
                'phone' => null,
                'first_name' => null,
                'last_name' => null,
                'display_name' => null,
                'profile' => null,
                'status' => ContactStatus::Archived,
            ])->save();

            $contact->leads()->update([
                'source_type' => null,
                'source_id' => null,
                'title' => null,
                'context' => null,
            ]);

            $contact->activities()->update([
                'subject_type' => null,
                'subject_id' => null,
                'summary' => null,
                'payload' => null,
            ]);

            return $contact->refresh();
        });
    }
}
