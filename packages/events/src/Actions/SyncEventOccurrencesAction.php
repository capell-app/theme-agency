<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Events\Data\EventOccurrenceData;
use Capell\Events\Models\Event;
use Capell\Events\Models\EventOccurrence;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Collection<int, EventOccurrence> run(Event $event, ?CarbonImmutable $startsAt = null, ?CarbonImmutable $endsAt = null)
 */
class SyncEventOccurrencesAction
{
    use AsAction;

    /**
     * @return Collection<int, EventOccurrence>
     */
    public function handle(Event $event, ?CarbonImmutable $startsAt = null, ?CarbonImmutable $endsAt = null): Collection
    {
        $startsAt ??= CarbonImmutable::now($event->timezone)->subDays($this->syncPastDays());
        $endsAt ??= CarbonImmutable::now($event->timezone)->addDays($this->syncHorizonDays());

        return ExpandEventRecurrenceAction::run($event, $startsAt, $endsAt)
            ->map(fn (EventOccurrenceData $occurrenceData): EventOccurrence => $this->syncOccurrence($event, $occurrenceData))
            ->values();
    }

    private function syncOccurrence(Event $event, EventOccurrenceData $occurrenceData): EventOccurrence
    {
        /** @var EventOccurrence $occurrence */
        $occurrence = EventOccurrence::query()->firstOrNew([
            'event_id' => $event->getKey(),
            'occurrence_key' => $occurrenceData->occurrenceKey,
        ]);

        if ($occurrence->is_override) {
            return $occurrence;
        }

        $occurrence->forceFill([
            'event_venue_id' => $event->event_venue_id,
            'starts_at' => $occurrenceData->startsAt,
            'ends_at' => $occurrenceData->endsAt,
            'timezone' => $event->timezone,
            'all_day' => $event->all_day,
            'status' => 'scheduled',
            'visibility' => $event->visibility,
            'location_mode' => $event->location_mode,
            'booking_mode' => $event->booking_mode,
            'booking_url' => $event->booking_url,
            'booking_label' => $event->booking_label,
            'capacity' => $event->capacity,
            'waitlist_enabled' => $event->waitlist_enabled,
        ])->save();

        return $occurrence;
    }
    private function syncPastDays(): int
    {
        $days = config('capell-events.recurrence.sync_past_days', 31);

        return is_numeric($days) ? max(0, (int) $days) : 31;
    }

    private function syncHorizonDays(): int
    {
        $days = config('capell-events.recurrence.sync_horizon_days', 365);

        return is_numeric($days) ? max(1, (int) $days) : 365;
    }
}
