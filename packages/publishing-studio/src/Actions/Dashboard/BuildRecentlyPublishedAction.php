<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Actions\Dashboard;

use Capell\Admin\Data\Dashboard\RecentlyPublishedData;
use Capell\Admin\Data\Dashboard\RecentlyPublishedItemData;
use Capell\Admin\Filament\Resources\Pages\PageResource;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\PublishingStudio\Enums\WorkspaceStatusEnum;
use Capell\PublishingStudio\Models\Workspace;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\LaravelData\DataCollection;

final class BuildRecentlyPublishedAction
{
    use AsAction;

    public function handle(int $limit = 10, ?Site $site = null): RecentlyPublishedData
    {
        $workspaceTable = (new Workspace)->getTable();
        $pageTable = (new Page)->getTable();

        $pageQuery = Page::query()
            ->withoutGlobalScopes()
            ->with('site')
            ->select($pageTable . '.*')
            ->selectRaw($workspaceTable . '.published_at as workspace_published_at')
            ->join($workspaceTable, $pageTable . '.workspace_id', '=', $workspaceTable . '.id')
            ->where($workspaceTable . '.status', WorkspaceStatusEnum::Published->value)
            ->whereNotNull($workspaceTable . '.published_at')
            ->where($workspaceTable . '.published_at', '<=', now())
            ->latest($workspaceTable . '.published_at')
            ->latest($pageTable . '.updated_at')
            ->limit($limit);

        if ($site instanceof Site) {
            $pageQuery->where($pageTable . '.site_id', $site->id);
        }

        $pages = $pageQuery->get();

        $items = $pages
            ->map(function (Page $page): RecentlyPublishedItemData {
                $siteName = $page->relationLoaded('site') && $page->site instanceof Site
                    ? $page->site->name
                    : '';

                return new RecentlyPublishedItemData(
                    pageId: $page->id,
                    title: $page->name,
                    siteName: $siteName,
                    publishedAt: $page->getAttribute('workspace_published_at') !== null
                        ? (string) $page->getAttribute('workspace_published_at')
                        : null,
                    editUrl: PageResource::getUrl('edit', ['record' => $page]),
                );
            })
            ->values();

        return new RecentlyPublishedData(
            items: RecentlyPublishedItemData::collect($items->all(), DataCollection::class),
        );
    }
}
