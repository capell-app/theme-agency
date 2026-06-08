<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Actions;

use Capell\Core\Support\Database\RuntimeSchemaState;
use Capell\PasswordPolicy\Support\PasswordPolicySettingsResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsObject;

class RecordPasswordHistoryAction
{
    use AsObject;

    public function handle(Model $user, string $passwordHash): void
    {
        $settings = resolve(PasswordPolicySettingsResolver::class)->settings();

        if (! $settings->passwordHistoryEnabled || ! resolve(RuntimeSchemaState::class)->hasTable('password_policy_password_histories')) {
            return;
        }

        DB::table('password_policy_password_histories')->insert([
            'user_id' => $user->getKey(),
            'password' => $passwordHash,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->pruneHistory($user, max(1, $settings->passwordHistoryCount));
    }

    private function pruneHistory(Model $user, int $keepCount): void
    {
        $idsToPrune = DB::table('password_policy_password_histories')
            ->where('user_id', $user->getKey())->latest()
            ->orderByDesc('id')
            ->skip($keepCount)
            ->pluck('id');

        if ($idsToPrune->isEmpty()) {
            return;
        }

        DB::table('password_policy_password_histories')
            ->whereIn('id', $idsToPrune->all())
            ->delete();
    }
}
