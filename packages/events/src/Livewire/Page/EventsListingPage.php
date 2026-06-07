<?php

declare(strict_types=1);

namespace Capell\Events\Livewire\Page;

use Capell\Core\Models\Site;
use Capell\Events\Actions\BuildEventOccurrenceViewDataAction;
use Capell\Events\Actions\QueryPublicEventOccurrencesAction;
use Capell\Events\Data\EventOccurrenceViewData;
use Capell\Events\Models\EventOccurrence;
use Capell\Frontend\Facades\Frontend;
use Capell\Frontend\Livewire\Page\AbstractPage;
use Carbon\CarbonImmutable;
use DateTimeZone;
use RuntimeException;

class EventsListingPage extends AbstractPage
{
    protected static string $defaultView = 'capell-events::livewire.page.events-listing';

    protected function setup(): void
    {
        $now = CarbonImmutable::now();
        $site = Frontend::site();

        throw_unless($site instanceof Site, RuntimeException::class, 'Events listing requires a frontend site context.');

        $this->results = QueryPublicEventOccurrencesAction::run(
            $site,
            $now->subDay(),
            $now->addYear(),
            (int) (Frontend::page()->meta['limit'] ?? config('capell-frontend.pagination_limit', 12)),
        )
            ->map(fn (EventOccurrence $occurrence): EventOccurrenceViewData => BuildEventOccurrenceViewDataAction::run($occurrence, $this->viewerTimezone()))
            ->values();
    }

    private function viewerTimezone(): ?string
    {
        $timezone = request()->query('timezone');

        if (! is_string($timezone) || $timezone === '') {
            $timezone = Frontend::page()->meta['viewer_timezone']
                ?? Frontend::page()->meta['default_timezone']
                ?? config('capell-events.display.default_timezone');
        }

        return is_string($timezone) && in_array($timezone, DateTimeZone::listIdentifiers(), true) ? $timezone : null;
    }
}
