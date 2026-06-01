<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Livewire;

use Capell\PublishingStudio\Actions\BuildReleaseWorkspaceReadinessAction;
use Capell\PublishingStudio\Actions\BuildReleaseWorkspaceSummaryAction;
use Capell\PublishingStudio\Enums\WorkspaceKindEnum;
use Capell\PublishingStudio\Filament\Resources\PublishingStudio\WorkspaceResource;
use Capell\PublishingStudio\Models\Workspace;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\User as AuthenticatedUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

final class ReleaseWorkspaceSummaryPanel extends Component
{
    private const int SUMMARY_ITEM_LIMIT = 25;

    #[Locked]
    public ?int $workspaceId = null;

    public function mount(?Workspace $record = null, ?Workspace $workspace = null): void
    {
        $resolvedWorkspace = $record ?? $workspace;

        if ($resolvedWorkspace instanceof Workspace) {
            Gate::authorize('view', $resolvedWorkspace);
        }

        $this->workspaceId = $resolvedWorkspace?->getKey();
    }

    public function render(): View
    {
        $workspace = $this->workspace();

        if (! $workspace instanceof Workspace || $workspace->kind !== WorkspaceKindEnum::Release) {
            return view('capell-publishing-studio::livewire.release-workspace-summary-panel', [
                'visible' => false,
            ]);
        }

        Gate::authorize('view', $workspace);

        $summary = BuildReleaseWorkspaceSummaryAction::run($workspace, self::SUMMARY_ITEM_LIMIT);

        return view('capell-publishing-studio::livewire.release-workspace-summary-panel', [
            'visible' => true,
            'summary' => $summary,
            'readiness' => BuildReleaseWorkspaceReadinessAction::run(
                $workspace,
                $summary->itemCount,
                $this->canBypassReleaseWindow(),
            ),
            'remainingItemCount' => max(0, $summary->itemCount - count($summary->items)),
            'compareUrl' => $this->compareUrl($workspace),
        ]);
    }

    private function workspace(): ?Workspace
    {
        if ($this->workspaceId === null) {
            return null;
        }

        return Workspace::query()->find($this->workspaceId);
    }

    private function compareUrl(Workspace $workspace): ?string
    {
        try {
            return WorkspaceResource::getUrl('compare', ['record' => $workspace]);
        } catch (Throwable) {
            return null;
        }
    }

    private function canBypassReleaseWindow(): bool
    {
        $user = Auth::user();

        return $user instanceof AuthenticatedUser
            && $user->can(config('capell.publishing-studio.release_windows.bypass_permission', 'publish_outside_release_window'));
    }
}
