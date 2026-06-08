<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\PublishingStudio\Actions\BuildEditorialCalendarEventsAction;
use Capell\PublishingStudio\Contracts\EditorialCalendarEventContributor;
use Capell\PublishingStudio\Data\EditorialCalendarEventData;
use Capell\PublishingStudio\Data\EditorialCalendarQueryData;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Capell\PublishingStudio\Models\Workspace;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('builds editorial calendar events from existing scheduler sources', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    Page::factory()->create([
        'name' => 'Launch page',
        'visible_from' => CarbonImmutable::parse('2026-05-02 10:00:00', 'UTC'),
    ]);

    Workspace::factory()->scheduled('2026-05-04 09:00:00')->create([
        'name' => 'Release workspace',
    ]);

    $events = BuildEditorialCalendarEventsAction::run(
        startsAt: CarbonImmutable::parse('2026-05-01 00:00:00', 'UTC'),
        endsAt: CarbonImmutable::parse('2026-05-31 23:59:59', 'UTC'),
    );

    $pageEvent = $events->firstWhere('sourceType', 'page');

    expect($events)->toHaveCount(2)
        ->and($events->pluck('sourcePackage')->all())->toBe([
            'capell-app/publishing-studio',
            'capell-app/publishing-studio',
        ])
        ->and($events->pluck('sourceType')->sort()->values()->all())->toBe(['page', 'workspace'])
        ->and($events->pluck('eventType')->all())->toBe([
            SchedulerEventTypeEnum::Publish->value,
            SchedulerEventTypeEnum::Publish->value,
        ])
        ->and($pageEvent)->toBeInstanceOf(EditorialCalendarEventData::class)
        ->and($pageEvent?->toCalendarRecord())
        ->toMatchArray([
            'source_package' => 'capell-app/publishing-studio',
            'source_type' => 'page',
            'title' => 'Launch page',
            'event_type' => SchedulerEventTypeEnum::Publish->value,
        ]);
});

test('merges tagged package contributors and applies calendar filters', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    Page::factory()->create([
        'name' => 'Filtered page',
        'visible_from' => CarbonImmutable::parse('2026-05-02 10:00:00', 'UTC'),
    ]);

    app()->bind(
        'publishing-studio.tests.editorial-calendar-contributor',
        fn (): EditorialCalendarEventContributor => new class implements EditorialCalendarEventContributor
        {
            public function editorialCalendarEvents(EditorialCalendarQueryData $query): iterable
            {
                return [
                    new EditorialCalendarEventData(
                        id: 'campaign-12-launch',
                        sourcePackage: 'capell-app/campaign-studio',
                        sourceType: 'campaign',
                        sourceId: '12',
                        title: 'Campaign launch',
                        eventType: 'campaign.launch',
                        startsAt: CarbonImmutable::parse('2026-05-03 10:00:00', 'UTC'),
                        eventTypeLabel: 'Campaign launch',
                        status: 'Scheduled',
                        state: 'scheduled',
                        siteId: 7,
                        timezone: 'UTC',
                    ),
                    new EditorialCalendarEventData(
                        id: 'campaign-13-launch',
                        sourcePackage: 'capell-app/campaign-studio',
                        sourceType: 'campaign',
                        sourceId: '13',
                        title: 'Other site campaign',
                        eventType: 'campaign.launch',
                        startsAt: CarbonImmutable::parse('2026-05-03 11:00:00', 'UTC'),
                        state: 'scheduled',
                        siteId: 99,
                    ),
                    new EditorialCalendarEventData(
                        id: 'newsletter-9-send',
                        sourcePackage: 'capell-app/newsletter',
                        sourceType: 'newsletter',
                        sourceId: '9',
                        title: 'Newsletter send',
                        eventType: 'newsletter.send',
                        startsAt: CarbonImmutable::parse('2026-05-03 12:00:00', 'UTC'),
                        state: 'scheduled',
                        siteId: 7,
                    ),
                ];
            }
        },
    );
    app()->tag(['publishing-studio.tests.editorial-calendar-contributor'], EditorialCalendarEventContributor::TAG);

    $events = BuildEditorialCalendarEventsAction::run(
        startsAt: CarbonImmutable::parse('2026-05-01 00:00:00', 'UTC'),
        endsAt: CarbonImmutable::parse('2026-05-31 23:59:59', 'UTC'),
        sourceTypes: ['campaign'],
        eventTypes: ['campaign.launch'],
        siteIds: [7],
        state: 'scheduled',
    );

    $event = publishingStudioFirstEditorialCalendarEvent($events);

    expect($events)->toHaveCount(1)
        ->and($event->sourcePackage)->toBe('capell-app/campaign-studio')
        ->and($event->sourceType)->toBe('campaign')
        ->and($event->title)->toBe('Campaign launch');
});

/**
 * @param  Collection<int, EditorialCalendarEventData>  $events
 */
function publishingStudioFirstEditorialCalendarEvent(Collection $events): EditorialCalendarEventData
{
    $event = $events->first();

    throw_unless($event instanceof EditorialCalendarEventData);

    return $event;
}
