<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Policies;

use Capell\Admin\Policies\Concerns\ResolvesShieldPermission;
use Capell\Admin\Support\SiteScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Throwable;

abstract class AbstractKnowledgeBaseResourcePolicy
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

    private function canUseRecordSite(User $user, Model $record): bool
    {
        $siteId = $this->recordSiteId($record);

        if ($siteId === null || SiteScope::isGlobalActor($user)) {
            return true;
        }

        if (! method_exists($user, 'getAssignedSiteIds')) {
            return false;
        }

        return $user->getAssignedSiteIds()->contains($siteId);
    }

    private function recordSiteId(Model $record): ?int
    {
        $siteId = $record->getAttribute('site_id');

        return is_numeric($siteId) ? (int) $siteId : null;
    }
}
