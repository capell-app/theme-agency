<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Filament\Widgets;

use Capell\Admin\Data\MessageData;
use Capell\Admin\Enums\AlertTypeEnum;
use Capell\Admin\Enums\ResourceEnum;
use Capell\Admin\Filament\Resources\Pages\PageResource;
use Capell\Admin\Filament\Resources\Sites\SiteResource;
use Capell\Admin\Filament\Widgets\ResourceAlertsWidget;
use Capell\Core\Actions\GetResourceFromBlueprintAction;
use Capell\Core\Contracts\Pageable;
use Capell\Core\Enums\PublishStatusEnum;
use Capell\Core\Models\Page;
use Capell\HtmlCache\Actions\ClearCachedUrlsForModelAction;
use Capell\HtmlCache\Models\CachedModelUrl;
use Capell\PublishingStudio\Filament\Resources\PublishingStudio\WorkspaceResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Support\Collection;

class PageAlertsWidget extends ResourceAlertsWidget
{
    public ?Pageable $record = null;

    public ?int $recordKey = null;

    private ?Page $resolvedRecord = null;

    public function mount(): void
    {
        $this->recordKey ??= $this->initialRecordKey();
    }

    public function hydrate(): void
    {
        $this->recordKey ??= $this->initialRecordKey();
    }

    public function clearCacheAction(): Action
    {
        return Action::make('clearCache')
            ->label(__('capell-admin::button.clear_cache'))
            ->icon('heroicon-c-trash')
            ->color('warning')
            ->link()
            ->size(Size::Small)
            ->action(function (): void {
                $record = $this->pageRecord();

                if (! $record instanceof Page) {
                    return;
                }

                ClearCachedUrlsForModelAction::dispatch($record);

                Notification::make()
                    ->title(__('capell-admin::notification.page_cache_cleared'))
                    ->success()
                    ->send();
            });
    }

    public function viewSiteAction(): Action
    {
        return Action::make('viewSite')
            ->label(__('capell-admin::button.edit_site'))
            ->link()
            ->url(SiteResource::getUrl('edit', ['record' => $this->pageRecord()->site->id]));
    }

    public function viewCanonicalsAction(): Action
    {
        return Action::make('viewCanonicals')
            ->label(__('capell-admin::button.view_pages'))
            ->visible(fn (): bool => (bool) $this->pageRecord()->canonical_pages_count)
            ->url(
                self::getResource()::getUrl(
                    'index',
                    ['filters[filter][canonical_page_id]' => $this->pageRecord()->getKey()],
                ),
            );
    }

    protected static function getCachedPage(Pageable $page): ?CachedModelUrl
    {
        return CachedModelUrl::query()
            ->where('cacheable_type', $page->getMorphClass())
            ->where('cacheable_id', $page->getKey())
            ->latest('cached_at')
            ->first();
    }

    /**
     * @return Collection<string, MessageData>
     */
    protected function buildAlerts(): Collection
    {
        $record = $this->pageRecord();
        $alerts = collect();

        if (! $record instanceof Page) {
            return $alerts;
        }

        $pageStatus = $this->draftStatusAlert();

        if ($pageStatus instanceof MessageData) {
            $alerts->put('pageStatus', $pageStatus);
        }

        if ($record->trashed()) {
            $alerts->put('deleted', new MessageData(
                message: __('capell-admin::message.resource_deleted'),
                type: AlertTypeEnum::Warning,
                icon: 'heroicon-m-exclamation-triangle',
            ));
        }

        if ($record->site->trashed()) {
            $alerts->put('deleted_site', new MessageData(
                message: __('capell-admin::message.page_site_deleted'),
                type: AlertTypeEnum::Warning,
                icon: 'heroicon-m-exclamation-triangle',
                action: $this->viewSiteAction(),
            ));
        }

        $record->loadCount('pageUrls');

        if ($record->page_urls_count === 0) {
            $alerts->put('missingUrl', new MessageData(
                message: __('capell-admin::message.page_no_urls'),
                type: AlertTypeEnum::Warning,
                icon: 'heroicon-o-link',
            ));
        }

        $record->loadCount('canonicalPages');

        if (($record->canonical_pages_count ?? 0) > 0) {
            $alerts->put('referenced', new MessageData(
                message: __('capell-admin::message.canonical_page_count', [
                    'count' => $record->canonical_pages_count,
                ]),
                type: AlertTypeEnum::Info,
                icon: 'heroicon-o-information-circle',
                action: $this->viewCanonicalsAction(),
            ));
        }

        switch ($record->publish_status) {
            case PublishStatusEnum::pending:
                $alerts->put('pending', new MessageData(
                    message: __('capell-admin::message.resource_pending', [
                        'date' => $record->visible_from?->diffForHumans(),
                        'name' => __('capell-admin::generic.page'),
                    ]),
                    type: AlertTypeEnum::Warning,
                    icon: 'heroicon-o-clock',
                ));
                break;
            case PublishStatusEnum::expired:
                $alerts->put('expired', new MessageData(
                    message: __('capell-admin::message.resource_expired', [
                        'date' => $record->visible_until?->diffForHumans(),
                        'name' => strtolower(__('capell-admin::generic.page')),
                    ]),
                    type: AlertTypeEnum::Warning,
                    icon: 'heroicon-o-clock',
                ));
                break;
        }

        $cachedPage = static::getCachedPage($record);
        if ($cachedPage instanceof CachedModelUrl) {
            $alerts->put('cached', new MessageData(
                message: __(
                    'capell-admin::message.page_cached_warning',
                    ['diff_time' => $cachedPage->cached_at?->diffForHumans()],
                ),
                type: AlertTypeEnum::Info,
                icon: 'heroicon-o-check-badge',
                action: $this->clearCacheAction(),
            ));
        }

        return $alerts;
    }

    protected function pageRecord(): ?Page
    {
        if ($this->resolvedRecord instanceof Page) {
            return $this->resolvedRecord;
        }

        if ($this->record instanceof Page) {
            $this->recordKey ??= (int) $this->record->getKey();
            $this->resolvedRecord = $this->hydratePageRecord($this->record);

            return $this->resolvedRecord;
        }

        if ($this->recordKey === null) {
            return null;
        }

        $this->resolvedRecord = Page::query()
            ->withTrashed()
            ->find($this->recordKey);

        if (! $this->resolvedRecord instanceof Page) {
            return null;
        }

        $this->resolvedRecord = $this->hydratePageRecord($this->resolvedRecord);

        return $this->resolvedRecord;
    }

    private function hydratePageRecord(Page $page): Page
    {
        $page->load([
            'site' => fn (BuilderContract $query): BuilderContract => $query->withTrashed(),
            'type',
            'pageUrls',
        ]);

        return $page;
    }

    private function draftStatusAlert(): ?MessageData
    {
        $record = $this->pageRecord();

        if (! $record instanceof Page) {
            return null;
        }

        $workspaceId = $record->getAttribute('workspace_id');

        if ($workspaceId === null || (int) $workspaceId === 0) {
            return null;
        }

        $workspace = $record->workspace;

        $actions = [];

        $previewUrl = rescue(fn (): ?string => $record->pageUrls->first()?->full_url, null, false);

        if ($previewUrl !== null && $previewUrl !== '') {
            $actions[] = Action::make('previewDraft')
                ->label(__('capell-admin::button.preview'))
                ->link()
                ->size(Size::Small)
                ->icon('heroicon-o-eye')
                ->url($previewUrl)
                ->openUrlInNewTab();
        }

        $actions[] = Action::make('openWorkspace')
            ->label(__('capell-admin::message.page_status_open_workspace'))
            ->link()
            ->size(Size::Small)
            ->url(WorkspaceResource::getUrl('index'));

        return new MessageData(
            message: __('capell-admin::message.page_status_draft', [
                'workspace' => $workspace->name ?? '—',
            ]),
            type: AlertTypeEnum::Info,
            icon: 'heroicon-o-document-text',
            action: $actions,
        );
    }

    /**
     * @return class-string<PageResource>
     */
    private function getResource(): string
    {
        return GetResourceFromBlueprintAction::run(ResourceEnum::Page, $this->pageRecord()?->type) ?? PageResource::class;
    }

    private function initialRecordKey(): ?int
    {
        if ($this->record instanceof Pageable) {
            return (int) $this->record->getKey();
        }

        $routeRecord = request()->route('record');

        if ($routeRecord instanceof Pageable) {
            return (int) $routeRecord->getKey();
        }

        if (is_numeric($routeRecord)) {
            return (int) $routeRecord;
        }

        return null;
    }
}
