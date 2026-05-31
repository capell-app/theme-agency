<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Support;

use Capell\Core\Models\Page;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Validation\ValidationException;

final class AgentBridgePageAccess
{
    public static function authorizeSite(?Authenticatable $user, int $siteId, string $field = 'site_id'): void
    {
        if (! $user instanceof Authenticatable) {
            throw ValidationException::withMessages([
                $field => __('capell-agent-bridge::admin.capability_site_requires_user'),
            ]);
        }

        if (self::isGlobalAdmin($user)) {
            return;
        }

        $assignedSiteIds = $user->getAssignedSiteIds();

        if (! is_iterable($assignedSiteIds)) {
            throw ValidationException::withMessages([
                $field => __('capell-agent-bridge::admin.capability_site_forbidden'),
            ]);
        }

        foreach ($assignedSiteIds as $assignedSiteId) {
            if ((int) $assignedSiteId === $siteId) {
                return;
            }
        }

        throw ValidationException::withMessages([
            $field => __('capell-agent-bridge::admin.capability_site_forbidden'),
        ]);
    }

    /**
     * @param  list<string>  $with
     */
    public static function authorizedPage(?Authenticatable $user, int $pageId, array $with = []): Page
    {
        $query = Page::query()->whereKey($pageId);

        if ($with !== []) {
            $query->with($with);
        }

        /** @var Page $page */
        $page = $query->firstOrFail();
        $siteId = $page->getAttribute('site_id');

        if ($siteId === null) {
            self::authorizeGlobalPage($user);

            return $page;
        }

        self::authorizeSite($user, (int) $siteId, 'page_id');

        return $page;
    }

    private static function authorizeGlobalPage(?Authenticatable $user): void
    {
        if ($user instanceof Authenticatable && self::isGlobalAdmin($user)) {
            return;
        }

        throw ValidationException::withMessages([
            'page_id' => __('capell-agent-bridge::admin.capability_site_forbidden'),
        ]);
    }

    private static function isGlobalAdmin(Authenticatable $user): bool
    {
        if (is_callable([$user, 'isGlobalAdmin']) && $user->isGlobalAdmin() === true) {
            return true;
        }

        return is_callable([$user, 'hasRole'])
            && $user->hasRole(config('capell.roles.super_admin', 'super_admin')) === true;
    }
}
