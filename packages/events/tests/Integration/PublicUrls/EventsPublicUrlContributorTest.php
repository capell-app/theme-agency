<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\SiteDomain;
use Capell\Events\Enums\EventVisibilityEnum;
use Capell\Events\Models\Event;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Providers\EventsServiceProvider;
use Capell\Events\Support\PublicUrls\EventsPublicUrlContributor;
use Capell\SiteDiscovery\Contracts\PublicUrlContributor;
use Capell\SiteDiscovery\Data\PublicUrlData;
use Carbon\CarbonImmutable;

it('contributes public event occurrence URLs to the public URL registry contract', function (): void {
    $event = Event::factory()->create([
        'visibility' => EventVisibilityEnum::Public,
        'visible_from' => CarbonImmutable::now()->subDay(),
        'visible_until' => null,
    ]);
    $site = $event->site;
    $language = Language::query()->firstOrFail();
    SiteDomain::factory()->for($site)->for($language)->default()->create([
        'domain' => 'events.example.test',
        'scheme' => 'https',
    ]);

    PageUrl::factory()
        ->page($event)
        ->site($site)
        ->language($language)
        ->state(['url' => '/events/community-event'])
        ->create();

    $occurrence = EventOccurrence::factory()->for($event, 'event')->create([
        'starts_at' => CarbonImmutable::now()->addWeek()->setTime(10, 0),
        'occurrence_key' => '20260610T100000',
    ]);

    $urls = (new EventsPublicUrlContributor)->publicUrls();
    $expectedUrl = $occurrence->refresh()->load('event.pageUrl')->occurrenceUrl();

    expect($urls->first())->toBeInstanceOf(PublicUrlData::class)
        ->and($urls->pluck('canonicalUrl')->all())->toContain($expectedUrl)
        ->and($urls->first()?->sourcePackage)->toBe(EventsServiceProvider::$packageName);
});

it('registers the events public URL contributor when Site Discovery is available', function (): void {
    $contributors = collect(app()->tagged(PublicUrlContributor::TAG));

    expect($contributors->contains(fn (mixed $contributor): bool => $contributor instanceof EventsPublicUrlContributor))->toBeTrue();
});
