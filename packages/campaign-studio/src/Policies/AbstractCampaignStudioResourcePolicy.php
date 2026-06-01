<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Policies;

use Capell\Admin\Policies\Concerns\ResolvesShieldPermission;
use Capell\Admin\Support\SiteScope;
use Capell\CampaignStudio\Models\CampaignGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Throwable;

abstract class AbstractCampaignStudioResourcePolicy
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

    public function replicate(User $user, Model $record): bool
    {
        return $this->hasPermission($user, 'replicate')
            && $this->canUseRecordSite($user, $record);
    }

    public function reorder(User $user): bool
    {
        return $this->hasPermission($user, 'reorder');
    }

    protected function canUseRecordSite(User $user, Model $record): bool
    {
        $siteId = $this->recordSiteId($record);

        if (SiteScope::isGlobalActor($user)) {
            return true;
        }

        if ($siteId === null) {
            return false;
        }

        return $user->getAssignedSiteIds()->contains($siteId);
    }

    protected function recordSiteId(Model $record): ?int
    {
        $siteId = $record->getAttribute('site_id');

        if (is_numeric($siteId)) {
            return (int) $siteId;
        }

        if (! is_numeric($record->getAttribute('campaign_group_id'))) {
            return null;
        }

        $record->loadMissing('campaignGroup');
        $campaignGroup = $record->getRelation('campaignGroup');

        if (! $campaignGroup instanceof CampaignGroup) {
            return null;
        }

        $campaignGroupSiteId = $campaignGroup->getAttribute('site_id');

        return is_numeric($campaignGroupSiteId) ? (int) $campaignGroupSiteId : null;
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
