<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Livewire;

use Capell\Admin\Contracts\Extenders\PublishPanelExtender;
use Capell\Admin\Data\PagePublishStateData;
use Capell\Core\Models\Page;
use Capell\PublishingStudio\Actions\GenerateWorkspacePreviewUrlAction;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\WorkspaceContext;
use Carbon\CarbonImmutable;
use DateTimeInterface;
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
            $path = is_string($pageUrl?->url) ? $pageUrl->url : '/';
            $previewUrl = (new GenerateWorkspacePreviewUrlAction)->handle($workspace, $path);
        }

        return new PagePublishStateData(
            pageId: (int) $page->getKey(),
            isDraft: $this->isDraft($page),
            publishedAt: $this->isDraft($page) ? null : $this->publishedAt($page),
            previewUrl: $previewUrl,
            contextId: $workspace?->id,
            contextName: $workspace?->name,
            contextStatus: $workspace?->status?->getLabel(),
            scheduledPublishAt: $this->isDraft($page) ? null : $this->scheduledPublishAt($page),
            unpublishAt: $this->isDraft($page) ? null : $this->dateAttribute($page, 'visible_until'),
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

    private function isDraft(Page $page): bool
    {
        return (int) ($page->getAttributes()['workspace_id'] ?? 0) !== 0;
    }

    private function publishedAt(Page $page): ?CarbonImmutable
    {
        $publishedAt = $this->dateAttribute($page, 'published_at');

        if ($publishedAt instanceof CarbonImmutable) {
            return $publishedAt;
        }

        $visibleFrom = $this->dateAttribute($page, 'visible_from');

        if ($visibleFrom instanceof CarbonImmutable && ! $visibleFrom->isFuture()) {
            return $visibleFrom;
        }

        return null;
    }

    private function scheduledPublishAt(Page $page): ?CarbonImmutable
    {
        $visibleFrom = $this->dateAttribute($page, 'visible_from');

        return $visibleFrom instanceof CarbonImmutable && $visibleFrom->isFuture()
            ? $visibleFrom
            : null;
    }

    private function dateAttribute(Page $page, string $attribute): ?CarbonImmutable
    {
        if (! array_key_exists($attribute, $page->getAttributes())) {
            return null;
        }

        $value = $page->getAttribute($attribute);

        if ($value instanceof CarbonImmutable) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value);
        }

        if (is_string($value) || is_int($value)) {
            return CarbonImmutable::parse((string) $value);
        }

        return null;
    }
}
