<?php

declare(strict_types=1);

use Capell\Newsletter\Actions\CreatePreferenceCenterTokenAction;
use Capell\Newsletter\Models\Segment;
use Capell\Newsletter\Models\Subscriber;
use Capell\Tests\Fixtures\Models\User;
use Illuminate\Database\Eloquent\Model;

it('keeps anonymous preference center HTML free of admin and model identifiers', function (): void {
    [$token, $segment] = newsletterPublicPreferenceCenterFixture();

    $response = $this->get(route('capell-newsletter.preferences.show', ['token' => $token]));

    $response
        ->assertOk()
        ->assertSee('value="' . $segment->handle . '"', false)
        ->assertDontSee('value="' . newsletterPublicPreferenceCenterModelKey($segment) . '"', false)
        ->assertDontSee('/admin', false)
        ->assertDontSee('filament', false)
        ->assertDontSee('wire:', false)
        ->assertDontSee('newsletter_segments', false)
        ->assertDontSee('newsletter_subscribers', false)
        ->assertDontSee('subscriber_id', false)
        ->assertDontSee('capell-app/newsletter', false)
        ->assertDontSee('signed-preview', false);
});

it('keeps non-admin preference center HTML free of admin and model identifiers', function (): void {
    [$token, $segment] = newsletterPublicPreferenceCenterFixture();

    $this->actingAs(User::factory()->createOne());

    $response = $this->get(route('capell-newsletter.preferences.show', ['token' => $token]));

    $response
        ->assertOk()
        ->assertSee('value="' . $segment->handle . '"', false)
        ->assertDontSee('value="' . newsletterPublicPreferenceCenterModelKey($segment) . '"', false)
        ->assertDontSee('/admin', false)
        ->assertDontSee('filament', false)
        ->assertDontSee('wire:', false)
        ->assertDontSee('newsletter_segments', false)
        ->assertDontSee('newsletter_subscribers', false)
        ->assertDontSee('subscriber_id', false)
        ->assertDontSee('capell-app/newsletter', false)
        ->assertDontSee('signed-preview', false);
});

/**
 * @return array{0: string, 1: Segment}
 */
function newsletterPublicPreferenceCenterFixture(): array
{
    $subscriber = Subscriber::factory()->createOne();

    $segment = Segment::query()->create([
        'site_id' => $subscriber->site_id,
        'name' => 'Product updates',
        'handle' => 'product-updates',
        'type' => 'static',
        'filters' => [],
        'is_active' => true,
    ]);

    return [
        CreatePreferenceCenterTokenAction::run($subscriber),
        $segment,
    ];
}

function newsletterPublicPreferenceCenterModelKey(Model $model): string
{
    $key = $model->getKey();

    return is_string($key) || is_int($key) || is_float($key)
        ? (string) $key
        : '';
}
