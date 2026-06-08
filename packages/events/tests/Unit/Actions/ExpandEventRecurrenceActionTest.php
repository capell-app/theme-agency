<?php

declare(strict_types=1);

use Capell\Events\Actions\ExpandEventRecurrenceAction;
use Capell\Events\Data\EventOccurrenceData;
use Capell\Events\Models\Event;
use Carbon\CarbonImmutable;

it('expands practical RRULE occurrences inside a range', function (): void {
    $event = Event::factory()->make([
        'name' => 'Weekly clinic',
        'starts_at' => CarbonImmutable::parse('2026-06-01 10:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-06-01 12:00:00', 'UTC'),
        'timezone' => 'UTC',
        'recurrence_rule' => 'FREQ=WEEKLY;COUNT=3',
    ]);

    $occurrences = ExpandEventRecurrenceAction::run(
        $event,
        CarbonImmutable::parse('2026-06-01 00:00:00', 'UTC'),
        CarbonImmutable::parse('2026-06-30 23:59:59', 'UTC'),
    );

    expect($occurrences)->toHaveCount(3)
        ->and($occurrences->pluck('occurrenceKey')->all())->toBe([
            '20260601T100000',
            '20260608T100000',
            '20260615T100000',
        ]);
});
it('keeps recurring event wall time stable across DST changes', function (): void {
    $event = new Event([
        'starts_at' => CarbonImmutable::parse('2026-03-22 09:00:00', 'Europe/London'),
        'ends_at' => CarbonImmutable::parse('2026-03-22 10:00:00', 'Europe/London'),
        'timezone' => 'Europe/London',
        'recurrence_rule' => 'FREQ=WEEKLY;COUNT=3',
    ]);

    $occurrences = ExpandEventRecurrenceAction::run(
        $event,
        CarbonImmutable::parse('2026-03-20 00:00:00', 'Europe/London'),
        CarbonImmutable::parse('2026-04-10 00:00:00', 'Europe/London'),
    );

    expect($occurrences)->toHaveCount(3)
        ->and($occurrences->map(fn (EventOccurrenceData $occurrence): string => $occurrence->startsAt->format('H:i'))->all())->toBe([
            '09:00',
            '09:00',
            '09:00',
        ])
        ->and($occurrences->pluck('occurrenceKey')->all())->toBe([
            '20260322T090000',
            '20260329T090000',
            '20260405T090000',
        ]);
});
