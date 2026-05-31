<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Filament\Actions;

use Capell\Admin\Filament\Resources\Pages\Pages\EditPage;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Page;
use Capell\FilamentPeek\Actions\CreatePagePreviewSnapshotAction;
use Capell\FilamentPeek\Providers\FilamentPeekServiceProvider;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Pboivin\FilamentPeek\Facades\Peek;

final class PeekPagePreviewAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('capell-filament-peek::actions.preview.label'))
            ->tooltip(__('capell-filament-peek::actions.preview.tooltip'))
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->authorize(fn (?Model $record): bool => $record instanceof Page && Gate::allows('update', $record))
            ->visible(fn (?Model $record): bool => $record instanceof Page && CapellCore::isPackageInstalled(FilamentPeekServiceProvider::$packageName))
            ->action(function (Model $record, EditPage $livewire): void {
                if (! $record instanceof Page) {
                    return;
                }

                $snapshot = CreatePagePreviewSnapshotAction::run(
                    page: $record,
                    formState: $this->formState($livewire),
                );

                $livewire->dispatch(
                    'open-preview-modal',
                    modalTitle: __('capell-filament-peek::actions.preview.modal_title'),
                    iframeUrl: $snapshot['url'],
                    iframeContent: null,
                );
            });

        Peek::registerPreviewModal();
    }

    public static function getDefaultName(): string
    {
        return 'peekPagePreview';
    }

    /**
     * @return array<string, mixed>
     */
    private function formState(EditPage $livewire): array
    {
        return $livewire->data ?? [];
    }
}
