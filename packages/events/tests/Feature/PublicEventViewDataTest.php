<?php

declare(strict_types=1);

use Capell\Events\Data\EventOccurrenceViewData;
use Capell\Events\Support\Calendar\CalendarWeek;
use Capell\Events\Tests\TestCase;
use Carbon\CarbonImmutable;

uses(TestCase::class);

it('renders the public event listing from hydrated view data without model identifiers', function (): void {
    $html = view('capell-events::livewire.page.events-listing', [
        'results' => collect([
            new EventOccurrenceViewData(
                title: 'Public workshop',
                isoStartsAt: '2026-06-10T09:00:00+00:00',
                displayStartsAt: '10 June 2026 09:00',
                eventTimezone: 'UTC',
                viewerDisplayStartsAt: '10 June 2026 10:00',
                viewerTimezone: 'Europe/London',
                url: 'https://example.test/events/workshop/2026-06-10',
                venueName: 'Main Hall',
                startsAt: CarbonImmutable::parse('2026-06-10 09:00:00', 'UTC'),
            ),
        ]),
    ])->render();

    expect($html)->toContain('Public workshop')
        ->and($html)->toContain('Main Hall')
        ->and($html)->toContain('Your time')
        ->and($html)->toContain('Europe/London')
        ->and($html)->not->toContain('event_id')
        ->and($html)->not->toContain('model_id')
        ->and($html)->not->toContain('/admin');
});

it('renders the public event calendar from hydrated view data without admin labels', function (): void {
    $day = CarbonImmutable::parse('2026-06-10 00:00:00', 'UTC');

    $html = view('capell-events::livewire.event-calendar', [
        'monthDate' => CarbonImmutable::parse('2026-06-01 00:00:00', 'UTC'),
        'weeks' => collect([
            new CalendarWeek(collect([$day])),
        ]),
        'occurrences' => collect([
            new EventOccurrenceViewData(
                title: 'Calendar workshop',
                isoStartsAt: '2026-06-10T09:00:00+00:00',
                displayStartsAt: '10 June 2026 09:00',
                eventTimezone: 'UTC',
                url: 'https://example.test/events/workshop/2026-06-10',
                startsAt: CarbonImmutable::parse('2026-06-10 09:00:00', 'UTC'),
            ),
        ]),
    ])->render();

    expect($html)->toContain('Calendar workshop')
        ->and($html)->toContain(__('capell-events::generic.event_calendar'))
        ->and($html)->not->toContain(__('capell-events::generic.admin_calendar'))
        ->and($html)->not->toContain('event_id')
        ->and($html)->not->toContain('/admin');
});
