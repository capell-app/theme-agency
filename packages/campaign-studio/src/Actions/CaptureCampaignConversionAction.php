<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Actions;

use Capell\CampaignStudio\Data\CampaignConversionCaptureData;
use Capell\CampaignStudio\Enums\ConversionGoalType;
use Capell\CampaignStudio\Models\CampaignConversion;
use Capell\CampaignStudio\Models\CampaignConversionGoal;
use Capell\CampaignStudio\Models\CampaignCtaWidget;
use Capell\CampaignStudio\Models\CampaignLandingPage;
use Capell\Insights\Models\InsightsVisit;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class CaptureCampaignConversionAction
{
    use AsAction;

    public function handle(CampaignConversionCaptureData $data): ?CampaignConversion
    {
        $visit = $this->resolveVisit($data->visitId);
        $landingPage = ResolveCampaignLandingPageFromUrlAction::run($data->url);

        if ($data->type === ConversionGoalType::PageView->value && $landingPage instanceof CampaignLandingPage) {
            return RecordPageViewConversionAction::run($landingPage, $visit);
        }

        if ($data->type !== ConversionGoalType::CtaClick->value || $data->goalKey === null || trim($data->goalKey) === '') {
            return null;
        }

        if (! $landingPage instanceof CampaignLandingPage) {
            return null;
        }

        $goal = CampaignConversionGoal::query()
            ->where('campaign_group_id', $landingPage->campaign_group_id)
            ->where('key', $data->goalKey)
            ->where('type', ConversionGoalType::CtaClick)
            ->where('is_active', true)
            ->first();

        if (! $goal instanceof CampaignConversionGoal) {
            return null;
        }

        return RecordCampaignConversionAction::run(
            goal: $goal,
            visit: $visit,
            landingPage: $landingPage,
            source: $visit instanceof InsightsVisit ? $this->resolveCtaWidget($data->ctaKey, $goal) : null,
        );
    }

    private function resolveVisit(?string $visitUuid): ?InsightsVisit
    {
        if ($visitUuid === null || trim($visitUuid) === '') {
            return null;
        }

        return InsightsVisit::query()
            ->where('uuid', $visitUuid)
            ->first();
    }

    private function resolveCtaWidget(?string $ctaKey, CampaignConversionGoal $goal): ?CampaignCtaWidget
    {
        if ($ctaKey === null || trim($ctaKey) === '') {
            return null;
        }

        return CampaignCtaWidget::query()
            ->where('key', $ctaKey)
            ->where('is_active', true)
            ->where(function (Builder $builder) use ($goal): void {
                $builder
                    ->whereNull('campaign_group_id')
                    ->orWhere('campaign_group_id', $goal->getAttribute('campaign_group_id'));
            })
            ->first();
    }
}
