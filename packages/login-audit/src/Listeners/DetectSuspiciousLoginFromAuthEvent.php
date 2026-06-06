<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Listeners;

use Capell\LoginAudit\Actions\DetectSuspiciousLoginAction;
use Capell\LoginAudit\Models\LoginAudit;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

final class DetectSuspiciousLoginFromAuthEvent
{
    public function handle(Failed|Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof Authenticatable || ! $user instanceof Model) {
            return;
        }

        $loginAudit = LoginAudit::query()
            ->where('authenticatable_type', $user->getMorphClass())
            ->where('authenticatable_id', $user->getKey())
            ->where('login_successful', $event instanceof Login)
            ->where('login_at', '>=', now()->subMinutes(5))
            ->latest('login_at')
            ->latest('id')
            ->first();

        if (! $loginAudit instanceof LoginAudit) {
            return;
        }

        DetectSuspiciousLoginAction::run($loginAudit);
    }
}
