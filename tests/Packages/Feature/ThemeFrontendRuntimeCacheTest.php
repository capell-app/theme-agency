<?php

declare(strict_types=1);

require_once __DIR__ . '/../Support/ThemeFrontendTestSupport.php';

use Capell\Core\Facades\CapellCore;
use Capell\HtmlCache\Providers\HtmlCacheServiceProvider;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;

it('uses the selected database theme key and preset on a real route', function (): void {
    $pageUrl = themeFrontendCreatePage('saas', 'saas');

    $response = get($pageUrl->full_url);

    $response->assertOk();

    expect($response->getContent())
        ->toContain('data-theme="saas"')
        ->toContain('Theme route smoke CTA');

    assertThemeFrontendPublicHtmlIsSafe($response);
});

it('lets database brand and token overrides beat package preset defaults on a real route', function (): void {
    $pageUrl = themeFrontendCreatePage('saas', 'saas');

    themeFrontendConfigureRuntime(
        themeKey: 'saas',
        presetKey: 'saas',
        brandProfile: ['primaryColor' => '#111827'],
        themeOverrides: ['saas' => ['primaryColor' => '#dc2626', 'accentColor' => '#16a34a']],
    );

    $response = get($pageUrl->full_url);

    $response->assertOk();

    expect($response->getContent())
        ->toContain('#dc2626')
        ->toContain('#16a34a')
        ->not->toContain('#2563eb');

    assertThemeFrontendPublicHtmlIsSafe($response);
});

it('falls back to the selected theme default preset when saved preset settings are stale on a real route', function (): void {
    $pageUrl = themeFrontendCreatePage('saas', 'boardroom');

    $response = get($pageUrl->full_url);

    $response->assertOk();

    expect($response->getContent())
        ->toContain('data-theme="saas"')
        ->toContain('#2563eb');

    assertThemeFrontendPublicHtmlIsSafe($response);
});

it('caches public theme route output without authoring surface', function (): void {
    themeFrontendMigrateHtmlCacheTables();

    CapellCore::forcePackageInstalled(HtmlCacheServiceProvider::$packageName);
    app()->register(HtmlCacheServiceProvider::class);

    Route::getRoutes()->getByName('capell-frontend.page')?->middleware('frontend.cache');

    config()->set('capell-html-cache.enabled', true);
    config()->set('capell-html-cache.write_enabled', true);
    config()->set('capell-html-cache.cache_ttl', '3600');
    config()->set('capell-html-cache.cache_skip_authenticated', false);

    $pageUrl = themeFrontendCreatePage('saas', 'saas');

    $firstResponse = get($pageUrl->full_url);
    $secondResponse = get($pageUrl->full_url);

    $firstResponse->assertOk();
    $secondResponse->assertOk();

    expect($firstResponse->baseResponse->headers->get('X-Frontend-Cache'))->toBe('MISS')
        ->and($secondResponse->baseResponse->headers->get('X-Frontend-Cache'))->toBe('HIT')
        ->and($secondResponse->getContent())->toContain('Theme route smoke CTA');

    assertThemeFrontendPublicHtmlIsSafe($secondResponse);
});
