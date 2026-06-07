<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Events\Data\EventOccurrenceViewData;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Models\EventVenue;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildEventOccurrenceViewDataAction
{
    use AsAction;

    public function handle(EventOccurrence $occurrence): EventOccurrenceViewData
    {
        $startsAt = $occurrence->starts_at->setTimezone($occurrence->timezone);
        $event = $occurrence->event;
        $venue = $occurrence->venue;

        return new EventOccurrenceViewData(
            title: $event->translation?->title ?? $event->name,
            isoStartsAt: $occurrence->starts_at->toIso8601String(),
            displayStartsAt: $startsAt->format('j F Y H:i'),
            url: $occurrence->occurrenceUrl(),
            venueName: $venue instanceof EventVenue ? $venue->name : null,
            startsAt: $startsAt,
        );
    }
}
