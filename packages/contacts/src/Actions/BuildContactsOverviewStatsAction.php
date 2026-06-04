<?php

declare(strict_types=1);

namespace Capell\Contacts\Actions;

use Capell\Contacts\Enums\LeadStatus;
use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\ContactActivity;
use Capell\Contacts\Models\Lead;
use Capell\Contacts\Models\Organisation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildContactsOverviewStatsAction
{
    use AsAction;

    /**
     * @return array{contacts: int, organisations: int, open_leads: int, activities: int}
     */
    public function handle(?int $siteId = null): array
    {
        return [
            'contacts' => $this->forSite(Contact::query(), $siteId)->count(),
            'organisations' => $this->forSite(Organisation::query(), $siteId)->count(),
            'open_leads' => $this->forSite(Lead::query(), $siteId)
                ->whereIn('status', [
                    LeadStatus::New->value,
                    LeadStatus::Open->value,
                    LeadStatus::Qualified->value,
                ])
                ->count(),
            'activities' => $this->forSite(ContactActivity::query(), $siteId)->count(),
        ];
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function forSite(Builder $query, ?int $siteId): Builder
    {
        if ($siteId === null) {
            return $query;
        }

        return $query->where('site_id', $siteId);
    }
}
