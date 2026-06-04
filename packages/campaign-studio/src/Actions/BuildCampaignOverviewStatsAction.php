<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Actions;

use Capell\Admin\Support\SiteScope;
use Capell\CampaignStudio\Models\CampaignConversion;
use Capell\CampaignStudio\Models\CampaignGroup;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildCampaignOverviewStatsAction
{
    use AsAction;

    /**
     * @return array{active_campaign-studio: int, conversions: int, conversion_rate: float}
     */
    public function handle(?CarbonImmutable $startsAt = null, ?CarbonImmutable $endsAt = null): array
    {
        $activeCampaignStudio = SiteScope::applyForCurrentActor(CampaignGroup::query())->active()->count();
        $conversions = SiteScope::applyForCurrentActor(CampaignConversion::query())
            ->when($startsAt instanceof CarbonImmutable, fn (Builder $builder): Builder => $builder->where('converted_at', '>=', $startsAt))
            ->when($endsAt instanceof CarbonImmutable, fn (Builder $builder): Builder => $builder->where('converted_at', '<=', $endsAt))
            ->count();
        $visits = $this->campaignVisitCount($startsAt, $endsAt);

        return [
            'active_campaign-studio' => $activeCampaignStudio,
            'conversions' => $conversions,
            'conversion_rate' => $visits > 0 ? round(($conversions / $visits) * 100, 2) : 0.0,
        ];
    }

    private function campaignVisitCount(?CarbonImmutable $startsAt, ?CarbonImmutable $endsAt): int
    {
        $visitsTableName = config('capell-insights.tables.visits', 'insights_visits');

        if (! is_string($visitsTableName) || ! Schema::hasTable($visitsTableName)) {
            return 0;
        }

        $groupsTableName = (new CampaignGroup)->getTable();

        return SiteScope::applyForCurrentActor(CampaignGroup::query(), $groupsTableName . '.site_id')
            ->join($visitsTableName, $groupsTableName . '.utm_campaign', '=', $visitsTableName . '.utm_campaign')
            // Guard against campaign groups with a null/blank utm_campaign joining to
            // visits that also have a null/blank utm_campaign, which would otherwise
            // attribute unrelated visits and skew the headline conversion rate.
            ->whereNotNull($groupsTableName . '.utm_campaign')
            ->where($groupsTableName . '.utm_campaign', '!=', '')
            ->when($startsAt instanceof CarbonImmutable, fn (Builder $builder): Builder => $builder->where($visitsTableName . '.last_seen_at', '>=', $startsAt))
            ->when($endsAt instanceof CarbonImmutable, fn (Builder $builder): Builder => $builder->where($visitsTableName . '.last_seen_at', '<=', $endsAt))
            ->distinct()
            ->count($visitsTableName . '.id');
    }
}
