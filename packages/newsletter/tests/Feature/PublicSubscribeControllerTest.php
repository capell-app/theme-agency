<?php

declare(strict_types=1);

use Capell\Core\Models\Site;
use Capell\Frontend\Support\State\FrontendState;
use Capell\Newsletter\Enums\PublicTokenType;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Models\ConsentEvent;
use Capell\Newsletter\Models\PublicToken;
use Capell\Newsletter\Models\Subscriber;
use Capell\Newsletter\Providers\NewsletterServiceProvider;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;

it('throttles public newsletter write routes', function (): void {
    expect(Route::getRoutes()->getByName('capell-newsletter.subscribe')?->gatherMiddleware())
        ->toContain('throttle:capell-newsletter-subscribe')
        ->and(Route::getRoutes()->getByName('capell-newsletter.unsubscribe.one-click')?->gatherMiddleware())
        ->toContain('throttle:capell-newsletter-one-click-unsubscribe')
        ->and(Route::getRoutes()->getByName('capell-newsletter.preferences.update')?->gatherMiddleware())
        ->toContain('throttle:capell-newsletter-preferences')
        ->and(Route::getRoutes()->getByName('capell-newsletter.provider-webhook')?->gatherMiddleware())
        ->toContain('throttle:capell-newsletter-provider-webhook');
});

it('loads public routes only after the newsletter package is installed', function (): void {
    $providerSource = file_get_contents((new ReflectionClass(NewsletterServiceProvider::class))->getFileName());

    expect($providerSource)->toBeString()
        ->and($providerSource)->not->toContain("->hasRoute('web')")
        ->and($providerSource)->toContain('loadRoutesFrom(__DIR__ . \'/../../routes/web.php\')');
});

it('captures public newsletter subscriptions for the current frontend site', function (): void {
    Notification::fake();

    $site = $this->createNewsletterSite();

    resolve(FrontendState::class)->withSite($site);

    $this
        ->from('/notes')
        ->post(route('capell-newsletter.subscribe'), [
            'email' => 'reader@gmail.com',
            'first_name' => 'Ada',
            'source' => 'theme_portfolio_newsletter',
        ])
        ->assertRedirect('/notes')
        ->assertSessionHas('newsletter_status', __('capell-newsletter::messages.subscribed'));

    $subscriber = Subscriber::query()->forEmail(newsletterPublicSubscribeSiteId($site), 'reader@gmail.com')->first();

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

function newsletterPublicSubscribeSiteId(Site $site): int
{
    $key = $site->getKey();

    if (is_int($key)) {
        return $key;
    }

    return is_string($key) && ctype_digit($key) ? (int) $key : 0;
}

it('rejects public newsletter subscriptions without a frontend site context', function (): void {
    $this
        ->post(route('capell-newsletter.subscribe'), [
            'email' => 'reader@example.com',
        ])
        ->assertNotFound();
});
