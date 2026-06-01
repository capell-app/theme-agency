<?php

declare(strict_types=1);

namespace Capell\AccessGate\Support;

use Capell\AccessGate\Models\Area;
use Capell\Admin\Support\SiteScope;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class AccessGateSiteScope
{
    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function applyAreaScope(Builder $query): Builder
    {
        $actor = auth()->user();

        if (! $actor instanceof Authenticatable) {
            return $query->whereRaw('1 = 0');
        }

        if (SiteScope::isGlobalActor($actor)) {
            return $query;
        }

        $assignedSiteIds = $actor->getAssignedSiteIds();

        return $assignedSiteIds->isNotEmpty()
            ? $query->whereHas('area', fn (Builder $areaQuery): Builder => $areaQuery->whereIn('site_id', $assignedSiteIds))
            : $query->whereRaw('1 = 0');
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function applyAreaOptionsScope(Builder $query): Builder
    {
        return SiteScope::applyForCurrentActor($query, denyWhenMissingActor: true);
    }

    public static function actorCanUseRecord(?Authenticatable $actor, Model $record): bool
    {
        if (! $actor instanceof Authenticatable) {
            return false;
        }

        if (SiteScope::isGlobalActor($actor)) {
            return true;
        }

        $area = $record instanceof Area
            ? $record
            : self::areaForRecord($record);

        if (! $area instanceof Area || $area->site_id === null) {
            return false;
        }

        return $actor->getAssignedSiteIds()->contains((int) $area->site_id);
    }

    private static function areaForRecord(Model $record): ?Area
    {
        if ($record->relationLoaded('area')) {
            $area = $record->getRelation('area');

            return $area instanceof Area ? $area : null;
        }

        if (! method_exists($record, 'area')) {
            return null;
        }

        $record->loadMissing('area');
        $area = $record->getRelation('area');

        return $area instanceof Area ? $area : null;
    }
}
