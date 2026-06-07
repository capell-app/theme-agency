<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Events\Data\EventOccurrenceViewData;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Models\EventVenue;
use DateTimeZone;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildEventOccurrenceViewDataAction
{
    use AsAction;

    public function handle(EventOccurrence $occurrence, ?string $viewerTimezone = null): EventOccurrenceViewData
    {
        $startsAt = $occurrence->starts_at->setTimezone($occurrence->timezone);
        $resolvedViewerTimezone = $this->validTimezone($viewerTimezone);
        $viewerStartsAt = $resolvedViewerTimezone === null
            ? null
            : $occurrence->starts_at->setTimezone($resolvedViewerTimezone);
        $event = $occurrence->event;
        $venue = $occurrence->venue;

        return new EventOccurrenceViewData(
            title: $event->translation?->title ?? $event->name,
            isoStartsAt: $occurrence->starts_at->toIso8601String(),
            displayStartsAt: $startsAt->format('j F Y H:i'),
            eventTimezone: $occurrence->timezone,
            viewerDisplayStartsAt: $viewerStartsAt?->format('j F Y H:i'),
            viewerTimezone: $resolvedViewerTimezone,
            url: $occurrence->occurrenceUrl(),
            venueName: $venue instanceof EventVenue ? $venue->name : null,
            startsAt: $startsAt,
        );
    }

    private function validTimezone(?string $timezone): ?string
    {
        if ($timezone === null || $timezone === '') {
            return null;
        }

        return in_array($timezone, DateTimeZone::listIdentifiers(), true) ? $timezone : null;
    }
}
