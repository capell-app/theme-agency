<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Policies;

use Capell\Admin\Policies\Concerns\ResolvesShieldPermission;
use Capell\PublishingStudio\Enums\PublishingStudioPermission;
use Capell\PublishingStudio\Enums\WorkspaceStatusEnum;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Support\WorkspaceAccess;
use Illuminate\Contracts\Auth\Authenticatable;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

/**
 * Workspace-scoped policy. Approval and publish decisions live here —
 * per-page approval has been folded into the workspace workflow.
 *
 * CRUD permission names are built via ResolvesShieldPermission to match the
 * host app's filament-shield.php configuration (case + separator). Workflow
 * permissions (approve, publish, submit) use the custom names defined in
 * PublishingStudioPermission.
 */
class WorkspacePolicy
{
    use ResolvesShieldPermission;

    private const string SUBJECT = 'Workspace';

    public function viewAny(Authenticatable $user): bool
    {
        if ($this->userHasPermission($user, self::permission('view_any', self::SUBJECT))) {
            return true;
        }

        return $this->userHasPermission($user, self::permission('view', self::SUBJECT));
    }

    public function view(Authenticatable $user, Workspace $workspace): bool
    {
        return $this->userHasPermission($user, self::permission('view', self::SUBJECT))
            && WorkspaceAccess::actorCanUseWorkspace($user, $workspace);
    }

    public function create(Authenticatable $user): bool
    {
        return $this->userHasPermission($user, self::permission('create', self::SUBJECT));
    }

    public function update(Authenticatable $user, Workspace $workspace): bool
    {
        return $this->userHasPermission($user, self::permission('update', self::SUBJECT))
            && WorkspaceAccess::actorCanUseWorkspace($user, $workspace)
            && $workspace->isEditable();
    }

    public function delete(Authenticatable $user, Workspace $workspace): bool
    {
        return $this->userHasPermission($user, self::permission('delete', self::SUBJECT))
            && WorkspaceAccess::actorCanUseWorkspace($user, $workspace);
    }

    public function preview(Authenticatable $user, Workspace $workspace): bool
    {
        return $this->view($user, $workspace);
    }

    /** Submit a workspace for review (junior-level action). */
    public function submitForApproval(Authenticatable $user, Workspace $workspace): bool
    {
        if (! $this->userHasPermission($user, PublishingStudioPermission::SubmitWorkspaceForApproval->value)) {
            return false;
        }

        return WorkspaceAccess::actorCanUseWorkspace($user, $workspace)
            && $workspace->status === WorkspaceStatusEnum::Open;
    }

    /** Approve a workspace that is in review (senior-level action). */
    public function approve(Authenticatable $user, Workspace $workspace): bool
    {
        if (! $this->userHasPermission($user, PublishingStudioPermission::ApproveWorkspace->value)) {
            return false;
        }

        return WorkspaceAccess::actorCanUseWorkspace($user, $workspace)
            && $workspace->status === WorkspaceStatusEnum::InReview;
    }

    /** Reject a workspace that is in review (senior-level action). */
    public function reject(Authenticatable $user, Workspace $workspace): bool
    {
        // Rejection requires the same approve permission — approvers can also reject.
        if (! $this->userHasPermission($user, PublishingStudioPermission::ApproveWorkspace->value)) {
            return false;
        }

        return WorkspaceAccess::actorCanUseWorkspace($user, $workspace)
            && $workspace->status === WorkspaceStatusEnum::InReview;
    }

    /** Publish an approved workspace onto live (release-level action). */
    public function publish(Authenticatable $user, Workspace $workspace): bool
    {
        if (! $this->userHasPermission($user, PublishingStudioPermission::PublishWorkspace->value)) {
            return false;
        }

        return WorkspaceAccess::actorCanUseWorkspace($user, $workspace) && in_array($workspace->status, [
            WorkspaceStatusEnum::Approved,
            WorkspaceStatusEnum::Scheduled,
        ], true);
    }

    /**
     * Safe permission check that tolerates environments where Shield has not
     * yet generated the workspace CRUD permissions (tests, fresh installs).
     */
    private function userHasPermission(Authenticatable $user, string $permission): bool
    {
        try {
            return $user->checkPermissionTo($permission);
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }
}
