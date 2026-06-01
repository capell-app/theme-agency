<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Enums\LeadStatus;
use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\ContactActivity;
use Capell\Contacts\Models\Lead;
use Capell\Contacts\Models\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildContactsOverviewStatsAction
{
    use AsAction;

    /**
     * @return array{contacts: int, organisations: int, open_leads: int, activities: int}
     */
    public function handle(): array
    {
        return [
            'contacts' => Contact::query()->count(),
            'organisations' => Organisation::query()->count(),
            'open_leads' => Lead::query()
                ->whereIn('status', [
                    LeadStatus::New->value,
                    LeadStatus::Open->value,
                    LeadStatus::Qualified->value,
                ])
                ->count(),
            'activities' => ContactActivity::query()->count(),
        ];
    }
}
