<?php

declare(strict_types=1);

use Capell\PublishingStudio\Actions\ComparePublishingRevisionAction;
use Capell\PublishingStudio\Actions\ListPublishingRevisionsAction;
use Capell\PublishingStudio\Actions\ResolveLatestPublishingRevisionAction;
use Capell\PublishingStudio\Enums\PublishingRevisionEventEnum;
use Capell\PublishingStudio\Filament\Actions\PublishingRevisionsHeaderAction;
use Capell\PublishingStudio\Models\PublishingRevision;
use Capell\PublishingStudio\Models\Version;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Publisher;
use Capell\PublishingStudio\Rollback;
use Capell\PublishingStudio\Rollback\EntityRollbackAction;
use Capell\PublishingStudio\Tests\Integration\Fixtures\WorkspaceDraftableFixture;
use Capell\PublishingStudio\WorkspaceRegistry;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

beforeEach(function (): void {
    Schema::create('workspace_draftable_fixtures', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('workspace_id')->default(0)->index();
        $table->unsignedBigInteger('shadowed_by_workspace_id')->default(0)->index();
        $table->uuid('uuid');
        $table->string('name');
        $table->timestamps();
    });

    WorkspaceRegistry::reset();
    WorkspaceRegistry::register(WorkspaceDraftableFixture::class);
});

afterEach(function (): void {
    Schema::dropIfExists('workspace_draftable_fixtures');
    WorkspaceRegistry::reset();
});

it('captures immutable per-entity publish revisions for changed workspace rows', function (): void {
    $workspace = Workspace::factory()->approved()->create();
    $editedUuid = (string) Str::uuid();
    $newUuid = (string) Str::uuid();

    WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => 0,
        'uuid' => $editedUuid,
        'name' => 'live-original',
    ]);

    WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => $workspace->id,
        'uuid' => $editedUuid,
        'name' => 'workspace-edited',
    ]);

    WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => $workspace->id,
        'uuid' => $newUuid,
        'name' => 'workspace-new',
    ]);

    $version = (new Publisher)->publish($workspace, notes: 'release notes');

    $editedRevision = PublishingRevision::query()
        ->where('revisionable_uuid', $editedUuid)
        ->firstOrFail();

    $newRevision = PublishingRevision::query()
        ->where('revisionable_uuid', $newUuid)
        ->firstOrFail();

    expect(PublishingRevision::query()->count())->toBe(2)
        ->and($editedRevision->event_type)->toBe(PublishingRevisionEventEnum::Published)
        ->and($editedRevision->workspace_id)->toBe($workspace->id)
        ->and($editedRevision->version_id)->toBe($version->id)
        ->and($editedRevision->version)->toBe(1)
        ->and($editedRevision->notes)->toBe('release notes')
        ->and($editedRevision->before_payload['name'])->toBe('live-original')
        ->and($editedRevision->after_payload['name'])->toBe('workspace-edited')
        ->and($editedRevision->after_payload['workspace_id'])->toBe(0)
        ->and($newRevision->before_payload)->toBeNull()
        ->and($newRevision->after_payload['name'])->toBe('workspace-new');
});

it('does not create revisions until a workspace is published', function (): void {
    $workspace = Workspace::factory()->approved()->create();

    WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => $workspace->id,
        'uuid' => (string) Str::uuid(),
        'name' => 'draft-only',
    ]);

    expect(PublishingRevision::query()->count())->toBe(0);
});

it('increments revision numbers per content entity across publishes', function (): void {
    $entityUuid = (string) Str::uuid();
    $firstWorkspace = Workspace::factory()->approved()->create();

    WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => $firstWorkspace->id,
        'uuid' => $entityUuid,
        'name' => 'first publish',
    ]);

    (new Publisher)->publish($firstWorkspace);

    $secondWorkspace = Workspace::factory()->approved()->create([
        'base_version_id' => Version::liveId(),
    ]);

    WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => $secondWorkspace->id,
        'uuid' => $entityUuid,
        'name' => 'second publish',
    ]);

    (new Publisher)->publish($secondWorkspace);

    $revisionVersions = PublishingRevision::query()
        ->where('revisionable_uuid', $entityUuid)
        ->orderBy('version')
        ->pluck('version')
        ->all();

    expect($revisionVersions)->toBe([1, 2]);
});

it('lists, compares, and resolves latest revisions for a content entity', function (): void {
    $entityUuid = (string) Str::uuid();
    $record = WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => 0,
        'uuid' => $entityUuid,
        'name' => 'current value',
    ]);

    $first = PublishingRevision::query()->create([
        'uuid' => (string) Str::uuid(),
        'revisionable_type' => WorkspaceDraftableFixture::class,
        'revisionable_id' => $record->id,
        'revisionable_uuid' => $entityUuid,
        'version' => 1,
        'event_type' => PublishingRevisionEventEnum::Published,
        'before_payload' => ['name' => 'old value', 'updated_at' => 'ignored'],
        'after_payload' => ['name' => 'current value', 'updated_at' => 'ignored later'],
    ]);

    $second = PublishingRevision::query()->create([
        'uuid' => (string) Str::uuid(),
        'revisionable_type' => WorkspaceDraftableFixture::class,
        'revisionable_id' => $record->id,
        'revisionable_uuid' => $entityUuid,
        'version' => 2,
        'event_type' => PublishingRevisionEventEnum::Restored,
        'before_payload' => ['name' => 'current value'],
        'after_payload' => ['name' => 'restored value'],
    ]);

    $revisions = ListPublishingRevisionsAction::run($record);
    $comparison = ComparePublishingRevisionAction::run($first);

    expect($revisions->pluck('id')->all())->toBe([$second->id, $first->id])
        ->and(ResolveLatestPublishingRevisionAction::run($record)->is($second))->toBeTrue()
        ->and($comparison['changes'])->toHaveKey('name')
        ->and($comparison['changes'])->not->toHaveKey('updated_at')
        ->and($comparison['changes']['name'])->toBe([
            'before' => 'old value',
            'after' => 'current value',
        ]);
});

it('renders the generic revision timeline action content for draftable models', function (): void {
    $entityUuid = (string) Str::uuid();
    $record = WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => 0,
        'uuid' => $entityUuid,
        'name' => 'current value',
    ]);

    $revision = PublishingRevision::query()->create([
        'uuid' => (string) Str::uuid(),
        'revisionable_type' => WorkspaceDraftableFixture::class,
        'revisionable_id' => $record->id,
        'revisionable_uuid' => $entityUuid,
        'version' => 1,
        'event_type' => PublishingRevisionEventEnum::Published,
        'after_payload' => ['name' => 'current value'],
        'notes' => 'release notes',
    ]);

    $html = view('capell-publishing-studio::filament.actions.publishing-revisions', [
        'revisions' => collect([$revision]),
    ])->render();

    expect(PublishingRevisionsHeaderAction::make()->getName())->toBe('publishingRevisions')
        ->and($html)->toContain('Revision 1')
        ->and($html)->toContain('release notes');
});

it('hides the generic revision timeline action when a draftable model has no revisions', function (): void {
    $record = WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => 0,
        'uuid' => (string) Str::uuid(),
        'name' => 'current value',
    ]);

    expect(PublishingRevisionsHeaderAction::make()->record($record)->isHidden())->toBeTrue();
});

it('shows the generic revision timeline action when a draftable model has revisions', function (): void {
    $entityUuid = (string) Str::uuid();
    $record = WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => 0,
        'uuid' => $entityUuid,
        'name' => 'current value',
    ]);

    PublishingRevision::query()->create([
        'uuid' => (string) Str::uuid(),
        'revisionable_type' => WorkspaceDraftableFixture::class,
        'revisionable_id' => $record->id,
        'revisionable_uuid' => $entityUuid,
        'version' => 1,
        'event_type' => PublishingRevisionEventEnum::Published,
        'after_payload' => ['name' => 'current value'],
    ]);

    expect(PublishingRevisionsHeaderAction::make()->record($record)->isHidden())->toBeFalse();
});

it('captures restored revisions when rolling back a published version', function (): void {
    $keeperUuid = (string) Str::uuid();
    $extraUuid = (string) Str::uuid();
    $firstWorkspace = Workspace::factory()->approved()->create();

    WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => $firstWorkspace->id,
        'uuid' => $keeperUuid,
        'name' => 'keeper',
    ]);

    $targetVersion = (new Publisher)->publish($firstWorkspace);

    $secondWorkspace = Workspace::factory()->approved()->create([
        'base_version_id' => Version::liveId(),
    ]);

    WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => $secondWorkspace->id,
        'uuid' => $extraUuid,
        'name' => 'extra',
    ]);

    (new Publisher)->publish($secondWorkspace);

    $rollbackRecord = (new Rollback)->rollbackTo($targetVersion, reason: 'regression');

    $revision = PublishingRevision::query()
        ->where('event_type', PublishingRevisionEventEnum::Restored)
        ->where('revisionable_uuid', $keeperUuid)
        ->firstOrFail();

    expect($revision->version_id)->toBe($rollbackRecord->id)
        ->and($revision->before_payload['name'])->toBe('keeper')
        ->and($revision->after_payload['name'])->toBe('keeper')
        ->and($revision->notes)->toBe('regression');
});

it('captures restored revisions when rolling back a single entity', function (): void {
    $entityUuid = (string) Str::uuid();

    $targetRow = WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => 999,
        'uuid' => $entityUuid,
        'name' => 'previous value',
    ]);

    WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => 0,
        'uuid' => $entityUuid,
        'name' => 'current value',
    ]);

    $targetVersion = Version::query()->create([
        'uuid' => (string) Str::uuid(),
        'number' => (int) (Version::query()->max('number') ?? 0) + 1,
        'name' => 'version',
        'is_live' => false,
        'manifest' => [WorkspaceDraftableFixture::class => [$targetRow->id]],
        'published_at' => now(),
    ]);

    (new EntityRollbackAction)->handle(
        modelClass: WorkspaceDraftableFixture::class,
        entityUuid: $entityUuid,
        targetVersion: $targetVersion,
        reason: 'restore one item',
    );

    $revision = PublishingRevision::query()
        ->where('event_type', PublishingRevisionEventEnum::Restored)
        ->where('revisionable_uuid', $entityUuid)
        ->firstOrFail();

    expect($revision->version_id)->toBe($targetVersion->id)
        ->and($revision->before_payload['name'])->toBe('current value')
        ->and($revision->after_payload['name'])->toBe('previous value')
        ->and($revision->notes)->toBe('restore one item');
});
