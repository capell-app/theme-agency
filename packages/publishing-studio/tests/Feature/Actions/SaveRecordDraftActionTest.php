<?php

declare(strict_types=1);

use Capell\PublishingStudio\Actions\ListPublishingRevisionsAction;
use Capell\PublishingStudio\Actions\SaveRecordDraftAction;
use Capell\PublishingStudio\Enums\WorkspaceStatusEnum;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Publisher;
use Capell\PublishingStudio\Tests\Integration\Fixtures\WorkspaceDraftableFixture;
use Capell\PublishingStudio\WorkspaceRegistry;
use Capell\Tests\Fixtures\Models\User;
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

it('saves a live record into a workspace draft without mutating live', function (): void {
    $user = User::factory()->create();
    $uuid = (string) Str::uuid();

    $live = WorkspaceDraftableFixture::query()->create([
        'workspace_id' => 0,
        'uuid' => $uuid,
        'name' => 'Live name',
    ]);

    $result = SaveRecordDraftAction::run($live, ['name' => 'Draft name'], $user);

    expect($result->workspace)->toBeInstanceOf(Workspace::class)
        ->and($result->record)->toBeInstanceOf(WorkspaceDraftableFixture::class)
        ->and($result->record->getAttribute('workspace_id'))->toBe($result->workspace->id)
        ->and($result->record->getAttribute('uuid'))->toBe($uuid)
        ->and($result->record->getAttribute('name'))->toBe('Draft name')
        ->and($live->fresh()->getAttribute('name'))->toBe('Live name')
        ->and($live->fresh()->getAttribute('shadowed_by_workspace_id'))->toBe($result->workspace->id);
});

it('keeps revision history empty until the draft workspace is published', function (): void {
    config()->set('capell.publishing-studio.release_windows.enabled', false);

    $user = User::factory()->create();
    $uuid = (string) Str::uuid();

    $live = WorkspaceDraftableFixture::query()->create([
        'workspace_id' => 0,
        'uuid' => $uuid,
        'name' => 'Live name',
    ]);

    expect(ListPublishingRevisionsAction::run($live))->toBeEmpty();

    $result = SaveRecordDraftAction::run($live, ['name' => 'Published name'], $user);
    $result->workspace->update(['status' => WorkspaceStatusEnum::Approved]);

    resolve(Publisher::class)->publish($result->workspace->fresh(), $user, bypassWindow: true);

    $published = WorkspaceDraftableFixture::query()
        ->withoutGlobalScopes()
        ->where('uuid', $uuid)
        ->where('workspace_id', 0)
        ->firstOrFail();

    expect($published->getAttribute('name'))->toBe('Published name')
        ->and(ListPublishingRevisionsAction::run($published))->toHaveCount(1);
});
