<?php

declare(strict_types=1);

use Capell\PrivacyCenter\Enums\ConsentDecision;
use Capell\PrivacyCenter\Enums\CookieCategory;
use Capell\PrivacyCenter\Models\ConsentRecord;
use Capell\PrivacyCenter\Tests\PrivacyCenterTestCase;
use Illuminate\Support\Facades\Route;

uses(PrivacyCenterTestCase::class);

it('renders a public cache-safe consent preference center without admin internals', function (): void {
    expect(Route::has('capell-privacy-center.consent.show'))->toBeTrue()
        ->and(Route::has('capell-privacy-center.consent.store'))->toBeTrue();

    $response = $this->get(route('capell-privacy-center.consent.show'));

    $response->assertOk()
        ->assertSee(__('capell-privacy-center::privacy.public.preferences.title'))
        ->assertSee(CookieCategory::Analytics->getLabel())
        ->assertDontSee('filament', false)
        ->assertDontSee('/admin', false)
        ->assertDontSee('privacy_consent_records')
        ->assertDontSee('subject_id')
        ->assertDontSee('policy_id');
});

it('records public consent preferences through the consent ledger action', function (): void {
    $response = $this->post(route('capell-privacy-center.consent.store'), [
        'categories' => [
            CookieCategory::Analytics->value,
            CookieCategory::Preferences->value,
        ],
    ]);

    $response->assertRedirect(route('capell-privacy-center.consent.show'));

    expect(ConsentRecord::query()->count())->toBe(count(CookieCategory::cases()))
        ->and(ConsentRecord::query()
            ->where('category', CookieCategory::Essential)
            ->where('decision', ConsentDecision::Granted)
            ->exists())->toBeTrue()
        ->and(ConsentRecord::query()
            ->where('category', CookieCategory::Analytics)
            ->where('decision', ConsentDecision::Granted)
            ->exists())->toBeTrue()
        ->and(ConsentRecord::query()
            ->where('category', CookieCategory::Marketing)
            ->where('decision', ConsentDecision::Denied)
            ->exists())->toBeTrue();
});
