<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Livewire;

use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Services\WorkspaceDiffService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DiffPanel extends Component
{
    #[Locked]
    public int $workspaceId;

    public string $mode = 'side-by-side';

    public bool $showUnchanged = false;

    public function mount(int $workspaceId): void
    {
        Gate::authorize('view', Workspace::query()->findOrFail($workspaceId));

        $this->workspaceId = $workspaceId;
    }

    public function toggleMode(): void
    {
        $this->mode = $this->mode === 'side-by-side' ? 'inline' : 'side-by-side';
    }

    public function toggleUnchanged(): void
    {
        $this->showUnchanged = ! $this->showUnchanged;
    }

    public function render(): View
    {
        $workspace = Workspace::query()->findOrFail($this->workspaceId);
        Gate::authorize('view', $workspace);

        $diffs = (new WorkspaceDiffService)->diffTree($workspace);

        return view('capell-publishing-studio::components.publishing-studio.diff-panel', [
            'diffs' => $diffs,
        ]);
    }
}
