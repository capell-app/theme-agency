<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Actions;

use Capell\Core\Support\Database\RuntimeSchemaState;
use Capell\PasswordPolicy\Data\PasswordChangeData;
use Capell\PasswordPolicy\Enums\PasswordPolicyLifecycleEvent;
use Capell\PasswordPolicy\Events\PasswordChanged;
use Capell\PasswordPolicy\Support\PasswordPolicySettingsResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsObject;

class UpdatePasswordAction
{
    use AsObject;

    public function handle(Model $user, PasswordChangeData $input): void
    {
        $settings = resolve(PasswordPolicySettingsResolver::class)->settings();

        ValidatePasswordChangeAction::run($user, $input, $settings->compromisedPasswordChecksEnabled);

        DB::transaction(function () use ($user, $input): void {
            $currentPasswordHash = (string) $user->getAttribute('password');
            RecordPasswordHistoryAction::run($user, $currentPasswordHash);

            $values = ['password' => Hash::make($input->password)];
            $schema = resolve(RuntimeSchemaState::class);

            if ($schema->hasColumn($user->getTable(), 'password_changed_at')) {
                $values['password_changed_at'] = now();
            }

            if ($schema->hasColumn($user->getTable(), 'must_change_password')) {
                $values['must_change_password'] = false;
            }

            $user->forceFill($values)->save();
        });

        NotifyPasswordPolicyLifecycleEventAction::run(
            PasswordPolicyLifecycleEvent::PasswordChanged,
            new PasswordChanged($user, 'password-policy.update-password'),
        );
    }
}
