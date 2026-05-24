<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Actions;

use Capell\CampaignStudio\Models\CampaignConversionGoal;
use Capell\CampaignStudio\Models\CampaignGroup;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildCampaignConversionFunnelAction
{
    use AsAction;

    /**
     * @return Collection<int, array{goal: string, conversions: int}>
     */
    public function handle(CampaignGroup $campaignGroup): Collection
    {
        return $campaignGroup
            ->conversionGoals()
            ->withCount('conversions')
            ->orderByDesc('conversions_count')
            ->get()
            ->map(fn (CampaignConversionGoal $goal): array => [
                'goal' => $goal->name,
                'conversions' => $goal->conversions_count,
            ])
            ->values();
    }
}
