<?php

declare(strict_types=1);

use Capell\Core\Enums\MediaCollectionEnum;
use Capell\Core\Enums\PageOrderEnum;
use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Events\Enums\EventNotificationTypeEnum;
use Capell\Events\Filament\Resources\Events\Tables\EventsTable;
use Capell\Events\Filament\Resources\Venues\EventVenueResource;
use Capell\Events\Models\Event;
use Capell\Events\Models\EventNotificationLog;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Models\EventRegistration;
use Capell\Events\Models\EventVenue;
use Capell\Events\Notifications\EventRegistrationNotification;
use Capell\Events\Support\Schema\EventSchemaTemplate;
use Carbon\CarbonImmutable;
use Filament\Schemas\Schema;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

it('builds events table configurator and schema template defaults', function (): void {
    $template = new EventSchemaTemplate;

    expect(EventsTable::configure(eventsTableForCoverage())->getColumns())->toHaveCount(7)
        ->and(EventVenueResource::form(Schema::make())->getComponents())->toHaveCount(6)
        ->and(EventVenueResource::table(eventsTableForCoverage())->getColumns())->toHaveCount(2)
        ->and($template->build(new Page, new Site, new Language))->toBe([])
        ->and($template->requiredFields(new Page, new Site, new Language))->toBe([
            '@type',
            'name',
            'startDate',
            'eventStatus',
            'eventAttendanceMode',
        ]);
});

it('builds event registration notification mail variants', function (EventNotificationTypeEnum $type): void {
    $event = new Event(['name' => 'Launch Briefing']);
    $occurrence = new EventOccurrence([
        'starts_at' => CarbonImmutable::parse('2026-06-01 10:00:00', 'UTC'),
        'timezone' => 'Europe/London',
    ]);
    $registration = new EventRegistration;

    $occurrence->setRelation('event', $event);
    $registration->setRelation('occurrence', $occurrence);

    $notification = new EventRegistrationNotification($registration, $type);
    $mail = $notification->toMail(new stdClass);

    expect($notification->via(new stdClass))->toBe(['mail'])
        ->and($notification)->toBeInstanceOf(ShouldQueueAfterCommit::class)
        ->and($mail->subject)->toContain('Launch Briefing');
})->with(EventNotificationTypeEnum::cases());

it('covers event model relationships and casts', function (): void {
    $event = (new Event)->forceFill([
        'meta' => ['audience' => 'partners'],
        'notification_settings' => ['confirmation' => true],
    ]);
    $venue = (new EventVenue)->forceFill([
        'metadata' => ['floor' => '2'],
    ]);
    $log = (new EventNotificationLog)->forceFill([
        'payload' => ['subject' => 'Reminder'],
    ]);

    expect($event->venue()->getRelated())->toBeInstanceOf(EventVenue::class)
        ->and($event->occurrences()->getRelated())->toBeInstanceOf(EventOccurrence::class)
        ->and($event->meta)->toBe(['audience' => 'partners'])
        ->and($event->notification_settings)->toBe(['confirmation' => true])
        ->and($venue->site()->getForeignKeyName())->toBe('site_id')
        ->and($venue->events()->getRelated())->toBeInstanceOf(Event::class)
        ->and($venue->occurrences()->getRelated())->toBeInstanceOf(EventOccurrence::class)
        ->and(EventVenue::query()->ordered()->toBase()->orders)->toHaveCount(1)
        ->and($venue->getAttribute('metadata'))->toBe(['floor' => '2'])
        ->and($log->getAttribute('payload'))->toBe(['subject' => 'Reminder']);
});

it('covers event model page helpers and publish metadata', function (): void {
    $language = Language::factory()->english()->create();
    $site = Site::factory()->withTranslations($language)->create();
    $blueprint = Blueprint::factory()->page()->create([
        'key' => 'event',
        'group' => null,
        'meta' => ['url_params' => ['year' => 'Y']],
        'order' => 10,
    ]);

    $event = Event::factory()->for($site)->create([
        'blueprint_id' => $blueprint->getKey(),
        'visible_from' => CarbonImmutable::parse('2026-06-01 09:00:00', 'UTC'),
        'starts_at' => CarbonImmutable::parse('2026-06-02 09:00:00', 'UTC'),
    ]);
    $event->setRelation('type', $blueprint);
    $event->setRelation('site', $site);

    $event->registerMediaCollections();

    expect(Event::getDefaultType(null)?->is($blueprint))->toBeTrue()
        ->and(Event::getDefaultType('page')?->is($blueprint))->toBeTrue()
        ->and(Event::hasPageHierarchy())->toBeFalse()
        ->and(Event::defaultOrdering())->toBe(PageOrderEnum::Latest)
        ->and($event->shouldLogVisit())->toBeTrue()
        ->and($event->getMediaCollection(MediaCollectionEnum::Image->value)?->name)->toBe(MediaCollectionEnum::Image->value)
        ->and($event->getParentUrl($language))->toBe('/events')
        ->and($event->layout()->getForeignKeyName())->toBe('layout_id')
        ->and($event->pageUrls()->getRelated())->toBeInstanceOf(PageUrl::class)
        ->and($event->canonicalPages()->getRelated())->toBeInstanceOf(Event::class)
        ->and($event->canonicalPage()->getMorphType())->toBe('meta->canonical_pageable_type')
        ->and($event->draftRevisions()->toBase()->toSql())->toContain('0=1')
        ->and($event->getPublishDate()?->toDateTimeString())->toBe('2026-06-01 09:00:00')
        ->and($event->url_params)->toBe(['year' => 'Y']);
});

function eventsTableForCoverage(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null);

    return Table::make($livewire);
}
