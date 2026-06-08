<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Events\Models\EventOccurrence;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event as CalendarEvent;

/**
 * @method static string run(Site $site, ?CarbonImmutable $startsAt = null, ?CarbonImmutable $endsAt = null, ?Page $listingPage = null)
 */
class BuildCalendarFeedAction
{
    use AsAction;

    public function handle(Site $site, ?CarbonImmutable $startsAt = null, ?CarbonImmutable $endsAt = null, ?Page $listingPage = null): string
    {
        $startsAt ??= CarbonImmutable::now()->subMonth();
        $endsAt ??= CarbonImmutable::now()->addYear();

        $calendar = Calendar::create('Events');

        $query = EventOccurrence::query()
            ->with(['event.translation', 'event.pageUrl', 'venue'])
            ->whereHas('event', function (Builder $query) use ($site): void {
                $query->where('site_id', $site->getKey())->where('visibility', 'public')->publishedDate();
            });

        if ($listingPage instanceof Page) {
            $query = $this->applyListingPageScope($query, $listingPage);
        }

        $query
            ->public()
            ->inRange($startsAt, $endsAt)
            ->ordered()
            ->get()
            ->each(function (EventOccurrence $occurrence) use ($calendar): void {
                $calendar->event($this->calendarEvent($occurrence));
            });

        return $calendar->get();
    }

    private function calendarEvent(EventOccurrence $occurrence): CalendarEvent
    {
        $event = CalendarEvent::create($occurrence->event->translation->title ?? $occurrence->event->name)
            ->uniqueIdentifier(sprintf('event-%s-occurrence-%s@capell', $occurrence->event_id, $occurrence->occurrence_key))
            ->startsAt($occurrence->starts_at->toDateTimeImmutable());

        if ($occurrence->ends_at !== null) {
            $event->endsAt($occurrence->ends_at->toDateTimeImmutable());
        }

        if ($occurrence->occurrenceUrl() !== null) {
            $event->url($occurrence->occurrenceUrl());
        }

        if ($occurrence->venue?->full_address !== null && $occurrence->venue->full_address !== '') {
            $event->address($occurrence->venue->full_address, $occurrence->venue->name);
        }

        return $event;
    }

    /**
     * @param  Builder<EventOccurrence>  $query
     * @return Builder<EventOccurrence>
     */
    private function applyListingPageScope(Builder $query, Page $listingPage): Builder
    {
        $venueIds = $this->ids($listingPage->meta['event_venue_id'] ?? $listingPage->meta['venue_id'] ?? []);
        $eventIds = $this->ids($listingPage->meta['event_ids'] ?? $listingPage->meta['event_id'] ?? []);

        return $query
            ->when($venueIds !== [], fn (Builder $query): Builder => $query->whereIn('event_venue_id', $venueIds))
            ->when($eventIds !== [], fn (Builder $query): Builder => $query->whereIn('event_id', $eventIds));
    }

    /**
     * @return list<int>
     */
    private function ids(mixed $value): array
    {
        $ids = [];

        foreach (Arr::wrap($value) as $id) {
            if (! is_numeric($id)) {
                continue;
            }

            $normalizedId = (int) $id;

            if ($normalizedId > 0) {
                $ids[] = $normalizedId;
            }
        }

        return $ids;
    }
}
