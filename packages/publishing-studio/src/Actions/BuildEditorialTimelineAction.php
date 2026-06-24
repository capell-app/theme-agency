<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Actions;

use Capell\PublishingStudio\Data\EditorialTimelineEntryData;
use Capell\PublishingStudio\Enums\EditorialTimelineEntryTypeEnum;
use Capell\PublishingStudio\Enums\PublishingRevisionEventEnum;
use Capell\PublishingStudio\Enums\WorkspaceApprovalActionEnum;
use Capell\PublishingStudio\Models\PreviewLink;
use Capell\PublishingStudio\Models\PublishingRevision;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Models\WorkspaceApproval;
use Capell\PublishingStudio\Models\WorkspaceFieldComment;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Collection<int, EditorialTimelineEntryData> run(Workspace $workspace)
 */
final class BuildEditorialTimelineAction
{
    use AsAction;

    /**
     * @return Collection<int, EditorialTimelineEntryData>
     */
    public function handle(Workspace $workspace): Collection
    {
        return collect()
            ->merge($this->workspaceEntries($workspace))
            ->merge($this->approvalEntries($workspace))
            ->merge($this->previewEntries($workspace))
            ->merge($this->commentEntries($workspace))
            ->merge($this->revisionEntries($workspace))
            ->sortByDesc(fn (EditorialTimelineEntryData $entry): int => $entry->occurredAt->getTimestamp())
            ->values();
    }

    /**
     * @return list<EditorialTimelineEntryData>
     */
    private function workspaceEntries(Workspace $workspace): array
    {
        $entries = [];

        if ($workspace->created_at !== null) {
            $entries[] = new EditorialTimelineEntryData(
                type: EditorialTimelineEntryTypeEnum::Draft,
                title: __('capell-publishing-studio::editorial_timeline.entries.draft_created'),
                description: $workspace->name,
                occurredAt: CarbonImmutable::instance($workspace->created_at),
                actorName: $this->userName($workspace->created_by),
                workspaceId: $workspace->id,
            );
        }

        if ($workspace->publish_at !== null) {
            $entries[] = new EditorialTimelineEntryData(
                type: EditorialTimelineEntryTypeEnum::Scheduled,
                title: __('capell-publishing-studio::editorial_timeline.entries.scheduled'),
                description: $workspace->name,
                occurredAt: CarbonImmutable::instance($workspace->publish_at),
                workspaceId: $workspace->id,
            );
        }

        if ($workspace->published_at !== null) {
            $entries[] = new EditorialTimelineEntryData(
                type: EditorialTimelineEntryTypeEnum::Published,
                title: __('capell-publishing-studio::editorial_timeline.entries.published'),
                description: $workspace->name,
                occurredAt: CarbonImmutable::instance($workspace->published_at),
                workspaceId: $workspace->id,
            );
        }

        return $entries;
    }

    /**
     * @return Collection<int, EditorialTimelineEntryData>
     */
    private function approvalEntries(Workspace $workspace): Collection
    {
        return WorkspaceApproval::query()
            ->with('actionable')
            ->where('workspace_id', $workspace->id)
            ->oldest()
            ->get()
            ->map(fn (WorkspaceApproval $approval): EditorialTimelineEntryData => new EditorialTimelineEntryData(
                type: $this->approvalType($approval->action),
                title: $approval->action->getLabel(),
                description: $approval->notes,
                occurredAt: CarbonImmutable::instance($approval->created_at ?? now()),
                actorName: $this->modelName($approval->actionable),
                workspaceId: $workspace->id,
                metadata: ['level' => $approval->level],
            ));
    }

    /**
     * @return Collection<int, EditorialTimelineEntryData>
     */
    private function previewEntries(Workspace $workspace): Collection
    {
        return PreviewLink::query()
            ->with('issuedBy')
            ->where('workspace_id', $workspace->id)
            ->oldest('issued_at')
            ->get()
            ->map(fn (PreviewLink $previewLink): EditorialTimelineEntryData => new EditorialTimelineEntryData(
                type: EditorialTimelineEntryTypeEnum::Preview,
                title: __('capell-publishing-studio::editorial_timeline.entries.preview_link_created'),
                description: $previewLink->isRevoked() && is_string($revokedDescription = __('capell-publishing-studio::editorial_timeline.entries.preview_link_revoked'))
                    ? $revokedDescription
                    : null,
                occurredAt: CarbonImmutable::instance($previewLink->issued_at ?? $previewLink->created_at ?? now()),
                actorName: $this->modelName($previewLink->issuedBy),
                workspaceId: $workspace->id,
                metadata: [
                    'expires_at' => $previewLink->expires_at->toIso8601String(),
                    'revoked_at' => $previewLink->revoked_at?->toIso8601String(),
                ],
            ));
    }

    /**
     * @return Collection<int, EditorialTimelineEntryData>
     */
    private function commentEntries(Workspace $workspace): Collection
    {
        return WorkspaceFieldComment::query()
            ->with('author')
            ->where('workspace_id', $workspace->id)
            ->oldest()
            ->get()
            ->map(fn (WorkspaceFieldComment $comment): EditorialTimelineEntryData => new EditorialTimelineEntryData(
                type: EditorialTimelineEntryTypeEnum::Comment,
                title: __('capell-publishing-studio::editorial_timeline.entries.comment_added'),
                description: $comment->body,
                occurredAt: CarbonImmutable::instance($comment->created_at ?? now()),
                actorName: $this->modelName($comment->author),
                workspaceId: $workspace->id,
                entityType: $comment->entity_type,
                entityId: $comment->entity_uuid,
                metadata: [
                    'field_path' => $comment->field_path,
                    'resolved_at' => $comment->resolved_at?->toIso8601String(),
                ],
            ));
    }

    /**
     * @return Collection<int, EditorialTimelineEntryData>
     */
    private function revisionEntries(Workspace $workspace): Collection
    {
        return PublishingRevision::query()
            ->with('actor')
            ->where('workspace_id', $workspace->id)
            ->oldest()
            ->get()
            ->map(fn (PublishingRevision $revision): EditorialTimelineEntryData => new EditorialTimelineEntryData(
                type: $revision->event_type === PublishingRevisionEventEnum::Restored
                    ? EditorialTimelineEntryTypeEnum::Restored
                    : EditorialTimelineEntryTypeEnum::Published,
                title: $revision->event_type === PublishingRevisionEventEnum::Restored
                    ? __('capell-publishing-studio::editorial_timeline.entries.restored')
                    : __('capell-publishing-studio::editorial_timeline.entries.revision_published'),
                description: $revision->notes,
                occurredAt: CarbonImmutable::instance($revision->created_at ?? now()),
                actorName: $this->modelName($revision->actor),
                workspaceId: $workspace->id,
                versionId: $revision->version_id,
                entityType: $revision->revisionable_type,
                entityId: $revision->revisionable_id,
                metadata: [
                    'revision_id' => $revision->id,
                    'event_type' => $revision->event_type->value,
                ],
            ));
    }

    private function approvalType(WorkspaceApprovalActionEnum $action): EditorialTimelineEntryTypeEnum
    {
        return match ($action) {
            WorkspaceApprovalActionEnum::Submitted => EditorialTimelineEntryTypeEnum::Submitted,
            WorkspaceApprovalActionEnum::Approved => EditorialTimelineEntryTypeEnum::Approved,
            WorkspaceApprovalActionEnum::Rejected => EditorialTimelineEntryTypeEnum::Rejected,
            WorkspaceApprovalActionEnum::ChangesRequested => EditorialTimelineEntryTypeEnum::ChangesRequested,
        };
    }

    private function userName(?int $userId): ?string
    {
        if ($userId === null) {
            return null;
        }

        $userClass = config('auth.providers.users.model');

        if (! is_string($userClass) || ! is_a($userClass, Model::class, true)) {
            return null;
        }

        /** @var Model|null $user */
        $user = $userClass::query()->find($userId);

        return $this->modelName($user);
    }

    private function modelName(?Model $model): ?string
    {
        if (! $model instanceof Model) {
            return null;
        }

        foreach (['name', 'title', 'email'] as $attribute) {
            $value = $model->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }
}
