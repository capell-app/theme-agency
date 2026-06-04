<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Policies;

use Capell\Admin\Policies\Concerns\ResolvesShieldPermission;
use Illuminate\Foundation\Auth\User;
use Throwable;

final class DocumentPolicy
{
    use ResolvesShieldPermission;

    private const string SUBJECT = 'Document';

    public function viewAny(User $user): bool
    {
        return $this->hasAnyPermission($user, ['view_any', 'view']);
    }

    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create');
    }

    public function update(User $user): bool
    {
        return $this->hasPermission($user, 'update');
    }

    public function delete(User $user): bool
    {
        return $this->hasPermission($user, 'delete');
    }

    public function deleteAny(User $user): bool
    {
        return $this->hasPermission($user, 'delete_any');
    }

    /**
     * @param  list<string>  $abilities
     */
    private function hasAnyPermission(User $user, array $abilities): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        foreach ($abilities as $ability) {
            if ($this->hasPermission($user, $ability)) {
                return true;
            }
        }

        return false;
    }

    private function hasPermission(User $user, string $ability): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        try {
            return $user->checkPermissionTo(self::permission($ability, self::SUBJECT));
        } catch (Throwable) {
            return false;
        }
    }

    private function isSuperAdmin(User $user): bool
    {
        try {
            return $user->hasRole(config('capell.roles.super_admin', 'super_admin'));
        } catch (Throwable) {
            return false;
        }
    }
}
