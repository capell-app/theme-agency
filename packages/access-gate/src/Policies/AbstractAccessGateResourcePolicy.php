<?php

declare(strict_types=1);

namespace Capell\AccessGate\Policies;

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\Support\Utils;
use Capell\AccessGate\Support\AccessGateSiteScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Str;
use Throwable;

abstract class AbstractAccessGateResourcePolicy
{
    abstract protected static function subject(): string;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyPermission($user, ['view_any', 'view']);
    }

    public function view(User $user, Model $record): bool
    {
        return $this->hasAnyPermission($user, ['view_any', 'view'])
            && AccessGateSiteScope::actorCanUseRecord($user, $record);
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create');
    }

    public function update(User $user, Model $record): bool
    {
        return $this->hasPermission($user, 'update')
            && AccessGateSiteScope::actorCanUseRecord($user, $record);
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->hasPermission($user, 'delete')
            && AccessGateSiteScope::actorCanUseRecord($user, $record);
    }

    public function deleteAny(User $user): bool
    {
        return $this->hasPermission($user, 'delete_any');
    }

    public function restore(User $user, Model $record): bool
    {
        return $this->hasPermission($user, 'restore')
            && AccessGateSiteScope::actorCanUseRecord($user, $record);
    }

    public function restoreAny(User $user): bool
    {
        return $this->hasPermission($user, 'restore_any');
    }

    public function forceDelete(User $user, Model $record): bool
    {
        return $this->hasPermission($user, 'force_delete')
            && AccessGateSiteScope::actorCanUseRecord($user, $record);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $this->hasPermission($user, 'force_delete_any');
    }

    public function reorder(User $user): bool
    {
        return $this->hasPermission($user, 'reorder');
    }

    private function permission(string $ability, string $subject): string
    {
        $utilsClass = Utils::class;
        $shieldClass = FilamentShield::class;

        if (class_exists($utilsClass) && class_exists($shieldClass)) {
            try {
                $config = call_user_func([$utilsClass, 'getConfig']);
                $separator = data_get($config, 'permissions.separator');
                $case = data_get($config, 'permissions.case');

                if (! is_string($separator) || ! is_string($case)) {
                    return Str::studly($ability) . ':' . $subject;
                }

                $permission = call_user_func_array([$shieldClass, 'defaultPermissionKeyBuilder'], [
                    'affix' => $ability,
                    'separator' => $separator,
                    'subject' => $subject,
                    'case' => $case,
                ]);

                if (is_string($permission) && $permission !== '') {
                    return $permission;
                }
            } catch (Throwable) {
                return Str::studly($ability) . ':' . $subject;
            }
        }

        return Str::studly($ability) . ':' . $subject;
    }

    /**
     * @param  list<string>  $abilities
     */
    private function hasAnyPermission(User $user, array $abilities): bool
    {
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
            return $user->checkPermissionTo($this->permission($ability, static::subject()));
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
