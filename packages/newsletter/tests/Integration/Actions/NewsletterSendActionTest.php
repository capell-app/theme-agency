<?php

declare(strict_types=1);

use Capell\Newsletter\Actions\BuildDueNewsletterSendsAction;
use Capell\Newsletter\Actions\BuildNewsletterSendHandoffPayloadAction;
use Capell\Newsletter\Actions\ScheduleNewsletterSendAction;
use Capell\Newsletter\Actions\UpdateNewsletterSendStatusAction;
use Capell\Newsletter\Enums\AuthType;
use Capell\Newsletter\Enums\NewsletterSendStatus;
use Capell\Newsletter\Enums\ProviderType;
use Capell\Newsletter\Enums\SegmentType;
use Capell\Newsletter\Models\NewsletterSend;
use Capell\Newsletter\Models\ProviderAudience;
use Capell\Newsletter\Models\ProviderConnection;
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

it('builds an explicit external handoff payload for downstream delivery workers', function (): void {
    $site = $this->createNewsletterSite();
    $segment = Segment::query()->create([
        'site_id' => $site->getKey(),
        'name' => 'Subscribed',
        'handle' => 'subscribed',
        'type' => SegmentType::SavedFilter,
        'filters' => [],
        'is_active' => true,
    ]);
    $connection = ProviderConnection::query()->create([
        'site_id' => $site->getKey(),
        'name' => 'Mailchimp',
        'provider' => ProviderType::Mailchimp,
        'auth_type' => AuthType::ApiKey,
        'credentials' => ['api_key' => 'fake'],
        'is_enabled' => true,
    ]);
    $audience = ProviderAudience::query()->create([
        'provider_connection_id' => $connection->getKey(),
        'name' => 'Product list',
        'remote_id' => 'list-123',
        'is_default' => true,
        'sync_subscribed_only' => true,
    ]);
    $send = NewsletterSend::factory()->create([
        'site_id' => $site->getKey(),
        'newsletter_segment_id' => $segment->getKey(),
        'newsletter_provider_audience_id' => $audience->getKey(),
        'status' => NewsletterSendStatus::Scheduled,
        'scheduled_at' => CarbonImmutable::parse('2026-05-31 12:00:00', 'UTC'),
        'utm_campaign' => 'may-product-update',
        'metadata' => ['template' => 'release-note'],
    ]);

    $payload = BuildNewsletterSendHandoffPayloadAction::run($send);

    expect($payload)->toMatchArray([
        'strategy' => 'external_handoff',
        'send' => [
            'id' => $send->getKey(),
            'site_id' => $site->getKey(),
            'status' => NewsletterSendStatus::Scheduled->value,
            'name' => $send->name,
            'subject' => $send->subject,
            'preheader' => $send->preheader,
            'scheduled_at' => $send->scheduled_at?->toJSON(),
        ],
        'audience' => [
            'segment_id' => $segment->getKey(),
            'segment_handle' => 'subscribed',
            'provider_audience_id' => $audience->getKey(),
            'provider_connection_id' => $connection->getKey(),
            'provider' => ProviderType::Mailchimp->value,
            'remote_audience_id' => 'list-123',
        ],
        'utm' => [
            'campaign' => 'may-product-update',
            'source' => null,
            'medium' => null,
            'term' => null,
            'content' => null,
            'id' => null,
        ],
        'metadata' => [
            'template' => 'release-note',
        ],
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
