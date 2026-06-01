<?php

declare(strict_types=1);

namespace Capell\Contacts\Data;

use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\ContactActivity;
use Capell\Contacts\Models\Lead;
use Spatie\LaravelData\Data;

final class ContactSourceSyncResultData extends Data
{
    public function __construct(
        public readonly Contact $contact,
        public readonly ?Lead $lead = null,
        public readonly ?ContactActivity $activity = null,
    ) {}
}
