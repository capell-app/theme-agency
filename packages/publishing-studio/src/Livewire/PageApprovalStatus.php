<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Livewire;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Page;
use Capell\PublishingStudio\Enums\WorkspaceApprovalActionEnum;
use Capell\PublishingStudio\Enums\WorkspaceStatusEnum;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Models\WorkspaceApproval;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Override;

class PageApprovalStatus extends Widget
{
    public ?Pageable $record = null;

    public ?int $recordKey = null;

    protected string $view = 'capell-admin::livewire.page-approval-status';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = 'full';

    private ?Pageable $resolvedRecord = null;

    public function mount(): void
    {
        $this->recordKey ??= $this->initialRecordKey();
    }

    public function hydrate(): void
    {
        $this->recordKey ??= $this->initialRecordKey();
    }

    #[Override]
    public function render(): View
    {
        $workspace = $this->pageRecord()?->workspace;
        $approvals = $this->approvalsFor($workspace);
        $latestAction = $approvals->first()?->action;

        return resolve(Factory::class)->make($this->view, [
            'workspace' => $workspace,
            'visible' => $this->isVisibleFor($workspace, $latestAction),
            'title' => $this->titleFor($workspace?->status, $latestAction),
            'approvals' => $approvals,
        ]);
    }

    private function isVisibleFor(?Workspace $workspace, ?WorkspaceApprovalActionEnum $latestAction): bool
    {
        if (! $workspace instanceof Workspace) {
            return false;
        }

        if (in_array($workspace->status, [
            WorkspaceStatusEnum::InReview,
            WorkspaceStatusEnum::Approved,
        ], true)) {
            return true;
        }

        return $latestAction === WorkspaceApprovalActionEnum::ChangesRequested
            || $latestAction === WorkspaceApprovalActionEnum::Rejected;
    }

    private function titleFor(?WorkspaceStatusEnum $status, ?WorkspaceApprovalActionEnum $latestAction): string
    {
        if ($status === WorkspaceStatusEnum::InReview) {
            return __('capell-admin::workspace.approval_panel.in_review_title');
        }

        if ($status === WorkspaceStatusEnum::Approved) {
            return __('capell-admin::workspace.approval_panel.approved_title');
        }

        return match ($latestAction) {
            WorkspaceApprovalActionEnum::ChangesRequested => __('capell-admin::workspace.approval_panel.changes_requested_title'),
            WorkspaceApprovalActionEnum::Rejected => __('capell-admin::workspace.approval_panel.rejected_title'),
            default => '',
        };
    }

    /** @return Collection<int, WorkspaceApproval> */
    private function approvalsFor(?Workspace $workspace): Collection
    {
        if (! $workspace instanceof Workspace) {
            return collect();
        }

        return WorkspaceApproval::query()
            ->where('workspace_id', $workspace->id)
            ->with('actionable')
            ->latest('id')
            ->limit(5)
            ->get();
    }

    private function pageRecord(): ?Pageable
    {
        if ($this->resolvedRecord instanceof Pageable) {
            return $this->resolvedRecord;
        }

        if ($this->record instanceof Pageable) {
            $this->recordKey ??= (int) $this->record->getKey();
            $this->resolvedRecord = $this->record;

            return $this->resolvedRecord;
        }

        if ($this->recordKey === null) {
            return null;
        }

        $this->resolvedRecord = Page::query()
            ->with('workspace')
            ->find($this->recordKey);

        return $this->resolvedRecord;
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
