<?php

declare(strict_types=1);

namespace Capell\PublicActions\Policies;

use Capell\Admin\Policies\Concerns\ResolvesShieldPermission;
use Capell\Admin\Support\SiteScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Throwable;

abstract class AbstractPublicActionResourcePolicy
{
    use ResolvesShieldPermission;

    abstract protected static function subject(): string;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyPermission($user, ['view_any', 'view']);
    }

    public function view(User $user, Model $record): bool
    {
        return $this->hasAnyPermission($user, ['view_any', 'view'])
            && $this->canUseRecordSite($user, $record);
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create');
    }

    public function update(User $user, Model $record): bool
    {
        return $this->hasPermission($user, 'update')
            && $this->canUseRecordSite($user, $record);
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->hasPermission($user, 'delete')
            && $this->canUseRecordSite($user, $record);
    }

    public function deleteAny(User $user): bool
    {
        return $this->hasPermission($user, 'delete_any');
    }

    public function restore(User $user, Model $record): bool
    {
        return $this->hasPermission($user, 'restore')
            && $this->canUseRecordSite($user, $record);
    }

    public function restoreAny(User $user): bool
    {
        return $this->hasPermission($user, 'restore_any');
    }

    public function forceDelete(User $user, Model $record): bool
    {
        return $this->hasPermission($user, 'force_delete')
            && $this->canUseRecordSite($user, $record);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $this->hasPermission($user, 'force_delete_any');
    }

    public function reorder(User $user): bool
    {
        return $this->hasPermission($user, 'reorder');
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
            return $user->checkPermissionTo(self::permission($ability, static::subject()));
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

    private function canUseRecordSite(User $user, Model $record): bool
    {
        if (SiteScope::isGlobalActor($user)) {
            return true;
        }

        $siteId = $this->recordSiteId($record);

        return is_int($siteId)
            && $user->getAssignedSiteIds()->contains($siteId);
    }

    private function recordSiteId(Model $record): ?int
    {
        $siteId = data_get($record, 'site_id');

        if (is_int($siteId)) {
            return $siteId;
        }

        if (is_numeric($siteId)) {
            return (int) $siteId;
        }

        $actionSiteId = data_get($record, 'action.site_id');

        if (is_int($actionSiteId) || is_numeric($actionSiteId)) {
            return (int) $actionSiteId;
        }

        $submissionSiteId = data_get($record, 'submission.site_id');

        if (is_int($submissionSiteId) || is_numeric($submissionSiteId)) {
            return (int) $submissionSiteId;
        }

        $destinationActionSiteId = data_get($record, 'destination.action.site_id');

        return is_int($destinationActionSiteId) || is_numeric($destinationActionSiteId)
            ? (int) $destinationActionSiteId
            : null;
    }
}
