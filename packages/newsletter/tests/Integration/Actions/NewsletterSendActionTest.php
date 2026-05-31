<?php

declare(strict_types=1);

use Capell\Newsletter\Actions\ScheduleNewsletterSendAction;
use Capell\Newsletter\Enums\NewsletterSendStatus;
use Capell\Newsletter\Enums\SegmentType;
use Capell\Newsletter\Models\NewsletterSend;
use Capell\Newsletter\Models\Segment;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

it('schedules a newsletter send with audience and attribution metadata', function (): void {
    $site = $this->createNewsletterSite();
    $segment = Segment::query()->create([
        'site_id' => $site->getKey(),
        'name' => 'Subscribed',
        'handle' => 'subscribed',
        'type' => SegmentType::SavedFilter,
        'filters' => [],
        'is_active' => true,
    ]);

    $send = ScheduleNewsletterSendAction::run(
        siteId: (int) $site->getKey(),
        name: 'May product update',
        subject: 'What changed in May',
        scheduledAt: CarbonImmutable::parse('2026-05-20 10:00:00', 'UTC'),
        segmentId: (int) $segment->getKey(),
        preheader: 'A practical release note.',
        utmSource: 'newsletter',
        utmMedium: 'email',
        utmCampaign: 'may-product-update',
        metadata: ['template' => 'release-note'],
    );

    expect($send)->toBeInstanceOf(NewsletterSend::class)
        ->and($send->status)->toBe(NewsletterSendStatus::Scheduled)
        ->and($send->site_id)->toBe($site->getKey())
        ->and($send->newsletter_segment_id)->toBe($segment->getKey())
        ->and($send->scheduled_at?->toIso8601String())->toBe('2026-05-20T10:00:00+00:00')
        ->and($send->utm_campaign)->toBe('may-product-update')
        ->and($send->metadata)->toBe(['template' => 'release-note']);
});

it('rejects scheduling a send against a segment from another site', function (): void {
    $site = $this->createNewsletterSite('Primary site');
    $otherSite = $this->createNewsletterSite('Other site');
    $segment = Segment::query()->create([
        'site_id' => $otherSite->getKey(),
        'name' => 'Other site segment',
        'handle' => 'other-site-segment',
        'type' => SegmentType::SavedFilter,
        'filters' => [],
        'is_active' => true,
    ]);

    ScheduleNewsletterSendAction::run(
        siteId: (int) $site->getKey(),
        name: 'Invalid send',
        subject: 'Invalid audience',
        scheduledAt: CarbonImmutable::parse('2026-05-20 10:00:00', 'UTC'),
        segmentId: (int) $segment->getKey(),
    );
})->throws(ValidationException::class);
