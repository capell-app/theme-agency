<?php

declare(strict_types=1);

use Capell\Core\Models\Site;
use Capell\Events\Enums\EventOccurrenceStatusEnum;
use Capell\Events\Models\Event;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Providers\EventsServiceProvider;
use Capell\Events\Support\EditorialCalendar\EventsEditorialCalendarEventContributor;
use Capell\PublishingStudio\Actions\BuildEditorialCalendarEventsAction;
use Capell\PublishingStudio\Contracts\EditorialCalendarEventContributor;
use Capell\PublishingStudio\Data\EditorialCalendarEventData;
use Capell\PublishingStudio\Data\EditorialCalendarQueryData;
use Carbon\CarbonImmutable;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('contributes event occurrences to the editorial calendar', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    $event = Event::factory()->create([
        'name' => 'Community clinic',
        'starts_at' => CarbonImmutable::parse('2026-05-15 10:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-05-15 12:00:00', 'UTC'),
        'timezone' => 'Europe/London',
    ]);

    $site = $event->site;

    $occurrence = EventOccurrence::factory()->for($event, 'event')->create([
        'occurrence_key' => '20260515T100000',
        'starts_at' => CarbonImmutable::parse('2026-05-15 10:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-05-15 12:00:00', 'UTC'),
        'timezone' => 'Europe/London',
        'status' => EventOccurrenceStatusEnum::Scheduled,
    ]);

    EventOccurrence::factory()->create([
        'starts_at' => CarbonImmutable::parse('2026-06-16 10:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-06-16 12:00:00', 'UTC'),
    ]);

    $events = (new EventsEditorialCalendarEventContributor)->editorialCalendarEvents(new EditorialCalendarQueryData(
        startsAt: CarbonImmutable::parse('2026-05-01 00:00:00', 'UTC'),
        endsAt: CarbonImmutable::parse('2026-05-31 23:59:59', 'UTC'),
        sourceTypes: ['event'],
        eventTypes: ['event.occurrence'],
        siteIds: [(int) $site->id],
        state: EventOccurrenceStatusEnum::Scheduled->value,
    ));

    $firstEvent = $events->first();

    if (! $firstEvent instanceof EditorialCalendarEventData) {
        throw new RuntimeException('Expected the events contributor to return an editorial calendar event.');
    }

    expect($events)->toHaveCount(1)
        ->and($firstEvent->id)->toBe('event-occurrence-' . $occurrence->id)
        ->and($firstEvent->sourcePackage)->toBe(EventsServiceProvider::$packageName)
        ->and($firstEvent->sourceType)->toBe('event')
        ->and($firstEvent->sourceId)->toBe((string) $occurrence->id)
        ->and($firstEvent->title)->toBe('Community clinic')
        ->and($firstEvent->eventType)->toBe('event.occurrence')
        ->and($firstEvent->state)->toBe(EventOccurrenceStatusEnum::Scheduled->value)
        ->and($firstEvent->siteId)->toBe((int) $site->id)
        ->and($firstEvent->timezone)->toBe('Europe/London')
        ->and($firstEvent->metadata['occurrence_key'])->toBe('20260515T100000');
});

it('is included by the publishing studio editorial calendar aggregator', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    $site = Site::factory()->withTranslations()->create();
    $event = Event::factory()->create([
        'site_id' => $site->id,
        'name' => 'Aggregator event',
    ]);

    EventOccurrence::factory()->for($event, 'event')->create([
        'starts_at' => CarbonImmutable::parse('2026-05-10 10:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-05-10 12:00:00', 'UTC'),
        'status' => EventOccurrenceStatusEnum::Scheduled,
    ]);

    $events = BuildEditorialCalendarEventsAction::run(
        startsAt: CarbonImmutable::parse('2026-05-01 00:00:00', 'UTC'),
        endsAt: CarbonImmutable::parse('2026-05-31 23:59:59', 'UTC'),
        sourceTypes: ['event'],
        eventTypes: ['event.occurrence'],
        siteIds: [(int) $site->id],
        state: EventOccurrenceStatusEnum::Scheduled->value,
    );

    $firstEvent = $events->first();

    if (! $firstEvent instanceof EditorialCalendarEventData) {
        throw new RuntimeException('Expected the aggregator to return an event editorial calendar event.');
    }

    expect($events)->toHaveCount(1)
        ->and($firstEvent->sourcePackage)->toBe(EventsServiceProvider::$packageName)
        ->and($firstEvent->sourceType)->toBe('event')
        ->and($firstEvent->title)->toBe('Aggregator event');
});

it('registers the events editorial calendar contributor when publishing studio is available', function (): void {
    $contributors = collect(app()->tagged(EditorialCalendarEventContributor::TAG));

    expect($contributors->contains(fn (mixed $contributor): bool => $contributor instanceof EventsEditorialCalendarEventContributor))->toBeTrue();
});
