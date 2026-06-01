<?php

declare(strict_types=1);

use Capell\Newsletter\Actions\BuildDueNewsletterSendsAction;
use Capell\Newsletter\Actions\ScheduleNewsletterSendAction;
use Capell\Newsletter\Actions\UpdateNewsletterSendStatusAction;
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

it('builds due newsletter sends in schedule order', function (): void {
    $site = $this->createNewsletterSite();
    $now = CarbonImmutable::parse('2026-05-31 12:00:00', 'UTC');
    $firstDue = NewsletterSend::factory()->create([
        'site_id' => $site->getKey(),
        'status' => NewsletterSendStatus::Scheduled,
        'scheduled_at' => $now->subHour(),
    ]);
    $secondDue = NewsletterSend::factory()->create([
        'site_id' => $site->getKey(),
        'status' => NewsletterSendStatus::Scheduled,
        'scheduled_at' => $now->subMinutes(10),
    ]);
    NewsletterSend::factory()->create([
        'site_id' => $site->getKey(),
        'status' => NewsletterSendStatus::Scheduled,
        'scheduled_at' => $now->addHour(),
    ]);
    NewsletterSend::factory()->create([
        'site_id' => $site->getKey(),
        'status' => NewsletterSendStatus::Draft,
        'scheduled_at' => $now->subHours(2),
    ]);

    $sends = BuildDueNewsletterSendsAction::run($now);

    expect($sends->pluck('id')->all())->toBe([
        $firstDue->getKey(),
        $secondDue->getKey(),
    ]);
});

it('updates newsletter send lifecycle timestamps and metadata', function (): void {
    $send = NewsletterSend::factory()->create([
        'status' => NewsletterSendStatus::Scheduled,
        'scheduled_at' => CarbonImmutable::parse('2026-05-31 12:00:00', 'UTC'),
        'metadata' => ['template' => 'release-note'],
    ]);
    $startedAt = CarbonImmutable::parse('2026-05-31 12:01:00', 'UTC');
    $sentAt = CarbonImmutable::parse('2026-05-31 12:05:00', 'UTC');

    UpdateNewsletterSendStatusAction::run($send, NewsletterSendStatus::Sending, $startedAt, [
        'provider' => ['id' => 'campaign-123'],
    ]);
    UpdateNewsletterSendStatusAction::run($send->refresh(), NewsletterSendStatus::Sent, $sentAt, [
        'provider' => ['status' => 'sent'],
    ]);

    expect($send->refresh()->status)->toBe(NewsletterSendStatus::Sent)
        ->and($send->sent_at?->toIso8601String())->toBe('2026-05-31T12:05:00+00:00')
        ->and($send->metadata)->toMatchArray([
            'template' => 'release-note',
            'provider' => [
                'id' => 'campaign-123',
                'status' => 'sent',
            ],
            'delivery' => [
                'started_at' => $startedAt->toISOString(),
            ],
        ]);
});

it('rejects marking a scheduled newsletter send as sent before sending', function (): void {
    $send = NewsletterSend::factory()->create([
        'status' => NewsletterSendStatus::Scheduled,
    ]);

    UpdateNewsletterSendStatusAction::run($send, NewsletterSendStatus::Sent);
})->throws(ValidationException::class);
