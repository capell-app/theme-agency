<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Actions;

use Capell\Core\Support\Database\RuntimeSchemaState;
use Capell\PasswordPolicy\Support\PasswordPolicySettingsResolver;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsObject;

final class PrunePasswordHistoryAction
{
    use AsObject;

    public function handle(?int $keepCount = null, ?int $userId = null, bool $dryRun = false): int
    {
        if (! resolve(RuntimeSchemaState::class)->hasTable('password_policy_password_histories')) {
            return 0;
        }

        $keepCount ??= resolve(PasswordPolicySettingsResolver::class)->settings()->passwordHistoryCount;
        $keepCount = max(1, $keepCount);
        $deleted = 0;

        DB::table('password_policy_password_histories')
            ->select('user_id')
            ->when($userId !== null, static fn (Builder $query): Builder => $query->where('user_id', $userId))
            ->distinct()
            ->orderBy('user_id')
            ->pluck('user_id')
            ->each(function (mixed $historyUserId) use ($keepCount, $dryRun, &$deleted): void {
                if (! is_numeric($historyUserId)) {
                    return;
                }

                $idsToPrune = DB::table('password_policy_password_histories')
                    ->where('user_id', (int) $historyUserId)
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->skip($keepCount)
                    ->pluck('id');

                $deleted += $idsToPrune->count();

                if ($dryRun || $idsToPrune->isEmpty()) {
                    return;
                }

                DB::table('password_policy_password_histories')
                    ->whereIn('id', $idsToPrune->all())
                    ->delete();
            });

        return $deleted;
    }
}
