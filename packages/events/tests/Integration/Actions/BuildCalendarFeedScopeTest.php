<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Events\Actions\BuildCalendarFeedAction;
use Capell\Events\Models\Event;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Models\EventVenue;
use Carbon\CarbonImmutable;

it('filters listing page calendar feeds by configured venue metadata', function (): void {
    $site = Site::factory()->create();
    $includedVenue = EventVenue::factory()->create();
    $excludedVenue = EventVenue::factory()->create();
    $listingPage = Page::factory()->create([
        'site_id' => $site->getKey(),
        'meta' => [
            'event_venue_id' => $includedVenue->getKey(),
        ],
    ]);

    $includedEvent = Event::factory()->create([
        'site_id' => $site->getKey(),
        'event_venue_id' => $includedVenue->getKey(),
        'name' => 'Included workshop',
    ]);
    $excludedEvent = Event::factory()->create([
        'site_id' => $site->getKey(),
        'event_venue_id' => $excludedVenue->getKey(),
        'name' => 'Excluded workshop',
    ]);

    EventOccurrence::factory()->create([
        'event_id' => $includedEvent->getKey(),
        'event_venue_id' => $includedVenue->getKey(),
        'starts_at' => CarbonImmutable::parse('2026-06-10 10:00:00', 'UTC'),
    ]);
    EventOccurrence::factory()->create([
        'event_id' => $excludedEvent->getKey(),
        'event_venue_id' => $excludedVenue->getKey(),
        'starts_at' => CarbonImmutable::parse('2026-06-11 10:00:00', 'UTC'),
    ]);

    $feed = BuildCalendarFeedAction::run(
        site: $site,
        startsAt: CarbonImmutable::parse('2026-06-01 00:00:00', 'UTC'),
        endsAt: CarbonImmutable::parse('2026-06-30 23:59:59', 'UTC'),
        listingPage: $listingPage,
    );

    expect($feed)->toContain('Included workshop')
        ->and($feed)->not->toContain('Excluded workshop');
});
