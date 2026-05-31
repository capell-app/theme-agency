<?php

declare(strict_types=1);

use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Models\WorkspaceFieldComment;

it('persists a field comment and resolves/reopens it', function (): void {
    $workspace = Workspace::factory()->create();

    $comment = WorkspaceFieldComment::query()->create([
        'workspace_id' => $workspace->id,
        'entity_type' => 'page',
        'entity_uuid' => 'abc-123',
        'field_path' => 'title',
        'body' => 'This copy feels off.',
    ]);

    expect($comment->isResolved())->toBeFalse();

    $comment->resolve();
    expect(publishingStudioTestInstance($comment->fresh(), WorkspaceFieldComment::class)->isResolved())->toBeTrue();

    $comment->reopen();
    expect(publishingStudioTestInstance($comment->fresh(), WorkspaceFieldComment::class)->isResolved())->toBeFalse();
});

it('queries unresolved comments for a given field path', function (): void {
    $workspace = Workspace::factory()->create();

    WorkspaceFieldComment::query()->create([
        'workspace_id' => $workspace->id,
        'entity_type' => 'page',
        'entity_uuid' => 'abc-123',
        'field_path' => 'title',
        'body' => 'open',
    ]);
    WorkspaceFieldComment::query()->create([
        'workspace_id' => $workspace->id,
        'entity_type' => 'page',
        'entity_uuid' => 'abc-123',
        'field_path' => 'title',
        'body' => 'closed',
        'resolved_at' => now(),
    ]);

    $unresolved = WorkspaceFieldComment::query()
        ->where('entity_uuid', 'abc-123')
        ->where('field_path', 'title')
        ->whereNull('resolved_at')
        ->get();

    $comment = publishingStudioTestInstance($unresolved->first(), WorkspaceFieldComment::class);

    expect($unresolved)->toHaveCount(1)
        ->and($comment->body)->toBe('open');
});
