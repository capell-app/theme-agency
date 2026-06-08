<?php

declare(strict_types=1);

use Capell\PublishingStudio\Enums\SchedulerEventStateEnum;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Capell\PublishingStudio\Enums\WorkspaceStatusEnum;
use Capell\PublishingStudio\Models\SchedulerEvent;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\PublishScheduledPublishingStudioJob;
use Capell\PublishingStudio\Tests\Integration\Fixtures\WorkspaceDraftableFixture;
use Capell\PublishingStudio\WorkspaceRegistry;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

beforeEach(function (): void {
    Schema::create('workspace_draftable_fixtures', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('workspace_id')->default(0)->index();
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
    CarbonImmutable::setTestNow();
    config()->set('capell.publishing-studio.release_windows.enabled', false);
});

function seedScheduledWorkspace(string $publishAt): Workspace
{
    $workspace = Workspace::factory()->scheduled(publishAt: $publishAt)->create();

    WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => $workspace->id,
        'uuid' => (string) Str::uuid(),
        'name' => 'draft',
    ]);

    return $workspace;
}

it('publishes every scheduled workspace whose publish_at has elapsed', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 18:05:00', 'UTC'));

    $due = seedScheduledWorkspace('2026-05-01 18:00:00');
    $notDue = seedScheduledWorkspace('2026-05-02 09:00:00');

    (new PublishScheduledPublishingStudioJob)->handle();

    $freshDue = publishingStudioTestInstance($due->fresh(), Workspace::class);
    $freshNotDue = publishingStudioTestInstance($notDue->fresh(), Workspace::class);
    $publishEvent = SchedulerEvent::query()
        ->where('workspace_id', $due->getKey())
        ->where('event_type', SchedulerEventTypeEnum::Publish->value)
        ->firstOrFail();

    expect($freshDue->status)->toBe(WorkspaceStatusEnum::Published)
        ->and($freshNotDue->status)->toBe(WorkspaceStatusEnum::Scheduled)
        ->and($publishEvent->state)->toBe(SchedulerEventStateEnum::Executed);
});

it('leaves a scheduled workspace in place when the release window is closed', function (): void {
    config()->set('capell.publishing-studio.release_windows.enabled', true);
    config()->set('capell.publishing-studio.release_windows.timezone', 'UTC');
    config()->set('capell.publishing-studio.release_windows.windows', [
        ['days' => ['mon'], 'start' => '09:00', 'end' => '17:00'],
    ]);

    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-04-18 10:00:00', 'UTC'));

    $workspace = seedScheduledWorkspace('2026-04-18 09:00:00');

    (new PublishScheduledPublishingStudioJob)->handle();

    $freshWorkspace = publishingStudioTestInstance($workspace->fresh(), Workspace::class);
    $publishEvent = SchedulerEvent::query()
        ->where('workspace_id', $workspace->getKey())
        ->where('event_type', SchedulerEventTypeEnum::Publish->value)
        ->firstOrFail();

    expect($freshWorkspace->status)->toBe(WorkspaceStatusEnum::Scheduled)
        ->and($publishEvent->state)->toBe(SchedulerEventStateEnum::SkippedReleaseWindow);
});

it('ignores publishing-studio whose publish_at is still in the future', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    $workspace = seedScheduledWorkspace('2026-05-01 18:00:00');

    (new PublishScheduledPublishingStudioJob)->handle();

    $freshWorkspace = publishingStudioTestInstance($workspace->fresh(), Workspace::class);

    expect($freshWorkspace->status)->toBe(WorkspaceStatusEnum::Scheduled);
});
