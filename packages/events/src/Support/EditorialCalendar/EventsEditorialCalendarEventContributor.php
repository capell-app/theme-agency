<?php

declare(strict_types=1);

namespace Capell\Events\Support\EditorialCalendar;

use Capell\Events\Filament\Resources\Occurrences\EventOccurrenceResource;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Providers\EventsServiceProvider;
use Capell\PublishingStudio\Contracts\EditorialCalendarEventContributor;
use Capell\PublishingStudio\Data\EditorialCalendarEventData;
use Capell\PublishingStudio\Data\EditorialCalendarQueryData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

final class EventsEditorialCalendarEventContributor implements EditorialCalendarEventContributor
{
    private const string EVENT_TYPE = 'event.occurrence';

    /**
     * @return Collection<int, EditorialCalendarEventData>
     */
    public function editorialCalendarEvents(EditorialCalendarQueryData $query): Collection
    {
        if (! $this->shouldContribute($query)) {
            return collect();
        }

        return EventOccurrence::query()
            ->with(['event.site'])
            ->where('starts_at', '<=', $query->endsAt)
            ->where(function (Builder $builder) use ($query): void {
                $builder->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $query->startsAt);
            })
            ->when($query->state !== null, fn (Builder $builder): Builder => $builder->where('status', $query->state))
            ->when(
                $query->siteIds !== null,
                fn (Builder $builder): Builder => $builder->whereHas(
                    'event',
                    fn (Builder $eventBuilder): Builder => $eventBuilder->whereIn('site_id', $query->siteIds),
                ),
            )
            ->oldest('starts_at')
            ->limit($query->limit)
            ->get()
            ->map(fn (EventOccurrence $occurrence): EditorialCalendarEventData => $this->occurrenceEvent($occurrence));
    }

    private function shouldContribute(EditorialCalendarQueryData $query): bool
    {
        if ($query->sourceTypes !== null && ! in_array('event', $query->sourceTypes, true)) {
            return false;
        }

        if ($query->eventTypes !== null && ! in_array(self::EVENT_TYPE, $query->eventTypes, true)) {
            return false;
        }

        return $query->ownerId === null && $query->ownerType === null;
    }

    private function occurrenceEvent(EventOccurrence $occurrence): EditorialCalendarEventData
    {
        $event = $occurrence->event;

        return new EditorialCalendarEventData(
            id: 'event-occurrence-' . $occurrence->id,
            sourcePackage: EventsServiceProvider::$packageName,
            sourceType: 'event',
            sourceId: (string) $occurrence->getKey(),
            title: $event->name,
            eventType: self::EVENT_TYPE,
            startsAt: $occurrence->starts_at,
            endsAt: $occurrence->ends_at,
            eventTypeLabel: (string) __('capell-events::generic.editorial_calendar.event_types.occurrence'),
            status: (string) __('capell-events::generic.editorial_calendar.statuses.' . $occurrence->status->value),
            recordUrl: $this->recordUrl(),
            state: $occurrence->status->value,
            siteId: is_numeric($event->site_id) ? (int) $event->site_id : null,
            siteName: $event->site->name,
            timezone: $occurrence->timezone,
            isAllDay: $occurrence->all_day,
            color: $occurrence->status->value === 'cancelled' ? 'danger' : 'info',
            metadata: [
                'event_id' => $event->getKey(),
                'occurrence_key' => $occurrence->occurrence_key,
            ],
        );
    }

    private function recordUrl(): ?string
    {
        if (! Route::has('filament.admin.resources.event-occurrences.index')) {
            return null;
        }

        return EventOccurrenceResource::getUrl('index');
    }
}
