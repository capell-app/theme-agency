<?php

declare(strict_types=1);

use Capell\Events\Actions\SyncEventOccurrencesAction;
use Capell\Events\Models\Event;
use Capell\Events\Models\EventOccurrence;
use Carbon\CarbonImmutable;

it('materializes generated occurrences without replacing overrides', function (): void {
    $event = Event::factory()->create([
        'starts_at' => CarbonImmutable::parse('2026-06-01 10:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-06-01 12:00:00', 'UTC'),
        'timezone' => 'UTC',
        'recurrence_rule' => 'FREQ=DAILY;COUNT=2',
    ]);

    EventOccurrence::factory()->create([
        'event_id' => $event->id,
        'occurrence_key' => '20260601T100000',
        'starts_at' => CarbonImmutable::parse('2026-06-01 14:00:00', 'UTC'),
        'is_override' => true,
    ]);

    $occurrences = SyncEventOccurrencesAction::run(
        $event,
        CarbonImmutable::parse('2026-06-01 00:00:00', 'UTC'),
        CarbonImmutable::parse('2026-06-05 00:00:00', 'UTC'),
    );

    expect($occurrences)->toHaveCount(2)
        ->and(EventOccurrence::query()->where('event_id', $event->id)->count())->toBe(2)
        ->and(EventOccurrence::query()->where('occurrence_key', '20260601T100000')->firstOrFail()->starts_at->hour)->toBe(14);
});
