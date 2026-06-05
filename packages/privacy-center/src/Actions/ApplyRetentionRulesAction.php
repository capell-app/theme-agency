<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Actions;

use Capell\PrivacyCenter\Data\RetentionExecutionResultData;
use Capell\PrivacyCenter\Models\RetentionRule;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Collection<int, RetentionExecutionResultData> run(?CarbonInterface $now = null)
 */
final class ApplyRetentionRulesAction
{
    use AsAction;

    /**
     * @return Collection<int, RetentionExecutionResultData>
     */
    public function handle(?CarbonInterface $now = null): Collection
    {
        return RetentionRule::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(fn (RetentionRule $rule): RetentionExecutionResultData => ApplyRetentionRuleAction::run($rule, $now));
    }
}
