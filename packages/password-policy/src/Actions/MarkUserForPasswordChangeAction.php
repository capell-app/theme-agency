<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Actions;

use Capell\Core\Support\Database\RuntimeSchemaState;
use Capell\PasswordPolicy\Enums\PasswordPolicyLifecycleEvent;
use Capell\PasswordPolicy\Events\UserMarkedForPasswordChange;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsObject;

class MarkUserForPasswordChangeAction
{
    use AsObject;

    public function handle(Model $user): void
    {
        if (! resolve(RuntimeSchemaState::class)->hasColumn($user->getTable(), 'must_change_password')) {
            return;
        }

        $user->forceFill(['must_change_password' => true])->save();

        NotifyPasswordPolicyLifecycleEventAction::run(
            PasswordPolicyLifecycleEvent::UserMarkedForChange,
            new UserMarkedForPasswordChange($user),
        );
    }
}
