<?php

declare(strict_types=1);

use Capell\PublishingStudio\Actions\RunDueSchedulerEventsAction;
use Capell\PublishingStudio\Enums\SchedulerEventStateEnum;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Capell\PublishingStudio\Enums\WorkspaceStatusEnum;
use Capell\PublishingStudio\Models\SchedulerEvent;
use Capell\PublishingStudio\Models\Workspace;
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

function seedReschedulingWorkspace(string $publishAt): Workspace
{
    $workspace = Workspace::factory()->scheduled(publishAt: $publishAt)->create();

    WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => $workspace->id,
        'uuid' => (string) Str::uuid(),
        'name' => 'draft',
    ]);

    return $workspace;
}

it('reschedules a closed-window scheduled publish to the next window open instead of silently re-skipping every tick', function (): void {
    config()->set('capell.publishing-studio.release_windows.enabled', true);
    config()->set('capell.publishing-studio.release_windows.timezone', 'UTC');
    config()->set('capell.publishing-studio.release_windows.windows', [
        ['days' => ['mon'], 'start' => '09:00', 'end' => '17:00'],
    ]);

    // Saturday 2026-04-18 10:00 — outside the Monday-only window. The editor's
    // chosen go-live time (Saturday 09:00) falls outside the configured window.
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-04-18 10:00:00', 'UTC'));

    $workspace = seedReschedulingWorkspace('2026-04-18 09:00:00');

    // First tick: window closed -> skipped + rescheduled forward.
    RunDueSchedulerEventsAction::run();

    $publishEvent = SchedulerEvent::query()
        ->where('workspace_id', $workspace->getKey())
        ->where('event_type', SchedulerEventTypeEnum::Publish->value)
        ->firstOrFail();

    // The next Monday 09:00 is 2026-04-20 09:00.
    expect($publishEvent->state)->toBe(SchedulerEventStateEnum::SkippedReleaseWindow)
        ->and($publishEvent->scheduled_for->format('Y-m-d H:i'))->toBe('2026-04-20 09:00');

    // A subsequent tick before the window reopens must NOT re-claim the event
    // (it is no longer due), proving the silent every-tick re-skip is gone.
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-04-19 12:00:00', 'UTC'));

    $processedBeforeReopen = RunDueSchedulerEventsAction::run();

    expect($processedBeforeReopen)->toBe(0)
        ->and($workspace->refresh()->status)->toBe(WorkspaceStatusEnum::Scheduled);

    // Once the window reopens (Monday 09:00) the deferred publish fires.
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-04-20 09:30:00', 'UTC'));

    RunDueSchedulerEventsAction::run();

    $publishedEvent = $publishEvent->refresh();

    expect($publishedEvent->state)->toBe(SchedulerEventStateEnum::Executed)
        ->and($workspace->refresh()->status)->toBe(WorkspaceStatusEnum::Published);
});
