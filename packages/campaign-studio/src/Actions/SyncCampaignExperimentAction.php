<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Actions;

use Capell\CampaignStudio\Enums\CampaignStatus;
use Capell\CampaignStudio\Enums\ConversionGoalType;
use Capell\CampaignStudio\Models\CampaignConversionGoal;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\Experiments\Actions\CreateExperimentAction;
use Capell\Experiments\Data\ExperimentAudienceRuleData;
use Capell\Experiments\Data\ExperimentData;
use Capell\Experiments\Data\ExperimentGoalData;
use Capell\Experiments\Data\ExperimentVariantData;
use Capell\Experiments\Enums\AudienceOperator;
use Capell\Experiments\Enums\AudienceRuleType;
use Capell\Experiments\Enums\ExperimentGoalType;
use Capell\Experiments\Enums\ExperimentStatus;
use Capell\Experiments\Enums\ExperimentSubjectType;
use Capell\Experiments\Models\Experiment;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class SyncCampaignExperimentAction
{
    use AsAction;

    public function handle(CampaignGroup $campaignGroup): ?object
    {
        if (! $this->experimentsPackageIsAvailable()) {
            return null;
        }

        $campaignGroup->loadMissing(['landingPages', 'conversionGoals']);

        /** @var Experiment|null $experiment */
        $experiment = Experiment::query()
            ->where('subject_type', ExperimentSubjectType::Campaign)
            ->where('subject_class', CampaignGroup::class)
            ->where('subject_id', $campaignGroup->getKey())
            ->first();

        if (! $experiment instanceof Experiment) {
            return CreateExperimentAction::run($this->experimentData($campaignGroup));
        }

        $experiment->forceFill([
            'site_id' => $campaignGroup->site_id,
            'name' => $this->experimentName($campaignGroup),
            'key' => $this->experimentKey($campaignGroup),
            'status' => $this->experimentStatus($campaignGroup),
            'allocation_strategy' => 'sticky_weighted',
            'traffic_percentage' => 100,
            'starts_at' => $campaignGroup->starts_at,
            'ends_at' => $campaignGroup->ends_at,
            'metadata' => $this->metadata($campaignGroup),
        ])->save();

        $this->syncVariants($experiment, $campaignGroup);
        $this->syncGoals($experiment, $campaignGroup);
        $this->syncAudienceRules($experiment, $campaignGroup);

        return $experiment->refresh();
    }

    private function experimentsPackageIsAvailable(): bool
    {
        return class_exists(CreateExperimentAction::class)
            && class_exists(Experiment::class)
            && class_exists(ExperimentData::class);
    }

    private function experimentData(CampaignGroup $campaignGroup): ExperimentData
    {
        return new ExperimentData(
            name: $this->experimentName($campaignGroup),
            key: $this->experimentKey($campaignGroup),
            siteId: $campaignGroup->site_id,
            status: $this->experimentStatus($campaignGroup),
            subjectType: ExperimentSubjectType::Campaign,
            subjectClass: CampaignGroup::class,
            subjectId: (int) $campaignGroup->getKey(),
            trafficPercentage: 100,
            startsAt: $campaignGroup->starts_at,
            endsAt: $campaignGroup->ends_at,
            variants: $this->variantData($campaignGroup),
            goals: $this->goalData($campaignGroup),
            audienceRules: $this->audienceRuleData($campaignGroup),
            metadata: $this->metadata($campaignGroup),
        );
    }

    private function experimentName(CampaignGroup $campaignGroup): string
    {
        return $campaignGroup->name . ' experiment';
    }

    private function experimentKey(CampaignGroup $campaignGroup): string
    {
        return 'campaign-' . $campaignGroup->getKey() . '-' . Str::slug($campaignGroup->slug ?: $campaignGroup->name);
    }

    private function experimentStatus(CampaignGroup $campaignGroup): ExperimentStatus
    {
        return match ($campaignGroup->status) {
            CampaignStatus::Draft => ExperimentStatus::Draft,
            CampaignStatus::Scheduled => ExperimentStatus::Scheduled,
            CampaignStatus::Active => ExperimentStatus::Active,
            CampaignStatus::Paused => ExperimentStatus::Paused,
            CampaignStatus::Ended => ExperimentStatus::Ended,
        };
    }

    /**
     * @return list<ExperimentVariantData>
     */
    private function variantData(CampaignGroup $campaignGroup): array
    {
        return BuildCampaignLandingPageVariantsAction::run($campaignGroup)
            ->map(fn (object $variant, int $variantIndex): ExperimentVariantData => new ExperimentVariantData(
                name: $variant->headline ?: __('capell-campaign-studio::generic.landing_page_variant'),
                key: $variant->variantKey,
                weight: 100,
                isControl: $variant->isPrimary,
                sortOrder: $variantIndex + 1,
                payload: [
                    'campaign_group_id' => $variant->campaignGroupId,
                    'campaign_landing_page_id' => $variant->landingPageId,
                    'page_id' => $variant->pageId,
                    'utm_content' => $variant->utmContent,
                    'utm_term' => $variant->utmTerm,
                ],
            ))
            ->all();
    }

    /**
     * @return list<ExperimentGoalData>
     */
    private function goalData(CampaignGroup $campaignGroup): array
    {
        return $campaignGroup
            ->conversionGoals
            ->map(fn (CampaignConversionGoal $goal): ExperimentGoalData => new ExperimentGoalData(
                name: $goal->name,
                key: $goal->key,
                type: $this->goalType($goal),
                target: $goal->target,
                valueAmount: $goal->value_amount === null ? null : (string) $goal->value_amount,
                isPrimary: (bool) $goal->is_primary,
                isActive: (bool) $goal->is_active,
            ))
            ->values()
            ->all();
    }

    /**
     * @return list<ExperimentAudienceRuleData>
     */
    private function audienceRuleData(CampaignGroup $campaignGroup): array
    {
        if (! is_string($campaignGroup->utm_campaign) || trim($campaignGroup->utm_campaign) === '') {
            return [];
        }

        return [
            new ExperimentAudienceRuleData(
                type: AudienceRuleType::Utm,
                key: 'campaign',
                operator: AudienceOperator::Equals,
                value: $campaignGroup->utm_campaign,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function metadata(CampaignGroup $campaignGroup): array
    {
        return [
            'source_package' => 'capell-app/campaign-studio',
            'campaign_group_id' => (int) $campaignGroup->getKey(),
            'campaign_slug' => $campaignGroup->slug,
        ];
    }

    private function goalType(CampaignConversionGoal $goal): ExperimentGoalType
    {
        return match ($goal->type) {
            ConversionGoalType::PageView => ExperimentGoalType::PageView,
            ConversionGoalType::CtaClick => ExperimentGoalType::Click,
            ConversionGoalType::FormSubmission => ExperimentGoalType::FormSubmission,
            ConversionGoalType::CustomAction => ExperimentGoalType::CustomEvent,
        };
    }

    private function syncVariants(Experiment $experiment, CampaignGroup $campaignGroup): void
    {
        foreach ($this->variantData($campaignGroup) as $variantData) {
            $experiment->variants()->updateOrCreate(
                ['key' => $variantData->key],
                [
                    'name' => $variantData->name,
                    'weight' => $variantData->weight,
                    'is_control' => $variantData->isControl,
                    'is_active' => $variantData->isActive,
                    'sort_order' => $variantData->sortOrder,
                    'payload' => $variantData->payload,
                ],
            );
        }
    }

    private function syncGoals(Experiment $experiment, CampaignGroup $campaignGroup): void
    {
        foreach ($this->goalData($campaignGroup) as $goalData) {
            $experiment->goals()->updateOrCreate(
                ['key' => $goalData->key],
                [
                    'name' => $goalData->name,
                    'type' => $goalData->type,
                    'target' => $goalData->target,
                    'value_amount' => $goalData->valueAmount,
                    'is_primary' => $goalData->isPrimary,
                    'is_active' => $goalData->isActive,
                ],
            );
        }
    }

    private function syncAudienceRules(Experiment $experiment, CampaignGroup $campaignGroup): void
    {
        foreach ($this->audienceRuleData($campaignGroup) as $ruleData) {
            $experiment->audienceRules()->updateOrCreate(
                [
                    'type' => $ruleData->type,
                    'key' => $ruleData->key,
                ],
                [
                    'operator' => $ruleData->operator,
                    'value' => $ruleData->value,
                    'is_required' => $ruleData->isRequired,
                    'is_active' => $ruleData->isActive,
                    'sort_order' => $ruleData->sortOrder,
                ],
            );
        }
    }
}
