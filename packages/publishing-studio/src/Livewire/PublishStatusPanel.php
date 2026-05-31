<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Livewire;

use Capell\Admin\Contracts\Extenders\PublishPanelExtender;
use Capell\Admin\Data\PagePublishStateData;
use Capell\Core\Models\Page;
use Capell\PublishingStudio\Actions\GenerateWorkspacePreviewUrlAction;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\WorkspaceContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PublishStatusPanel extends Component
{
    #[Locked]
    public int $pageId;

    #[Computed]
    public function state(): PagePublishStateData
    {
        /** @var class-string<Page> $model */
        $model = Page::class;

        /** @var Page|null $page */
        $page = $model::query()->withoutGlobalScopes()->find($this->pageId);

        if ($page === null) {
            return new PagePublishStateData(
                pageId: $this->pageId,
                isDraft: true,
                publishedAt: null,
                previewUrl: null,
            );
        }

        $pageWorkspace = $this->workspaceForPage($page);

        if ($pageWorkspace instanceof Workspace) {
            Gate::authorize('view', $pageWorkspace);
        }

        $activeWorkspace = WorkspaceContext::current();
        $workspace = $activeWorkspace instanceof Workspace ? $activeWorkspace : null;

        $previewUrl = null;
        if ($workspace instanceof Workspace) {
            Gate::authorize('preview', $workspace);

            $pageUrl = $page->pageUrl;
            $path = $pageUrl !== null ? $pageUrl->url : '/';
            $previewUrl = (new GenerateWorkspacePreviewUrlAction)->handle($workspace, $path);
        }

        return new PagePublishStateData(
            pageId: $page->id,
            isDraft: $page->workspace_id !== 0,
            publishedAt: $page->getAttribute('published_at'),
            previewUrl: $previewUrl,
            contextId: $workspace?->id,
            contextName: $workspace?->name,
            contextStatus: $workspace?->status?->getLabel(),
        );
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function extensions(): array
    {
        $rendered = [];

        foreach (app()->tagged(PublishPanelExtender::TAG) as $extender) {
            /** @var PublishPanelExtender $extender */
            $result = $extender->extendPanel($this->state());

            if ($result === null) {
                continue;
            }

            $rendered[] = is_string($result) ? $result : $result->render();
        }

        return $rendered;
    }

    public function render(): View
    {
        return view('capell-publishing-studio::livewire.publish-status-panel');
    }

    private function workspaceForPage(Page $page): ?Workspace
    {
        if ($page->relationLoaded('workspace')) {
            $workspace = $page->getRelation('workspace');

            if ($workspace instanceof Workspace) {
                return $workspace;
            }
        }

        $workspaceId = $page->getAttribute('workspace_id');

        if (! is_int($workspaceId) && ! (is_string($workspaceId) && ctype_digit($workspaceId))) {
            return null;
        }

        if ((int) $workspaceId <= 0) {
            return null;
        }

        return Workspace::query()->find((int) $workspaceId);
    }
}
