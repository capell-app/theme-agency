<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Support;

use Capell\Admin\Support\SiteScope;
use Capell\Core\Models\Page;
use Capell\PublishingStudio\Models\Workspace;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Throwable;

final class WorkspaceAccess
{
    /**
     * @param  Builder<Workspace>  $query
     * @return Builder<Workspace>
     */
    public static function scopeVisibleTo(Builder $query, ?Authenticatable $actor): Builder
    {
        if (! $actor instanceof Authenticatable) {
            return $query->whereRaw('1 = 0');
        }

        if (SiteScope::isGlobalActor($actor)) {
            return $query;
        }

        $siteIds = self::assignedSiteIds($actor);

        $siteIds = $siteIds->map(static fn (mixed $siteId): int => $siteId)->all();

        $pageTable = (new Page)->getTable();
        $workspaceTable = $query->getModel()->getTable();

        return $query
            ->where(function (Builder $workspaceQuery) use ($pageTable, $siteIds, $workspaceTable): void {
                $workspaceQuery
                    ->whereNotExists(function (QueryBuilder $pageQuery) use ($pageTable, $workspaceTable): void {
                        $pageQuery
                            ->selectRaw('1')
                            ->from($pageTable)
                            ->whereColumn($pageTable . '.workspace_id', $workspaceTable . '.id');
                    })
                    ->when($siteIds !== [], function (Builder $workspaceQuery) use ($pageTable, $siteIds, $workspaceTable): void {
                        $workspaceQuery
                            ->orWhere(function (Builder $workspaceQuery) use ($pageTable, $siteIds, $workspaceTable): void {
                                $workspaceQuery
                                    ->whereExists(function (QueryBuilder $pageQuery) use ($pageTable, $siteIds, $workspaceTable): void {
                                        $pageQuery
                                            ->selectRaw('1')
                                            ->from($pageTable)
                                            ->whereColumn($pageTable . '.workspace_id', $workspaceTable . '.id')
                                            ->whereIn($pageTable . '.site_id', $siteIds);
                                    })
                                    ->whereNotExists(function (QueryBuilder $pageQuery) use ($pageTable, $siteIds, $workspaceTable): void {
                                        $pageQuery
                                            ->selectRaw('1')
                                            ->from($pageTable)
                                            ->whereColumn($pageTable . '.workspace_id', $workspaceTable . '.id')
                                            ->where(function (QueryBuilder $pageQuery) use ($pageTable, $siteIds): void {
                                                $pageQuery
                                                    ->whereNull($pageTable . '.site_id')
                                                    ->orWhereNotIn($pageTable . '.site_id', $siteIds);
                                            });
                                    });
                            });
                    });
            });
    }

    public static function actorCanUseWorkspace(Authenticatable $actor, Workspace $workspace): bool
    {
        if (SiteScope::isGlobalActor($actor)) {
            return true;
        }

        if (! self::workspaceHasPageRows($workspace)) {
            return true;
        }

        return self::scopeVisibleTo(
            Workspace::query()->withoutGlobalScopes()->whereKey($workspace->getKey()),
            $actor,
        )->exists();
    }

    /** @return Collection<int, int> */
    private static function assignedSiteIds(Authenticatable $actor): Collection
    {
        try {
            $siteIds = $actor->getAssignedSiteIds();
        } catch (Throwable) {
            return collect();
        }

        if (! $siteIds instanceof Collection) {
            return collect();
        }

        return $siteIds;
    }

    private static function workspaceHasPageRows(Workspace $workspace): bool
    {
        return Page::query()
            ->withoutGlobalScopes()
            ->where('workspace_id', $workspace->getKey())
            ->exists();
    }
}
