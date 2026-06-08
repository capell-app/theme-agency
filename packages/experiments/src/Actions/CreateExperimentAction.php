<?php

declare(strict_types=1);

namespace Capell\Experiments\Actions;

use Capell\Experiments\Data\ExperimentAudienceRuleData;
use Capell\Experiments\Data\ExperimentData;
use Capell\Experiments\Data\ExperimentGoalData;
use Capell\Experiments\Data\ExperimentVariantData;
use Capell\Experiments\Models\Experiment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Experiment run(ExperimentData $data)
 */
final class CreateExperimentAction
{
    use AsAction;

    public function handle(ExperimentData $data): Experiment
    {
        return DB::transaction(function () use ($data): Experiment {
            $experiment = Experiment::query()->create([
                'site_id' => $data->siteId,
                'name' => $data->name,
                'key' => $data->key ?? Str::slug($data->name),
                'status' => $data->status,
                'subject_type' => $data->subjectType,
                'subject_class' => $data->subjectClass,
                'subject_id' => $data->subjectId,
                'allocation_strategy' => $data->allocationStrategy,
                'traffic_percentage' => max(0, min(100, $data->trafficPercentage)),
                'starts_at' => $data->startsAt,
                'ends_at' => $data->endsAt,
                'metadata' => $data->metadata,
            ]);

            foreach ($data->variants as $variantIndex => $variantData) {
                $variant = $variantData instanceof ExperimentVariantData
                    ? $variantData
                    : ExperimentVariantData::from($variantData);

                $experiment->variants()->create([
                    'name' => $variant->name,
                    'key' => $variant->key ?? Str::slug($variant->name),
                    'weight' => max(0, $variant->weight),
                    'is_control' => $variant->isControl,
                    'is_active' => $variant->isActive,
                    'sort_order' => $variant->sortOrder > 0 ? $variant->sortOrder : $variantIndex + 1,
                    'payload' => $variant->payload,
                ]);
            }

            foreach ($data->goals as $goalData) {
                $goal = $goalData instanceof ExperimentGoalData
                    ? $goalData
                    : ExperimentGoalData::from($goalData);

                $experiment->goals()->create([
                    'name' => $goal->name,
                    'key' => $goal->key ?? Str::slug($goal->name),
                    'type' => $goal->type,
                    'target' => $goal->target,
                    'value_amount' => $goal->valueAmount,
                    'is_primary' => $goal->isPrimary,
                    'is_active' => $goal->isActive,
                ]);
            }

            foreach ($data->audienceRules as $ruleIndex => $ruleData) {
                $rule = $ruleData instanceof ExperimentAudienceRuleData
                    ? $ruleData
                    : ExperimentAudienceRuleData::from($ruleData);

                $experiment->audienceRules()->create([
                    'type' => $rule->type,
                    'key' => $rule->key,
                    'operator' => $rule->operator,
                    'value' => $rule->value,
                    'is_required' => $rule->isRequired,
                    'is_active' => $rule->isActive,
                    'sort_order' => $rule->sortOrder > 0 ? $rule->sortOrder : $ruleIndex + 1,
                ]);
            }

            return $experiment->refresh();
        });
    }
}
