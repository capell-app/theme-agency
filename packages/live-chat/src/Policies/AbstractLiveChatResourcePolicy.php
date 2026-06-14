<?php

declare(strict_types=1);

namespace Capell\LiveChat\Policies;

use Capell\Admin\Policies\Concerns\ResolvesShieldPermission;
use Capell\Admin\Support\SiteScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Str;
use Throwable;

abstract class AbstractLiveChatResourcePolicy
{
    use ResolvesShieldPermission {
        permission as private shieldPermission;
    }

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

    public function update(User $user, ?Model $record = null): bool
    {
        if (! $this->hasPermission($user, 'update')) {
            return false;
        }

        return ! $record instanceof Model || $this->canUseRecordSite($user, $record);
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

    protected function canUseRecordSite(User $user, Model $record): bool
    {
        if (SiteScope::isGlobalActor($user)) {
            return true;
        }

        $siteId = $this->recordSiteId($record);

        return is_int($siteId)
            && method_exists($user, 'getAssignedSiteIds')
            && $user->getAssignedSiteIds()->contains($siteId);
    }

    protected function recordSiteId(Model $record): ?int
    {
        foreach ([
            $record->getAttribute('site_id'),
            data_get($record, 'installation.site_id'),
            data_get($record, 'conversation.site_id'),
            data_get($record, 'message.conversation.site_id'),
            data_get($record, 'source.site_id'),
        ] as $siteId) {
            if (is_int($siteId) || is_numeric($siteId)) {
                return (int) $siteId;
            }
        }

        return null;
    }

    private static function permission(string $ability, string $subject): string
    {
        try {
            return self::shieldPermission($ability, $subject);
        } catch (Throwable) {
            return Str::studly($ability) . ':' . $subject;
        }
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
        if (SiteScope::isGlobalActor($user)) {
            return true;
        }

        try {
            return $user->checkPermissionTo(self::permission($ability, static::subject()));
        } catch (Throwable) {
            return false;
        }
    }
}
