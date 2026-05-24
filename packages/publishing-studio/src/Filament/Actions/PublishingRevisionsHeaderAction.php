<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Filament\Actions;

use Capell\PublishingStudio\Actions\ListPublishingRevisionsAction;
use Filament\Actions\Action;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Override;

final class PublishingRevisionsHeaderAction extends Action
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (Model $record): string => __('capell-publishing-studio::workspace.revisions.action_label', [
                'count' => ListPublishingRevisionsAction::run($record)->count(),
            ]))
            ->icon('heroicon-o-clock')
            ->visible(fn (Model $record): bool => ListPublishingRevisionsAction::run($record)->isNotEmpty())
            ->modalHeading(__('capell-publishing-studio::workspace.revisions.modal_heading'))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('capell-publishing-studio::workspace.revisions.close'))
            ->modalContent(fn (Model $record): View => view('capell-publishing-studio::filament.actions.publishing-revisions', [
                'revisions' => ListPublishingRevisionsAction::run($record),
            ]));
    }

    public static function getDefaultName(): string
    {
        return 'publishingRevisions';
    }
}
