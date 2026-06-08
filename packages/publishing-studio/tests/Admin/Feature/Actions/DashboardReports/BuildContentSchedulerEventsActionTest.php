<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\PublishingStudio\Actions\DashboardReports\BuildContentSchedulerEventsAction;
use Capell\PublishingStudio\Data\SchedulerEventData;
use Capell\PublishingStudio\Enums\SchedulerEventStateEnum;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Capell\PublishingStudio\Models\SchedulerEvent;
use Capell\PublishingStudio\Models\Workspace;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('returns calendar-ready scheduler events for pages and publishing-studio', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    Page::factory()->create([
        'name' => 'Spring launch page',
        'visible_from' => CarbonImmutable::parse('2026-05-02 10:00:00', 'UTC'),
    ]);
    Page::factory()->create([
        'name' => 'Campaign landing page',
        'visible_from' => CarbonImmutable::parse('2026-04-01 10:00:00', 'UTC'),
        'visible_until' => CarbonImmutable::parse('2026-05-03 17:00:00', 'UTC'),
    ]);
    Workspace::factory()->scheduled('2026-05-04 09:00:00')->create([
        'name' => 'Summer campaign',
        'unpublish_at' => CarbonImmutable::parse('2026-05-20 17:00:00', 'UTC'),
        'embargo_until' => CarbonImmutable::parse('2026-05-03 09:00:00', 'UTC'),
        'review_reminder_at' => CarbonImmutable::parse('2026-05-02 12:00:00', 'UTC'),
    ]);

    $events = BuildContentSchedulerEventsAction::run();

    expect($events)->toHaveCount(6)
        ->and($events->pluck('eventType')->map(fn (mixed $eventType): string => $eventType instanceof SchedulerEventTypeEnum ? $eventType->value : '')->all())
        ->toBe([
            SchedulerEventTypeEnum::Publish->value,
            SchedulerEventTypeEnum::ReviewReminder->value,
            SchedulerEventTypeEnum::Embargo->value,
            SchedulerEventTypeEnum::Unpublish->value,
            SchedulerEventTypeEnum::Publish->value,
            SchedulerEventTypeEnum::Unpublish->value,
        ])
        ->and($events->first(fn (SchedulerEventData $event): bool => $event->title === 'Summer campaign' && $event->eventType === SchedulerEventTypeEnum::Unpublish)?->description)
        ->toContain('expires automatically')
        ->and(publishingStudioFirstSchedulerEvent($events)->title)->toBe('Spring launch page');
});

test('filters scheduler events by type and source', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    Page::factory()->create([
        'name' => 'Page publish',
        'visible_from' => CarbonImmutable::parse('2026-05-02 10:00:00', 'UTC'),
    ]);
    Workspace::factory()->scheduled('2026-05-04 09:00:00')->create([
        'name' => 'Workspace publish',
    ]);

    $events = BuildContentSchedulerEventsAction::run(
        eventType: SchedulerEventTypeEnum::Publish,
        sourceType: 'workspace',
    );

    $event = publishingStudioFirstSchedulerEvent($events);

    expect($events)->toHaveCount(1)
        ->and($event->sourceType)->toBe('workspace')
        ->and($event->title)->toBe('Workspace publish');
});

test('state filters only return durable events in that state', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    Page::factory()->create([
        'name' => 'Scheduled page',
        'visible_from' => CarbonImmutable::parse('2026-05-02 10:00:00', 'UTC'),
    ]);
    Workspace::factory()->scheduled('2026-05-04 09:00:00')->create([
        'name' => 'Legacy workspace',
    ]);
    $workspace = Workspace::factory()->create(['name' => 'Failed workspace']);

    SchedulerEvent::query()->create([
        'event_type' => SchedulerEventTypeEnum::Publish,
        'state' => SchedulerEventStateEnum::Failed,
        'source_type' => $workspace->getMorphClass(),
        'source_id' => $workspace->id,
        'workspace_id' => $workspace->id,
        'scheduled_for' => CarbonImmutable::parse('2026-05-03 09:00:00', 'UTC'),
        'idempotency_key' => 'failed-workspace',
    ]);

    $events = BuildContentSchedulerEventsAction::run(state: SchedulerEventStateEnum::Failed);

    $event = publishingStudioFirstSchedulerEvent($events);

    expect($events)->toHaveCount(1)
        ->and($event->title)->toBe('Failed workspace')
        ->and($event->state)->toBe(SchedulerEventStateEnum::Failed);
});

/**
 * @param  Collection<int, SchedulerEventData>  $events
 */
function publishingStudioFirstSchedulerEvent(Collection $events): SchedulerEventData
{
    $event = $events->first();

    throw_unless($event instanceof SchedulerEventData);

    return $event;
}
