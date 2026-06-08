<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Filament\Resources\PublishingStudio\Actions;

use Capell\PublishingStudio\Actions\BuildPublishReadinessAction;
use Capell\PublishingStudio\Data\PublishReadinessData;
use Capell\PublishingStudio\Models\Workspace;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Override;

class ValidateAction extends Action
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('capell-admin::workspace.actions.validate'))
            ->icon(Heroicon::OutlinedClipboardDocumentCheck)
            ->color('info')
            ->authorize('view')
            ->action(function (Workspace $record): void {
                $this->notifyFromReadiness(BuildPublishReadinessAction::run($record));
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'validate';
    }

    private function notifyFromReadiness(PublishReadinessData $readiness): void
    {
        if ($readiness->failureMessage !== null && $readiness->collisions === [] && $readiness->conflictCount === 0) {
            Notification::make()
                ->title(__('capell-admin::workspace.notifications.validate_failed'))
                ->body(__('capell-publishing-studio::workspace.validation.failed_body', [
                    'message' => $readiness->failureMessage,
                ]))
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        if ($readiness->blockingIssueCount > 0) {
            Notification::make()
                ->title(__('capell-admin::workspace.notifications.validate_warnings'))
                ->body(trans_choice('capell-publishing-studio::workspace.validation.blocking_body', $readiness->blockingIssueCount, [
                    'count' => $readiness->blockingIssueCount,
                    'first' => $readiness->blockingIssues[0] ?? '',
                ]))
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        Notification::make()
            ->title(__('capell-admin::workspace.notifications.validate_passed'))
            ->body(__('capell-admin::workspace.notifications.validate_passed_body', [
                'rows' => $readiness->totalRows,
            ]))
            ->success()
            ->send();
    }
}
