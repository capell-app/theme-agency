<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\PublishingStudio\Actions\BuildSchedulerIcalFeedAction;
use Capell\PublishingStudio\Actions\DashboardReports\BuildContentSchedulerEventsAction;
use Capell\PublishingStudio\Actions\ExecuteSchedulerEventAction;
use Capell\PublishingStudio\Actions\ExpireWorkspacePublicVisibilityAction;
use Capell\PublishingStudio\Actions\RunDueSchedulerEventsAction;
use Capell\PublishingStudio\Actions\SendWorkspaceReviewReminderAction;
use Capell\PublishingStudio\Actions\SetWorkspaceSchedulerMetadataAction;
use Capell\PublishingStudio\Actions\SyncWorkspaceSchedulerEventsAction;
use Capell\PublishingStudio\Data\SchedulerEventData;
use Capell\PublishingStudio\Enums\SchedulerEventStateEnum;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Capell\PublishingStudio\Enums\SchedulerIcalFeedScopeEnum;
use Capell\PublishingStudio\Exceptions\InvalidSchedulerMetadataException;
use Capell\PublishingStudio\Models\SchedulerEvent;
use Capell\PublishingStudio\Models\SchedulerIcalToken;
use Capell\PublishingStudio\Models\Version;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Models\WorkspaceReviewAssignment;
use Capell\PublishingStudio\Notifications\WorkspaceReviewReminderNotification;
use Capell\Tests\Fixtures\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('persists scheduler events when metadata is saved', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    $workspace = Workspace::factory()->approved()->create();

    SetWorkspaceSchedulerMetadataAction::run($workspace, [
        'unpublish_at' => CarbonImmutable::parse('2026-05-10 09:00:00', 'UTC'),
        'review_reminder_at' => CarbonImmutable::parse('2026-05-02 09:00:00', 'UTC'),
    ]);

    $eventTypes = SchedulerEvent::query()
        ->where('workspace_id', $workspace->id)
        ->get()
        ->map(fn (SchedulerEvent $event): string => $event->event_type->value)
        ->all();

    expect($eventTypes)
        ->toContain(SchedulerEventTypeEnum::Unpublish->value)
        ->toContain(SchedulerEventTypeEnum::ReviewReminder->value);
});

it('rejects unpublish before publish', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    $workspace = Workspace::factory()->scheduled('2026-05-10 09:00:00')->create();

    SetWorkspaceSchedulerMetadataAction::run($workspace, [
        'unpublish_at' => CarbonImmutable::parse('2026-05-09 09:00:00', 'UTC'),
    ]);
})->throws(InvalidSchedulerMetadataException::class);

it('expires public visibility without deleting the published page', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-12 09:00:00', 'UTC'));

    $workspace = Workspace::factory()->published()->create();
    $page = Page::factory()->create([
        'name' => 'Campaign page',
        'visible_from' => CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'),
        'visible_until' => null,
    ]);

    Version::query()->create([
        'uuid' => (string) Str::uuid(),
        'number' => 10,
        'name' => 'Campaign',
        'is_live' => true,
        'manifest' => [Page::class => [$page->id]],
        'source_workspace_id' => $workspace->id,
        'published_at' => CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'),
    ]);

    ExpireWorkspacePublicVisibilityAction::run($workspace, CarbonImmutable::parse('2026-05-12 09:00:00', 'UTC'));

    expect($page->fresh())->not->toBeNull()
        ->and($page->fresh()->visible_until?->toDateTimeString())->toBe('2026-05-12 09:00:00');
});

it('executes unpublish scheduler events idempotently', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-12 09:00:00', 'UTC'));

    $workspace = Workspace::factory()->published()->create();
    $page = Page::factory()->create(['visible_until' => null]);

    Version::query()->create([
        'uuid' => (string) Str::uuid(),
        'number' => 11,
        'name' => 'Campaign',
        'is_live' => true,
        'manifest' => [Page::class => [$page->id]],
        'source_workspace_id' => $workspace->id,
        'published_at' => CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'),
    ]);

    $event = SchedulerEvent::query()->create([
        'event_type' => SchedulerEventTypeEnum::Unpublish,
        'state' => SchedulerEventStateEnum::Scheduled,
        'source_type' => $workspace->getMorphClass(),
        'source_id' => $workspace->id,
        'workspace_id' => $workspace->id,
        'scheduled_for' => CarbonImmutable::parse('2026-05-12 08:59:00', 'UTC'),
        'schedule_version' => $workspace->updated_at?->getTimestamp() ?? now()->getTimestamp(),
        'idempotency_key' => 'test-unpublish-' . $workspace->id,
    ]);

    ExecuteSchedulerEventAction::run($event);
    ExecuteSchedulerEventAction::run($event->fresh());

    expect($event->state)->toBe(SchedulerEventStateEnum::Executed)
        ->and($event->failure_count)->toBe(0)
        ->and($page->fresh()->visible_until)->not->toBeNull();
});

it('does not duplicate synced workspace events with legacy scheduler columns', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    $workspace = Workspace::factory()->scheduled('2026-05-04 09:00:00')->create([
        'name' => 'Synced campaign',
    ]);

    SyncWorkspaceSchedulerEventsAction::run($workspace);

    $events = BuildContentSchedulerEventsAction::run(
        eventType: SchedulerEventTypeEnum::Publish,
        sourceType: 'workspace',
    );

    expect($events->filter(fn (SchedulerEventData $event): bool => $event->title === 'Synced campaign'))->toHaveCount(1);
});

it('does not resurrect executed due events from legacy due columns', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-12 09:00:00', 'UTC'));

    $workspace = Workspace::factory()->published()->create([
        'unpublish_at' => CarbonImmutable::parse('2026-05-12 08:00:00', 'UTC'),
    ]);
    $page = Page::factory()->create(['visible_until' => null]);

    Version::query()->create([
        'uuid' => (string) Str::uuid(),
        'number' => 12,
        'name' => 'Campaign',
        'is_live' => true,
        'manifest' => [Page::class => [$page->id]],
        'source_workspace_id' => $workspace->id,
        'published_at' => CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'),
    ]);

    SyncWorkspaceSchedulerEventsAction::run($workspace);
    RunDueSchedulerEventsAction::run(includePublish: false);
    RunDueSchedulerEventsAction::run(includePublish: false);

    $event = SchedulerEvent::query()
        ->where('workspace_id', $workspace->id)
        ->where('event_type', SchedulerEventTypeEnum::Unpublish->value)
        ->firstOrFail();

    expect($event->state)->toBe(SchedulerEventStateEnum::Executed)
        ->and($event->last_succeeded_at)->not->toBeNull()
        ->and($event->failure_count)->toBe(0);
});

it('excludes unsafe legacy workspace events from scoped iCal feeds', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    Workspace::factory()->scheduled('2026-05-04 09:00:00')->create([
        'name' => 'Legacy global campaign',
    ]);

    SchedulerIcalToken::query()->create([
        'token_hash' => hash('sha256', 'scoped-token'),
        'scope' => SchedulerIcalFeedScopeEnum::Mine,
        'owner_type' => 'editor',
        'owner_id' => 123,
    ]);

    $feed = BuildSchedulerIcalFeedAction::run(SchedulerIcalToken::query()->firstOrFail());

    expect($feed)->not->toContain('Legacy global campaign');
});

it('protects the public iCal route with tokens, revocation, and conditional responses', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    $workspace = Workspace::factory()->create(['name' => 'Feed campaign']);
    Permission::findOrCreate('ViewAny:Workspace', 'web');
    $owner = test()->createUserWithPermission('ViewAny:Workspace');
    SchedulerEvent::query()->create([
        'event_type' => SchedulerEventTypeEnum::Publish,
        'state' => SchedulerEventStateEnum::Scheduled,
        'source_type' => $workspace->getMorphClass(),
        'source_id' => $workspace->id,
        'workspace_id' => $workspace->id,
        'owner_type' => $owner->getMorphClass(),
        'owner_id' => $owner->id,
        'scheduled_for' => CarbonImmutable::parse('2026-05-03 09:00:00', 'UTC'),
        'idempotency_key' => 'feed-event',
    ]);
    $token = SchedulerIcalToken::query()->create([
        'token_hash' => hash('sha256', 'validfeedtoken'),
        'scope' => SchedulerIcalFeedScopeEnum::Mine,
        'owner_type' => $owner->getMorphClass(),
        'owner_id' => $owner->id,
    ]);

    $this->get('/capell/publishing-studio/scheduler/ical/missing')->assertNotFound();

    $response = $this->get('/capell/publishing-studio/scheduler/ical/validfeedtoken')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/calendar; charset=UTF-8');

    expect($response->getContent())->toContain('BEGIN:VCALENDAR')
        ->and($token->fresh()->last_used_at)->not->toBeNull();

    $this->withHeader('If-None-Match', $response->baseResponse->headers->get('ETag'))
        ->get('/capell/publishing-studio/scheduler/ical/validfeedtoken')
        ->assertStatus(304);

    $token->revoked_at = CarbonImmutable::now();
    $token->save();

    $this->get('/capell/publishing-studio/scheduler/ical/validfeedtoken')->assertNotFound();
});

it('sends review reminders once per recipient', function (): void {
    Notification::fake();
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    $reviewer = User::factory()->create();
    $workspace = Workspace::factory()->inReview()->create(['name' => 'Review campaign']);
    WorkspaceReviewAssignment::factory()->create([
        'workspace_id' => $workspace->id,
        'reviewer_type' => $reviewer->getMorphClass(),
        'reviewer_id' => $reviewer->id,
    ]);
    $event = SchedulerEvent::query()->create([
        'event_type' => SchedulerEventTypeEnum::ReviewReminder,
        'state' => SchedulerEventStateEnum::Scheduled,
        'source_type' => $workspace->getMorphClass(),
        'source_id' => $workspace->id,
        'workspace_id' => $workspace->id,
        'scheduled_for' => CarbonImmutable::parse('2026-05-01 08:00:00', 'UTC'),
        'idempotency_key' => 'review-reminder',
    ]);

    SendWorkspaceReviewReminderAction::run($event);
    SendWorkspaceReviewReminderAction::run($event);

    Notification::assertSentToTimes($reviewer, WorkspaceReviewReminderNotification::class, 1);
});
