<?php

declare(strict_types=1);

use Capell\Newsletter\Enums\NewsletterSendStatus;
use Capell\Newsletter\Models\NewsletterSend;
use Capell\Newsletter\Providers\NewsletterServiceProvider;
use Capell\Newsletter\Support\EditorialCalendar\NewsletterEditorialCalendarEventContributor;
use Capell\PublishingStudio\Actions\BuildEditorialCalendarEventsAction;
use Capell\PublishingStudio\Contracts\EditorialCalendarEventContributor;
use Capell\PublishingStudio\Data\EditorialCalendarEventData;
use Carbon\CarbonImmutable;

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('contributes scheduled newsletter sends to the editorial calendar', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-01 09:00:00', 'UTC'));

    $site = $this->createNewsletterSite('Primary newsletter site');
    $otherSite = $this->createNewsletterSite('Other newsletter site');

    $send = NewsletterSend::factory()->create([
        'site_id' => $site->getKey(),
        'name' => 'May product update',
        'subject' => 'What changed in May',
        'status' => NewsletterSendStatus::Scheduled,
        'scheduled_at' => CarbonImmutable::parse('2026-05-20 10:00:00', 'UTC'),
        'utm_campaign' => 'may-product-update',
    ]);

    NewsletterSend::factory()->create([
        'site_id' => $otherSite->getKey(),
        'status' => NewsletterSendStatus::Scheduled,
        'scheduled_at' => CarbonImmutable::parse('2026-05-20 11:00:00', 'UTC'),
    ]);

    $events = BuildEditorialCalendarEventsAction::run(
        startsAt: CarbonImmutable::parse('2026-05-01 00:00:00', 'UTC'),
        endsAt: CarbonImmutable::parse('2026-05-31 23:59:59', 'UTC'),
        sourceTypes: ['newsletter'],
        eventTypes: ['newsletter.send'],
        siteIds: [(int) $site->getKey()],
        state: NewsletterSendStatus::Scheduled->value,
    );
    $event = $events->first();

    throw_unless($event instanceof EditorialCalendarEventData, RuntimeException::class, 'Expected newsletter editorial calendar event.');

    expect($events)->toHaveCount(1)
        ->and($event->id)->toBe('newsletter-send-' . $send->id)
        ->and($event->sourcePackage)->toBe(NewsletterServiceProvider::$packageName)
        ->and($event->sourceType)->toBe('newsletter')
        ->and($event->sourceId)->toBe((string) $send->id)
        ->and($event->title)->toBe('May product update')
        ->and($event->eventType)->toBe('newsletter.send')
        ->and($event->state)->toBe(NewsletterSendStatus::Scheduled->value)
        ->and($event->siteId)->toBe((int) $site->getKey())
        ->and($event->metadata['subject'])->toBe('What changed in May')
        ->and($event->metadata['utm_campaign'])->toBe('may-product-update');
});

it('registers the newsletter editorial calendar contributor when publishing studio is available', function (): void {
    $contributors = collect(app()->tagged(EditorialCalendarEventContributor::TAG));

    expect($contributors->contains(fn (mixed $contributor): bool => $contributor instanceof NewsletterEditorialCalendarEventContributor))->toBeTrue();
});
