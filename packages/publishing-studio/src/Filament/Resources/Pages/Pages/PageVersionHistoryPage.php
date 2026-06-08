<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Filament\Resources\Pages\Pages;

use BackedEnum;
use Capell\Admin\Enums\ResourceEnum;
use Capell\Admin\Filament\Resources\Pages\PageResource;
use Capell\Admin\Support\AdminSurfaceLookup;
use Capell\Core\Models\Page;
use Capell\PublishingStudio\Actions\ComparePublishingRevisionAction;
use Capell\PublishingStudio\Actions\ListPublishingRevisionsAction;
use Capell\PublishingStudio\Models\PublishingRevision;
use Capell\PublishingStudio\Services\WorkspaceDiffService;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page as FilamentPage;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Override;

/**
 * Full-page published revision history for a Page record. Draft copies are
 * deliberately excluded: revisions are immutable publish/restore snapshots.
 *
 * @property Page $record
 */
class PageVersionHistoryPage extends FilamentPage
{
    use InteractsWithRecord;

    public ?int $selectedRevisionId = null;

    protected static ?string $slug = '{record}/history';

    protected static string $resource = PageResource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'capell-publishing-studio::filament.resources.pages.version-history';

    #[Override]
    public static function getResource(): string
    {
        return AdminSurfaceLookup::resource(ResourceEnum::Page);
    }

    public function mount(string|int $record): void
    {
        $resolvedRecord = $this->resolveRecord($record);

        abort_unless($resolvedRecord instanceof Page, 404);

        $this->record = $resolvedRecord;

        $this->authorizeResourceAccess();

        $first = $this->getRevisions()->first();

        if ($first instanceof PublishingRevision) {
            $this->selectedRevisionId = (int) $first->getKey();
        }
    }

    #[Override]
    public function getTitle(): string|Htmlable
    {
        return __('capell-admin::button.version_history') . ' — ' . $this->record->name;
    }

    public function selectRevision(int $revisionId): void
    {
        $this->selectedRevisionId = $revisionId;
    }

    /**
     * @return Collection<int, PublishingRevision>
     */
    public function getRevisions(): Collection
    {
        return ListPublishingRevisionsAction::run($this->record);
    }

    public function getSelectedRevision(): ?PublishingRevision
    {
        if ($this->selectedRevisionId === null) {
            return null;
        }

        return $this->getRevisions()
            ->first(fn (PublishingRevision $revision): bool => (int) $revision->getKey() === $this->selectedRevisionId);
    }

    public function renderHtmlDiff(mixed $before, mixed $after): string
    {
        return (new WorkspaceDiffService)->renderHtmlDiff($before, $after);
    }

    public function isLongText(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return str_contains($value, "\n") || strlen($value) > 120;
    }

    #[Override]
    protected function getActions(): array
    {
        return [
            Action::make('back')
                ->label(__('capell-admin::button.edit_page'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn (): string => static::getResource()::getUrl('edit', ['record' => $this->record])),
        ];
    }

    #[Override]
    protected function getViewData(): array
    {
        $selectedRevision = $this->getSelectedRevision();

        return [
            'revisions' => $this->getRevisions(),
            'diffs' => $selectedRevision instanceof PublishingRevision
                ? collect([ComparePublishingRevisionAction::run($selectedRevision)])
                : collect(),
            'selectedRevision' => $selectedRevision,
        ];
    }
}
