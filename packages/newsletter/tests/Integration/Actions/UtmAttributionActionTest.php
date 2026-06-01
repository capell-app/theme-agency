<?php

declare(strict_types=1);

use Capell\Newsletter\Actions\RecordConsentEventAction;
use Capell\Newsletter\Actions\ResolveUtmAttributionAction;
use Capell\Newsletter\Data\ConsentEvidenceData;
use Capell\Newsletter\Enums\ConsentEventType;
use Capell\Newsletter\Models\ConsentEvent;
use Capell\Newsletter\Models\Subscriber;

it('resolves known utm parameters from consent evidence', function (): void {
    $attribution = ResolveUtmAttributionAction::run(new ConsentEvidenceData(
        sourceType: 'form_submission',
        url: 'https://example.test/signup?utm_source=newsletter&utm_medium=email&utm_campaign=launch&utm_term=founders&utm_content=hero&utm_id=abc123&ignored=value',
    ));

    expect($attribution)->not->toBeNull()
        ->and($attribution?->source)->toBe('newsletter')
        ->and($attribution?->medium)->toBe('email')
        ->and($attribution?->campaign)->toBe('launch')
        ->and($attribution?->term)->toBe('founders')
        ->and($attribution?->content)->toBe('hero')
        ->and($attribution?->id)->toBe('abc123');
});

it('records utm attribution on consent event metadata', function (): void {
    $site = $this->createNewsletterSite();
    $subscriber = Subscriber::factory()->create([
        'site_id' => $site->getKey(),
        'email' => 'utm@example.com',
    ]);

    $event = RecordConsentEventAction::run(
        $subscriber,
        ConsentEventType::FormCapture,
        new ConsentEvidenceData(
            sourceType: 'form_submission',
            sourceId: 'submission-1',
            url: 'https://example.test/signup?utm_source=google&utm_medium=cpc&utm_campaign=spring',
        ),
        null,
        null,
        ['form_handle' => 'newsletter'],
    );

    expect($event)->toBeInstanceOf(ConsentEvent::class)
        ->and($event->metadata)->toMatchArray([
            'form_handle' => 'newsletter',
            'utm_attribution' => [
                'source' => 'google',
                'medium' => 'cpc',
                'campaign' => 'spring',
            ],
        ]);
});

it('preserves explicit utm attribution metadata', function (): void {
    $site = $this->createNewsletterSite();
    $subscriber = Subscriber::factory()->create([
        'site_id' => $site->getKey(),
        'email' => 'explicit-utm@example.com',
    ]);

    $event = RecordConsentEventAction::run(
        $subscriber,
        ConsentEventType::FormCapture,
        new ConsentEvidenceData(
            sourceType: 'form_submission',
            url: 'https://example.test/signup?utm_source=google',
        ),
        null,
        null,
        [
            'utm_attribution' => [
                'source' => 'manual',
            ],
        ],
    );

    expect($event->metadata['utm_attribution'])->toBe([
        'source' => 'manual',
    ]);
});
