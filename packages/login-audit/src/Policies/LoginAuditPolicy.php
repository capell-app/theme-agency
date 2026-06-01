<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Policies;

use Capell\Admin\Policies\Concerns\ResolvesShieldPermission;
use Capell\Admin\Support\SiteScope;
use Illuminate\Foundation\Auth\User;
use Throwable;

final class LoginAuditPolicy
{
    use ResolvesShieldPermission;

    private const string SUBJECT = 'LoginAudit';

    public function viewAny(User $user): bool
    {
        return $this->hasAnyPermission($user, ['view_any', 'view']);
    }

    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    private function hasAnyPermission(User $user, array $abilities): bool
    {
        if (SiteScope::isGlobalActor($user)) {
            return true;
        }

        foreach ($abilities as $ability) {
            try {
                if ($user->checkPermissionTo(self::permission($ability, self::SUBJECT))) {
                    return true;
                }
            } catch (Throwable) {
                continue;
            }
        }

        return false;
    }
}
