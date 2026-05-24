<?php

declare(strict_types=1);

require_once __DIR__ . '/../Support/ThemeFrontendTestSupport.php';

use function Pest\Laravel\get;

dataset('first party frontend themes', [
    'default' => ['default'],
    'agency' => ['agency'],
    'corporate' => ['corporate'],
    'commerce' => ['commerce'],
    'healthcare' => ['healthcare'],
    'saas' => ['saas'],
    'education' => ['education'],
    'knowledge' => ['knowledge'],
    'local-services' => ['local-services'],
    'nonprofit' => ['nonprofit'],
    'portfolio' => ['portfolio'],
]);

it('renders first-party themes through the real frontend page route', function (string $themeKey): void {
    $pageUrl = themeFrontendCreatePage($themeKey);

    $response = get($pageUrl->full_url);

    $response->assertOk();
    $response->assertSeeText(ucfirst(str_replace('-', ' ', $themeKey)) . ' Route Smoke');
    $response->assertSeeText('Theme route smoke CTA');

    expect($response->getContent())
        ->toContain('data-theme="' . $themeKey . '"')
        ->toContain('data-section="hero"');

    assertThemeFrontendPublicHtmlIsSafe($response);
})->with('first party frontend themes');
