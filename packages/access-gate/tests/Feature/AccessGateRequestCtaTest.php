<?php

declare(strict_types=1);

use Capell\AccessGate\Models\Area;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

it('renders a public request cta on an ungated page', function (): void {
    $area = Area::factory()->create([
        'key' => 'preview',
    ]);

    Route::get('/access-gate-test/landing', fn (): string => Blade::render(
        '<x-capell-access-gate::request-cta :area="$area" email="mona@example.test" requested-url="https://example.test/preview" />',
        ['area' => $area],
    ));

    $this->get('/access-gate-test/landing')
        ->assertOk()
        ->assertSee('Request access')
        ->assertElementExists('input[name="area"][value="preview"]')
        ->assertElementExists('input[name="email"]')
        ->assertSee('mona@example.test')
        ->assertDontSee('handler_class')
        ->assertDontSee('SubmitAccessGatePublicAction')
        ->assertDontSee('http_webhook')
        ->assertDontSee('signed');
});

it('falls back to the built-in access gate request endpoint when public actions routes are not loaded', function (): void {
    $area = Area::factory()->create([
        'key' => 'preview',
    ]);

    $html = Blade::render(
        '<x-capell-access-gate::request-cta :area="$area" />',
        ['area' => $area],
    );

    expect($html)->toContain(route('capell-access-gate.request.store', ['area' => 'preview']))
        ->and($html)->toContain('Request access');
});
