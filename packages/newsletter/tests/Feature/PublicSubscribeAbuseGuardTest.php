<?php

declare(strict_types=1);

use Capell\Frontend\Support\State\FrontendState;
use Capell\Newsletter\Enums\PublicTokenType;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Models\PublicToken;
use Capell\Newsletter\Models\Subscriber;
use Capell\Newsletter\Notifications\ConfirmNewsletterSubscriptionNotification;
use Illuminate\Support\Facades\Notification;

it('does not downgrade an already-subscribed email or send a fresh confirmation', function (): void {
    Notification::fake();

    $site = $this->createNewsletterSite();
    resolve(FrontendState::class)->withSite($site);

    $siteId = (int) $site->getKey();

    $subscriber = Subscriber::query()->create([
        'site_id' => $siteId,
        'email' => 'confirmed@gmail.com',
        'email_hash' => Subscriber::emailHash('confirmed@gmail.com'),
        'status' => SubscriberStatus::Subscribed,
        'subscribed_at' => now(),
    ]);

    $this
        ->from('/notes')
        ->post(route('capell-newsletter.subscribe'), [
            'email' => 'confirmed@gmail.com',
            'source' => 'attacker_form',
        ])
        ->assertRedirect('/notes');

    $subscriber->refresh();

    expect($subscriber->status)->toBe(SubscriberStatus::Subscribed);

    expect(PublicToken::query()
        ->where('subscriber_id', $subscriber->getKey())
        ->where('type', PublicTokenType::Confirm)
        ->exists())->toBeFalse();

    Notification::assertNothingSent();
});

it('does not send a second confirmation for a rapid repeat subscribe of the same email', function (): void {
    Notification::fake();

    $site = $this->createNewsletterSite();
    resolve(FrontendState::class)->withSite($site);

    $siteId = (int) $site->getKey();

    $payload = [
        'email' => 'newcomer@gmail.com',
        'source' => 'public_subscribe',
    ];

    $this->from('/notes')->post(route('capell-newsletter.subscribe'), $payload)->assertRedirect('/notes');

    $subscriber = Subscriber::query()->forEmail($siteId, 'newcomer@gmail.com')->firstOrFail();

    expect($subscriber->status)->toBe(SubscriberStatus::Pending);

    $tokensAfterFirst = PublicToken::query()
        ->where('subscriber_id', $subscriber->getKey())
        ->where('type', PublicTokenType::Confirm)
        ->count();

    expect($tokensAfterFirst)->toBe(1);

    Notification::assertSentTimes(ConfirmNewsletterSubscriptionNotification::class, 1);

    // Rapid second request for the same email (still inside the resend cooldown).
    $this->from('/notes')->post(route('capell-newsletter.subscribe'), $payload)->assertRedirect('/notes');

    $tokensAfterSecond = PublicToken::query()
        ->where('subscriber_id', $subscriber->getKey())
        ->where('type', PublicTokenType::Confirm)
        ->count();

    // No new token minted and no second confirmation email sent.
    expect($tokensAfterSecond)->toBe(1);

    Notification::assertSentTimes(ConfirmNewsletterSubscriptionNotification::class, 1);
});
