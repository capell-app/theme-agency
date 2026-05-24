<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Http\Livewire;

use Capell\Admin\Filament\Resources\Pages\Pages\EditPage;
use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Page;
use Capell\PublishingStudio\Actions\CopyOnWriteAction;
use Capell\PublishingStudio\Actions\CreatePageDraftWorkspaceAction;
use Capell\PublishingStudio\Actions\DeletePageDraftAction;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\WorkspaceContext;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Auth\User as AuthenticatedUser;
use InvalidArgumentException;

class WorkspacePageDraftHandler
{
    public function saveAsDraft(EditPage $editPage): void
    {
        $editPage->saveAsDraftWithLocation(['location' => 'new']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function saveAsDraftWithLocation(EditPage $editPage, array $data): void
    {
        $editPage->authorize('update', $editPage->record);

        $user = auth()->user();

        if (! $user instanceof AuthenticatedUser) {
            return;
        }

        $target = match ($data['location']) {
            'new' => $this->createDraftWorkspace($editPage, $user),
            'active' => WorkspaceContext::current(),
            'other' => Workspace::query()->findOrFail($data['workspace_id']),
            default => throw new InvalidArgumentException('Unknown draft location: ' . $data['location']),
        };

        if (! $target instanceof Workspace) {
            return;
        }

        $editPage->authorize('update', $target);

        $editPage->stripCountAttributes($editPage->record);

        WorkspaceContext::runWith($target, function () use ($editPage): void {
            $editPage->save(shouldRedirect: false);
        });

        $this->ensureDraftExists($editPage->record, $target);

        Notification::make('saved-as-draft')
            ->title(__('capell-admin::message.saved_as_draft_in', ['workspace' => $target->name]))
            ->success()
            ->send();

        $editPage->dispatch('workspace-changed', workspaceId: $target->id);
    }

    public function deletePageDraft(EditPage $editPage, int $draftId): void
    {
        $draft = Page::query()->withoutGlobalScopes()->findOrFail($draftId);
        $editPage->authorize('update', $draft);
        $workspaceName = $draft->workspace->name ?? '—';

        DeletePageDraftAction::run($draft);

        Notification::make()
            ->title(__('capell-admin::message.draft_deleted_notification', ['workspace' => $workspaceName]))
            ->success()
            ->send();
    }

    public function countDrafts(Pageable $record): int
    {
        if (blank($record->uuid)) {
            return 0;
        }

        return Page::query()
            ->withoutGlobalScopes()
            ->where('uuid', $record->uuid)
            ->where('workspace_id', '>', 0)
            ->count();
    }

    public function redirectToLive(EditPage $editPage): void
    {
        $editPage->redirect(
            $editPage::getResource()::getUrl('edit', ['record' => $editPage->record->getKey()]),
            navigate: false,
        );
    }

    private function ensureDraftExists(Pageable $record, Workspace $workspace): void
    {
        if (! $record instanceof Page || blank($record->uuid)) {
            return;
        }

        $draftExists = Page::query()
            ->withoutGlobalScopes()
            ->where('uuid', $record->uuid)
            ->where('workspace_id', $workspace->id)
            ->exists();

        if ($draftExists) {
            return;
        }

        $live = Page::query()
            ->withoutGlobalScopes()
            ->whereKey($record->getKey())
            ->where('workspace_id', 0)
            ->first();

        if (! $live instanceof Page) {
            return;
        }

        (new CopyOnWriteAction)->cloneForEdit($live, $workspace);
    }

    private function createDraftWorkspace(EditPage $editPage, AuthenticatedUser $user): Workspace
    {
        $editPage->authorize('create', Workspace::class);

        return CreatePageDraftWorkspaceAction::run($editPage->record, $user);
    }
}
