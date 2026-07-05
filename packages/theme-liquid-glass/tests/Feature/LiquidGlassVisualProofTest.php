<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\LiquidGlass\LiquidGlassThemeServiceProvider;

use function Pest\Laravel\get;

require_once __DIR__ . '/../../../../tests/Packages/Support/ThemeLayoutNativeSupport.php';

/*
|--------------------------------------------------------------------------
| Liquid Glass visual proof (real render path)
|--------------------------------------------------------------------------
|
| Liquid Glass is definition-only (see LiquidGlassThemeServiceProvider): it
| registers no ThemeRenderer, so it no longer ships a page shell view and
| every surface renders through the shared `x-capell::layout` +
| layout-builder container pipeline instead of the legacy
| section-rendering pipeline.
|
| CSS custom-property token injection is NOT lost by that conversion: it is
| independently supplied by the frontend package's `HeadClose` render hook
| (see `Capell\Frontend\Providers\FrontendServiceProvider::register()`),
| which inlines `<style data-capell-theme-tokens>:root { --theme-* }</style>`
| via `ResolveThemeRuntimeAction` whenever `ThemeRegistry::has()` is true for
| the active theme — a check that does not require a registered
| `ThemeRenderer`. So this test boots the real theme provider, seeds real
| demo content through `ThemeDemoPageInstaller`, and asserts against the
| real HTTP response instead of a hand-authored fixture document, using the
| same shared boot/seed/disable-chrome helpers
| (tests/Packages/Support/ThemeLayoutNativeSupport.php) that
| `LayoutNativeThemeRouteSmokeTest` and `ThemeDemoRendersCompletelyTest`'s
| layout-native block use for other layout-native themes.
|
*/

it('injects real theme tokens and renders seeded content on the real Liquid Glass page route', function (): void {
    $registry = resolve(ThemeRegistry::class);
    $registry->reset();

    [$pageUrl, $title] = layoutNativeThemeCreatePage(LiquidGlassThemeServiceProvider::THEME_KEY, 'Visual Proof');

    expect($registry->hasRenderer(LiquidGlassThemeServiceProvider::THEME_KEY))->toBeFalse();

    $response = get($pageUrl->full_url);

    $response->assertOk();

    $html = $response->getContent();

    expect($html)->toBeString();

    expect($html)
        ->toContain('data-capell-theme-tokens')
        ->toContain('--theme-primary:')
        ->toContain('--theme-accent:')
        ->toContain('--theme-surface:')
        ->toContain('--theme-foreground:')
        ->not->toContain('data-section=')
        ->not->toContain('capell-app/theme-liquid-glass')
        ->not->toContain('authoring')
        ->toContain(e($title));

    $registry->reset();
    CapellCore::clearPackages();
});
