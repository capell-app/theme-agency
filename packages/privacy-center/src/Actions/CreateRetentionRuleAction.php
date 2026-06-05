<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Actions;

use Capell\PrivacyCenter\Data\RetentionRuleData;
use Capell\PrivacyCenter\Models\RetentionRule;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static RetentionRule run(RetentionRuleData $ruleData)
 */
final class CreateRetentionRuleAction
{
    use AsAction;

    public function handle(RetentionRuleData $ruleData): RetentionRule
    {
        return RetentionRule::query()->updateOrCreate([
            'site_id' => $ruleData->siteId,
            'data_domain' => $ruleData->dataDomain,
            'record_type' => $ruleData->recordType,
        ], [
            'retention_days' => $ruleData->retentionDays,
            'action' => $ruleData->action,
            'legal_basis' => $ruleData->legalBasis,
            'is_active' => $ruleData->isActive,
            'metadata' => $ruleData->metadata === [] ? null : $ruleData->metadata,
        ]);
    }
}
