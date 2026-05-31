<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Actions;

use Capell\CampaignStudio\Data\LandingPageVariantData;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Models\CampaignLandingPage;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildCampaignLandingPageVariantsAction
{
    use AsAction;

    /**
     * @return Collection<int, LandingPageVariantData>
     */
    public function handle(CampaignGroup $campaignGroup): Collection
    {
        return $campaignGroup
            ->landingPages()
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->get()
            ->map(fn (CampaignLandingPage $landingPage): LandingPageVariantData => LandingPageVariantData::fromLandingPage($landingPage))
            ->values();
    }
}
