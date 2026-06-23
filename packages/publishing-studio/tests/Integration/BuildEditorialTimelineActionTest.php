<?php

declare(strict_types=1);

use Capell\PublishingStudio\Actions\BuildEditorialTimelineAction;
use Capell\PublishingStudio\Enums\EditorialTimelineEntryTypeEnum;
use Capell\PublishingStudio\Enums\PublishingRevisionEventEnum;
use Capell\PublishingStudio\Enums\WorkspaceApprovalActionEnum;
use Capell\PublishingStudio\Models\PreviewLink;
use Capell\PublishingStudio\Models\PublishingRevision;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Models\WorkspaceApproval;
use Capell\PublishingStudio\Models\WorkspaceFieldComment;
use Illuminate\Support\Str;

it('builds a newest first editorial timeline for a workspace', function (): void {
    $actor = $this->createUser(['name' => 'Ben Johnson']);
    $workspace = Workspace::factory()->create([
        'name' => 'Launch edits',
        'created_by' => $actor->getKey(),
        'created_at' => now()->subDays(6),
        'publish_at' => now()->addDay(),
    ]);

    WorkspaceApproval::factory()
        ->workspace($workspace)
        ->actionable($actor)
        ->create([
            'action' => WorkspaceApprovalActionEnum::Submitted,
            'created_at' => now()->subDays(5),
        ]);

    WorkspaceApproval::factory()
        ->workspace($workspace)
        ->actionable($actor)
        ->level(2)
        ->create([
            'action' => WorkspaceApprovalActionEnum::Approved,
            'notes' => 'Ready.',
            'created_at' => now()->subDays(4),
        ]);

    PreviewLink::query()->create([
        'workspace_id' => $workspace->id,
        'token' => PreviewLink::generateToken(),
        'issued_by_type' => $actor->getMorphClass(),
        'issued_by_id' => $actor->getKey(),
        'issued_at' => now()->subDays(3),
        'expires_at' => now()->addDays(4),
    ]);

    WorkspaceFieldComment::query()->create([
        'workspace_id' => $workspace->id,
        'entity_type' => 'page',
        'entity_uuid' => 'page-uuid',
        'field_path' => 'title',
        'author_type' => $actor->getMorphClass(),
        'author_id' => $actor->getKey(),
        'body' => 'Tighten this headline.',
        'created_at' => now()->subDays(2),
    ]);

    PublishingRevision::query()->create([
        'uuid' => (string) Str::uuid(),
        'revisionable_type' => 'page',
        'revisionable_id' => 123,
        'workspace_id' => $workspace->id,
        'version' => 3,
        'event_type' => PublishingRevisionEventEnum::Restored,
        'actor_type' => $actor->getMorphClass(),
        'actor_id' => $actor->getKey(),
        'notes' => 'Restored from previous version.',
        'created_at' => now()->subDay(),
    ]);

    $entries = BuildEditorialTimelineAction::run($workspace);

    expect($entries)->toHaveCount(7)
        ->and($entries->pluck('type')->all())->toBe([
            EditorialTimelineEntryTypeEnum::Scheduled,
            EditorialTimelineEntryTypeEnum::Restored,
            EditorialTimelineEntryTypeEnum::Comment,
            EditorialTimelineEntryTypeEnum::Preview,
            EditorialTimelineEntryTypeEnum::Approved,
            EditorialTimelineEntryTypeEnum::Submitted,
            EditorialTimelineEntryTypeEnum::Draft,
        ])
        ->and($entries->first()->workspaceId)->toBe($workspace->id)
        ->and($entries->where('type', EditorialTimelineEntryTypeEnum::Approved)->first()?->actorName)->toBe('Ben Johnson')
        ->and($entries->where('type', EditorialTimelineEntryTypeEnum::Comment)->first()?->metadata)->toHaveKey('field_path', 'title');
});
