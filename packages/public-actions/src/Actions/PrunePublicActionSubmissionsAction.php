<?php

declare(strict_types=1);

namespace Capell\PublicActions\Actions;

use Capell\PublicActions\Data\PublicActionRetentionPruneResultData;
use Capell\PublicActions\Models\PublicActionDispatchAttempt;
use Capell\PublicActions\Models\PublicActionSubmission;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PublicActionRetentionPruneResultData run(int $retentionDays, bool $dryRun = false)
 */
final class PrunePublicActionSubmissionsAction
{
    use AsAction;

    public function handle(int $retentionDays, bool $dryRun = false): PublicActionRetentionPruneResultData
    {
        $cutoff = CarbonImmutable::now()->subDays($retentionDays);
        $submissions = $this->submissionsOlderThan($cutoff);
        $matchedSubmissions = (clone $submissions)->count();
        $matchedDispatchAttempts = PublicActionDispatchAttempt::query()
            ->whereHas('submission', function (Builder $builder) use ($cutoff): void {
                $builder->where('submitted_at', '<', $cutoff);
            })
            ->count();
        $deletedSubmissions = $dryRun ? 0 : $this->deleteMatchedSubmissions($submissions);

        return new PublicActionRetentionPruneResultData(
            retentionDays: $retentionDays,
            cutoff: $cutoff,
            matchedSubmissions: $matchedSubmissions,
            matchedDispatchAttempts: $matchedDispatchAttempts,
            deletedSubmissions: $deletedSubmissions,
            dryRun: $dryRun,
        );
    }

    /**
     * @return Builder<PublicActionSubmission>
     */
    private function submissionsOlderThan(CarbonImmutable $cutoff): Builder
    {
        return PublicActionSubmission::query()
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '<', $cutoff);
    }

    /**
     * @param  Builder<PublicActionSubmission>  $submissions
     */
    private function deleteMatchedSubmissions(Builder $submissions): int
    {
        $deleted = (clone $submissions)->delete();

        return is_numeric($deleted) ? (int) $deleted : 0;
    }
}
