<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Support;

use Capell\Admin\Support\SiteScope;
use Capell\Core\Models\Site;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ShopifySiteContext
{
    public static function selectedSiteId(?Authenticatable $actor, mixed $requestedSiteId = null): ?int
    {
        if (! $actor instanceof Authenticatable) {
            return null;
        }

        if (is_numeric($requestedSiteId) && self::actorCanUseSiteId($actor, (int) $requestedSiteId)) {
            return (int) $requestedSiteId;
        }

        if (SiteScope::isGlobalActor($actor)) {
            $site = Site::query()->orderBy('id')->first();

            return $site instanceof Site ? (int) $site->getKey() : null;
        }

        /** @var Collection<int, int|string> $assignedSiteIds */
        $assignedSiteIds = $actor->getAssignedSiteIds();
        $siteId = $assignedSiteIds->map(static fn (mixed $siteId): int => (int) $siteId)->first();

        return is_int($siteId) && $siteId > 0 ? $siteId : null;
    }

    /**
     * @return array<int, string>
     */
    public static function options(?Authenticatable $actor): array
    {
        if (! $actor instanceof Authenticatable) {
            return [];
        }

        $query = Site::query()->orderBy('name');

        if (! SiteScope::isGlobalActor($actor)) {
            /** @var Collection<int, int|string> $assignedSiteIds */
            $assignedSiteIds = $actor->getAssignedSiteIds();
            $query->whereIn('id', $assignedSiteIds->map(static fn (mixed $siteId): int => (int) $siteId)->all());
        }

        /** @var array<int, string> $options */
        $options = $query->pluck('name', 'id')->map(static fn (mixed $name): string => (string) $name)->all();

        return $options;
    }

    public static function actorCanUseSiteId(Authenticatable $actor, int $siteId): bool
    {
        if ($siteId <= 0) {
            return false;
        }

        if (SiteScope::isGlobalActor($actor)) {
            return Site::query()->whereKey($siteId)->exists();
        }

        /** @var Collection<int, int|string> $assignedSiteIds */
        $assignedSiteIds = $actor->getAssignedSiteIds();

        return $assignedSiteIds->contains($siteId);
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function apply(Builder $query, ?Authenticatable $actor, ?int $siteId = null): Builder
    {
        if ($actor instanceof Authenticatable && SiteScope::isGlobalActor($actor)) {
            return $siteId === null ? $query : $query->where('site_id', $siteId);
        }

        if ($actor instanceof Authenticatable) {
            /** @var Collection<int, int|string> $assignedSiteIds */
            $assignedSiteIds = $actor->getAssignedSiteIds();
            $query->whereIn('site_id', $assignedSiteIds->map(static fn (mixed $assignedSiteId): int => (int) $assignedSiteId)->all());
        } else {
            $query->whereRaw('1 = 0');
        }

        return $siteId === null ? $query : $query->where('site_id', $siteId);
    }
}
