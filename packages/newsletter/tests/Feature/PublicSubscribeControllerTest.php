<?php

declare(strict_types=1);

use Capell\Frontend\Support\State\FrontendState;
use Capell\Newsletter\Enums\PublicTokenType;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Models\ConsentEvent;
use Capell\Newsletter\Models\PublicToken;
use Capell\Newsletter\Models\Subscriber;
use Illuminate\Support\Facades\Notification;

it('captures public newsletter subscriptions for the current frontend site', function (): void {
    Notification::fake();

    $site = $this->createNewsletterSite();

    app(FrontendState::class)->withSite($site);

    $this
        ->from('/notes')
        ->post(route('capell-newsletter.subscribe'), [
            'email' => 'reader@gmail.com',
            'first_name' => 'Ada',
            'source' => 'theme_portfolio_newsletter',
        ])
        ->assertRedirect('/notes')
        ->assertSessionHas('newsletter_status', __('capell-newsletter::messages.subscribed'));

    $subscriber = Subscriber::query()->forEmail((int) $site->getKey(), 'reader@gmail.com')->first();

    expect($subscriber)
        ->toBeInstanceOf(Subscriber::class)
        ->and($subscriber?->status)->toBe(SubscriberStatus::Pending)
        ->and($subscriber?->first_name)->toBe('Ada')
        ->and($subscriber?->source_form_handle)->toBe('theme_portfolio_newsletter')
        ->and(ConsentEvent::query()->where('subscriber_id', $subscriber?->getKey())->count())->toBe(2)
        ->and(PublicToken::query()
            ->where('subscriber_id', $subscriber?->getKey())
            ->where('type', PublicTokenType::Confirm)
            ->exists())->toBeTrue();
});

it('rejects public newsletter subscriptions without a frontend site context', function (): void {
    $this
        ->post(route('capell-newsletter.subscribe'), [
            'email' => 'reader@example.com',
        ])
        ->assertNotFound();
});
