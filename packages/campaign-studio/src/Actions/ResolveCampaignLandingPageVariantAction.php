<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Actions;

use Capell\CampaignStudio\Data\AudienceTargetData;
use Capell\CampaignStudio\Data\LandingPageVariantSelectionData;
use Capell\CampaignStudio\Enums\LandingPageVariantMatchType;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Models\CampaignLandingPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lorisleiva\Actions\Concerns\AsAction;

final class ResolveCampaignLandingPageVariantAction
{
    use AsAction;

    public function handle(CampaignGroup $campaignGroup, AudienceTargetData $audienceTarget): ?LandingPageVariantSelectionData
    {
        if ($audienceTarget->utmContent !== null) {
            $landingPage = $this->matchingLandingPage(
                campaignGroup: $campaignGroup,
                column: 'utm_content',
                value: $audienceTarget->utmContent,
            );

            if ($landingPage instanceof CampaignLandingPage) {
                return LandingPageVariantSelectionData::fromLandingPage(
                    landingPage: $landingPage,
                    matchType: LandingPageVariantMatchType::UtmContent,
                    matchedValue: $audienceTarget->utmContent,
                );
            }
        }

        if ($audienceTarget->utmTerm !== null) {
            $landingPage = $this->matchingLandingPage(
                campaignGroup: $campaignGroup,
                column: 'utm_term',
                value: $audienceTarget->utmTerm,
            );

            if ($landingPage instanceof CampaignLandingPage) {
                return LandingPageVariantSelectionData::fromLandingPage(
                    landingPage: $landingPage,
                    matchType: LandingPageVariantMatchType::UtmTerm,
                    matchedValue: $audienceTarget->utmTerm,
                );
            }
        }

        $primaryLandingPage = $this->publishedLandingPages($campaignGroup)
            ->where('is_primary', true)
            ->orderBy('id')
            ->first();

        if ($primaryLandingPage instanceof CampaignLandingPage) {
            return LandingPageVariantSelectionData::fromLandingPage(
                landingPage: $primaryLandingPage,
                matchType: LandingPageVariantMatchType::Primary,
            );
        }

        $firstLandingPage = $this->publishedLandingPages($campaignGroup)
            ->orderBy('id')
            ->first();

        if ($firstLandingPage instanceof CampaignLandingPage) {
            return LandingPageVariantSelectionData::fromLandingPage(
                landingPage: $firstLandingPage,
                matchType: LandingPageVariantMatchType::FirstAvailable,
            );
        }

        return null;
    }

    private function matchingLandingPage(CampaignGroup $campaignGroup, string $column, string $value): ?CampaignLandingPage
    {
        return $this->publishedLandingPages($campaignGroup)
            ->where(function (Builder $builder) use ($column, $value): void {
                $builder->where($column, $value);
            })
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->first();
    }

    /**
     * @return HasMany<CampaignLandingPage, CampaignGroup>
     */
    private function publishedLandingPages(CampaignGroup $campaignGroup): HasMany
    {
        return $campaignGroup
            ->landingPages()
            ->whereHas('page', function (Builder $builder): void {
                $builder->publishedDate();
            });
    }
}
