<?php

declare(strict_types=1);

namespace Capell\MediaLibrary\Actions\DashboardReports;

use Capell\Core\Support\Database\RuntimeSchemaState;
use Capell\MediaLibrary\Models\CuratorMedia;
use Capell\MediaLibrary\Support\CuratorMediaQueryFactory;
use Capell\MediaLibrary\Support\MediaUsageQueryExpressions;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildMediaHealthQueryAction
{
    use AsAction;

    /**
     * @param  array<int, array{table: string, column: string}>|null  $ownerForeignKeys
     * @return Builder<CuratorMedia>
     */
    public function handle(?array $ownerForeignKeys = null): Builder
    {
        if (! resolve(RuntimeSchemaState::class)->hasTable('curator')) {
            return $this->emptyCuratorQuery();
        }

        $staleThreshold = now()->subDays(90);
        $usageExpressions = resolve(MediaUsageQueryExpressions::class);
        $knownOwnerForeignKeys = $usageExpressions->knownOwnerForeignKeys(
            $ownerForeignKeys ?? config('capell.media_library.owner_foreign_keys', []),
        );
        $usageCountExpression = $usageExpressions->usageCountExpression($knownOwnerForeignKeys);

        return CuratorMedia::query()
            ->select('curator.*')
            ->selectRaw($usageCountExpression . ' as usage_count')
            ->where(function (Builder $nestedCuratorQuery) use ($knownOwnerForeignKeys, $staleThreshold, $usageCountExpression): void {
                $nestedCuratorQuery
                    ->whereNull('alt')
                    ->orWhere('alt', '')
                    ->orWhere('updated_at', '<', $staleThreshold);

                if ($knownOwnerForeignKeys !== []) {
                    $nestedCuratorQuery->orWhereRaw('(' . $usageCountExpression . ') = 0');
                }
            });
    }

    /**
     * @return Builder<CuratorMedia>
     */
    private function emptyCuratorQuery(): Builder
    {
        return resolve(CuratorMediaQueryFactory::class)->emptyQuery(['0 as usage_count']);
    }
}
