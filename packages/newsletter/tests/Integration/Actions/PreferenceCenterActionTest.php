<?php

declare(strict_types=1);

use Capell\Newsletter\Actions\CreatePreferenceCenterTokenAction;
use Capell\Newsletter\Actions\CreateUnsubscribeTokenAction;
use Capell\Newsletter\Actions\ResolvePreferenceCenterAction;
use Capell\Newsletter\Actions\UpdatePreferenceCenterAction;
use Capell\Newsletter\Data\PreferenceCenterData;
use Capell\Newsletter\Data\PreferenceCenterUpdateData;
use Capell\Newsletter\Enums\PublicTokenType;
use Capell\Newsletter\Enums\SegmentType;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Models\Segment;
use Capell\Newsletter\Models\Subscriber;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Route;

it('resolves a reusable preference center token into active same-site segment preferences', function (): void {
    $site = $this->createNewsletterSite();
    $otherSite = $this->createNewsletterSite('Other Site');
    $subscriber = Subscriber::factory()->create([
        'site_id' => $site->getKey(),
        'email' => 'reader@example.com',
        'status' => SubscriberStatus::Subscribed,
    ]);
    $selectedSegment = newsletterSegment((int) $site->getKey(), 'Product updates', 'product-updates');
    $unselectedSegment = newsletterSegment((int) $site->getKey(), 'Weekly digest', 'weekly-digest');
    $inactiveSegment = newsletterSegment((int) $site->getKey(), 'Internal', 'internal', false);
    $otherSiteSegment = newsletterSegment((int) $otherSite->getKey(), 'Other site', 'other-site');

    $subscriber->segments()->sync([
        $selectedSegment->getKey(),
        $inactiveSegment->getKey(),
        $otherSiteSegment->getKey(),
    ]);

    $token = CreatePreferenceCenterTokenAction::run($subscriber);
    $preferences = ResolvePreferenceCenterAction::run($token);
    $preferenceData = newsletterPreferenceData($preferences);

    expect($preferenceData->subscriberId)->toBe((int) $subscriber->getKey())
        ->and($preferenceData->email)->toBe('reader@example.com')
        ->and($preferenceData->canReceiveNewsletter)->toBeTrue()
        ->and(collect($preferenceData->segments)->pluck('handle')->all())->toBe([
            'product-updates',
            'weekly-digest',
        ])
        ->and(collect($preferenceData->segments)->pluck('selected', 'handle')->all())->toBe([
            'product-updates' => true,
            'weekly-digest' => false,
        ]);

    expect(ResolvePreferenceCenterAction::run($token))->not->toBeNull();
    expect($unselectedSegment->exists)->toBeTrue();
});

it('rejects invalid expired and wrong-purpose preference center tokens', function (): void {
    $subscriber = Subscriber::factory()->create([
        'site_id' => $this->createNewsletterSite()->getKey(),
        'status' => SubscriberStatus::Unsubscribed,
    ]);
    $expiredToken = 'expired-preference-token';
    $subscriber->publicTokens()->create([
        'type' => 'preference_center',
        'token_hash' => hash('sha256', $expiredToken),
        'expires_at' => now()->subMinute(),
    ]);
    $unsubscribeToken = CreateUnsubscribeTokenAction::run($subscriber);

    expect(ResolvePreferenceCenterAction::run('missing-token'))->toBeNull()
        ->and(ResolvePreferenceCenterAction::run($expiredToken))->toBeNull()
        ->and(ResolvePreferenceCenterAction::run($unsubscribeToken))->toBeNull();
});

it('creates expiring preference center and unsubscribe tokens', function (): void {
    config()->set('capell-newsletter.public_tokens.token_expiry_hours', 24);

    Date::setTestNow(Date::parse('2026-06-04 12:00:00'));

    try {
        $subscriber = Subscriber::factory()->create([
            'site_id' => $this->createNewsletterSite()->getKey(),
            'status' => SubscriberStatus::Subscribed,
        ]);

        $preferenceToken = CreatePreferenceCenterTokenAction::run($subscriber);
        $unsubscribeToken = CreateUnsubscribeTokenAction::run($subscriber);

        $preferencePublicToken = $subscriber->publicTokens()
            ->where('type', PublicTokenType::PreferenceCenter)
            ->where('token_hash', hash('sha256', (string) $preferenceToken))
            ->first();
        $unsubscribePublicToken = $subscriber->publicTokens()
            ->where('type', PublicTokenType::Unsubscribe)
            ->where('token_hash', hash('sha256', (string) $unsubscribeToken))
            ->first();

        expect($preferencePublicToken?->expires_at?->equalTo(now()->addHours(24)))->toBeTrue()
            ->and($unsubscribePublicToken?->expires_at?->equalTo(now()->addHours(24)))->toBeTrue();
    } finally {
        Date::setTestNow();
    }
});

it('updates preference center segment selections without accepting inactive or cross-site segments', function (): void {
    $site = $this->createNewsletterSite();
    $otherSite = $this->createNewsletterSite('Other Site');
    $subscriber = Subscriber::factory()->create([
        'site_id' => $site->getKey(),
        'status' => SubscriberStatus::Subscribed,
    ]);
    $allowedSegment = newsletterSegment((int) $site->getKey(), 'Product updates', 'product-updates');
    $inactiveSegment = newsletterSegment((int) $site->getKey(), 'Internal', 'internal', false);
    $otherSiteSegment = newsletterSegment((int) $otherSite->getKey(), 'Other site', 'other-site');

    $token = CreatePreferenceCenterTokenAction::run($subscriber);
    $preferences = UpdatePreferenceCenterAction::run($token, new PreferenceCenterUpdateData([
        (string) $allowedSegment->handle,
        (string) $inactiveSegment->handle,
        (string) $otherSiteSegment->handle,
    ]));
    $preferenceData = newsletterPreferenceData($preferences);

    expect(collect($preferenceData->segments)->pluck('selected', 'handle')->all())->toBe([
        'product-updates' => true,
    ])
        ->and($subscriber->refresh()->segments()->pluck('newsletter_segments.id')->all())->toBe([
            (int) $allowedSegment->getKey(),
        ]);
});

it('exposes a public preference center route for reusable newsletter tokens', function (): void {
    $site = $this->createNewsletterSite();
    $subscriber = Subscriber::factory()->create([
        'site_id' => $site->getKey(),
        'email' => 'portal-reader@example.com',
        'status' => SubscriberStatus::Subscribed,
    ]);
    $selectedSegment = newsletterSegment((int) $site->getKey(), 'Product updates', 'product-updates');
    $unselectedSegment = newsletterSegment((int) $site->getKey(), 'Weekly digest', 'weekly-digest');
    $subscriber->segments()->sync([$selectedSegment->getKey()]);

    $token = CreatePreferenceCenterTokenAction::run($subscriber);

    $this->get(route('capell-newsletter.preferences.show', ['token' => $token]))
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertSee('Newsletter preferences')
        ->assertSee('Product updates')
        ->assertSee('Weekly digest')
        ->assertDontSee('capell-app/newsletter', false)
        ->assertDontSee('newsletter_subscriber', false)
        ->assertDontSee('signed', false);

    $this->post(route('capell-newsletter.preferences.update', ['token' => $token]), [
        'segments' => [$unselectedSegment->handle],
    ])->assertRedirect(route('capell-newsletter.preferences.show', ['token' => $token]));

    expect($subscriber->refresh()->segments()->pluck('newsletter_segments.id')->all())->toBe([
        (int) $unselectedSegment->getKey(),
    ]);

    expect(Route::has('capell-newsletter.preferences.show'))->toBeTrue()
        ->and(Route::has('capell-newsletter.preferences.update'))->toBeTrue();
});

function newsletterSegment(int $siteId, string $name, string $handle, bool $isActive = true): Segment
{
    return Segment::query()->create([
        'site_id' => $siteId,
        'name' => $name,
        'handle' => $handle,
        'type' => SegmentType::Static,
        'is_active' => $isActive,
    ]);
}

function newsletterPreferenceData(?PreferenceCenterData $preferences): PreferenceCenterData
{
    expect($preferences)->toBeInstanceOf(PreferenceCenterData::class);

    throw_unless($preferences instanceof PreferenceCenterData, RuntimeException::class, 'Expected preference center data.');

    return $preferences;
}
