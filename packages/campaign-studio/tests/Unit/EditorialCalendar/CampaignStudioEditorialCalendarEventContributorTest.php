<?php

declare(strict_types=1);

use Capell\CampaignStudio\Enums\CampaignStatus;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Providers\CampaignStudioServiceProvider;
use Capell\CampaignStudio\Support\EditorialCalendar\CampaignStudioEditorialCalendarEventContributor;
use Capell\Core\Models\Site;
use Capell\PublishingStudio\Actions\BuildEditorialCalendarEventsAction;
use Capell\PublishingStudio\Contracts\EditorialCalendarEventContributor;
use Capell\PublishingStudio\Data\EditorialCalendarEventData;
use Capell\PublishingStudio\Data\EditorialCalendarQueryData;
use Carbon\CarbonImmutable;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('contributes campaign start and end events to the editorial calendar', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    $site = Site::factory()->withTranslations()->create(['name' => 'Marketing site']);

    $campaign = CampaignGroup::factory()->create([
        'site_id' => $site->id,
        'name' => 'Spring launch',
        'slug' => 'spring-launch',
        'status' => CampaignStatus::Scheduled,
        'starts_at' => CarbonImmutable::parse('2026-05-10 10:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-05-20 10:00:00', 'UTC'),
        'utm_campaign' => 'spring-launch',
    ]);

    CampaignGroup::factory()->create([
        'site_id' => $site->id,
        'starts_at' => CarbonImmutable::parse('2026-06-10 10:00:00', 'UTC'),
        'ends_at' => CarbonImmutable::parse('2026-06-20 10:00:00', 'UTC'),
    ]);

    $events = (new CampaignStudioEditorialCalendarEventContributor)->editorialCalendarEvents(new EditorialCalendarQueryData(
        startsAt: CarbonImmutable::parse('2026-05-01 00:00:00', 'UTC'),
        endsAt: CarbonImmutable::parse('2026-05-31 23:59:59', 'UTC'),
        sourceTypes: ['campaign'],
        siteIds: [(int) $site->id],
        state: CampaignStatus::Scheduled->value,
    ));

    $firstEvent = $events->first();

    throw_unless($firstEvent instanceof EditorialCalendarEventData, RuntimeException::class, 'Expected the campaign contributor to return an editorial calendar event.');

    expect($events)->toHaveCount(2)
        ->and($events->pluck('id')->all())->toBe([
            'campaign-' . $campaign->id . '-start',
            'campaign-' . $campaign->id . '-end',
        ])
        ->and($events->pluck('sourcePackage')->unique()->values()->all())->toBe([CampaignStudioServiceProvider::$packageName])
        ->and($events->pluck('sourceType')->unique()->values()->all())->toBe(['campaign'])
        ->and($events->pluck('eventType')->all())->toBe(['campaign.start', 'campaign.end'])
        ->and($firstEvent->title)->toBe('Spring launch')
        ->and($firstEvent->siteId)->toBe((int) $site->id)
        ->and($firstEvent->siteName)->toBe('Marketing site')
        ->and($firstEvent->state)->toBe(CampaignStatus::Scheduled->value)
        ->and($firstEvent->metadata['utm_campaign'])->toBe('spring-launch');
});

it('is included by the publishing studio editorial calendar aggregator', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    $site = Site::factory()->withTranslations()->create();

    CampaignGroup::factory()->create([
        'site_id' => $site->id,
        'name' => 'Aggregator campaign',
        'status' => CampaignStatus::Scheduled,
        'starts_at' => CarbonImmutable::parse('2026-05-10 10:00:00', 'UTC'),
    ]);

    $events = BuildEditorialCalendarEventsAction::run(
        startsAt: CarbonImmutable::parse('2026-05-01 00:00:00', 'UTC'),
        endsAt: CarbonImmutable::parse('2026-05-31 23:59:59', 'UTC'),
        sourceTypes: ['campaign'],
        eventTypes: ['campaign.start'],
        siteIds: [(int) $site->id],
        state: CampaignStatus::Scheduled->value,
    );

    $firstEvent = $events->first();

    throw_unless($firstEvent instanceof EditorialCalendarEventData, RuntimeException::class, 'Expected the aggregator to return a campaign editorial calendar event.');

    expect($events)->toHaveCount(1)
        ->and($firstEvent->sourcePackage)->toBe(CampaignStudioServiceProvider::$packageName)
        ->and($firstEvent->sourceType)->toBe('campaign')
        ->and($firstEvent->title)->toBe('Aggregator campaign');
});

it('registers the campaign studio editorial calendar contributor when publishing studio is available', function (): void {
    $contributors = collect(app()->tagged(EditorialCalendarEventContributor::TAG));

    expect($contributors->contains(fn (mixed $contributor): bool => $contributor instanceof CampaignStudioEditorialCalendarEventContributor))->toBeTrue();
});
