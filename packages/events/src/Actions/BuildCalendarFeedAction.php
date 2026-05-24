<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Core\Models\Site;
use Capell\Events\Models\EventOccurrence;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event as CalendarEvent;

/**
 * @method static string run(Site $site, ?CarbonImmutable $startsAt = null, ?CarbonImmutable $endsAt = null)
 */
class BuildCalendarFeedAction
{
    use AsAction;

    public function handle(Site $site, ?CarbonImmutable $startsAt = null, ?CarbonImmutable $endsAt = null): string
    {
        $startsAt ??= CarbonImmutable::now()->subWeek();
        $endsAt ??= CarbonImmutable::now()->addYear();

        $calendar = Calendar::create((string) ($site->translation->title ?? $site->name ?? __('capell-events::generic.events')))
            ->productIdentifier('-//Capell//Events//EN')
            ->refreshInterval(60);

        QueryPublicEventOccurrencesAction::run($site, $startsAt, $endsAt)
            ->each(function (EventOccurrence $occurrence) use ($calendar): void {
                $calendar->event($this->calendarEvent($occurrence));
            });

        return $calendar->get();
    }

    private function calendarEvent(EventOccurrence $occurrence): CalendarEvent
    {
        $event = CalendarEvent::create($occurrence->event->translation->title ?? $occurrence->event->name)
            ->uniqueIdentifier(sprintf('event-%s-occurrence-%s@capell', $occurrence->event_id, $occurrence->occurrence_key))
            ->startsAt($occurrence->starts_at->toDateTimeImmutable(), ! $occurrence->all_day);

        if ($occurrence->ends_at !== null) {
            $event->endsAt($occurrence->ends_at->toDateTimeImmutable(), ! $occurrence->all_day);
        }

        $url = $occurrence->occurrenceUrl();
        if ($url !== null) {
            $event->url($url);
        }

        if ($occurrence->venue?->full_address !== null && $occurrence->venue->full_address !== '') {
            $event->address($occurrence->venue->full_address, $occurrence->venue->name);
        }

        return $event;
    }
}
