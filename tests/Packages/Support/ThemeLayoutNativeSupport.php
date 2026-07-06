<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Theme;
use Capell\Core\ThemeStudio\Contracts\ThemeRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

/*
 * Phase C will start converting individual themes from the legacy
 * section-rendering pipeline to render through x-capell::layout +
 * layout-builder instead. A converted theme registers itself with
 * ThemeRegistry::register() but passes no ThemeRenderer, so
 * ThemeRegistry::hasRenderer() returns false for it — this helper is the
 * shared way test suites ask "has this theme been converted?" without each
 * guard test re-deriving the same negation.
 */

/**
 * Whether a theme has been converted to render through layout-builder
 * instead of registering a legacy {@see ThemeRenderer}.
 */
function themeIsLayoutNative(string $themeKey): bool
{
    return ! resolve(ThemeRegistry::class)->hasRenderer($themeKey);
}

/**
 * The single source of truth for which theme keys have completed the Phase C
 * conversion to layout-builder rendering. Guard/regression tests across
 * packages/theme-foundation and tests/Packages use this to exempt converted
 * themes from assertions that only apply to the legacy section-rendering
 * pipeline (per-theme theme-*.css, sections/ directories, page.blade.php
 * landmark structure, and so on). It starts empty — no theme has converted
 * yet — and is a one-way ratchet: entries are only ever added.
 *
 * @return list<string>
 */
function themesConvertedToLayoutBuilder(): array
{
    return ['liquid-glass', 'night-shift', 'reading-room', 'main-stage', 'call-out'];
}

/**
 * Boots a layout-native theme's REAL service provider (register + boot
 * against a fresh ThemeRegistry — no legacy fixture renderer involved) and
 * seeds a real homepage through ThemeDemoPageInstaller + the theme's own
 * ProvidesThemeDemoContent implementation, returning the seeded homepage's
 * resolved PageUrl and title so the caller can exercise the real HTTP route.
 *
 * Shared by every layout-native integration test that needs a real,
 * non-fixture theme provider boot + demo seed (LayoutNativeThemeRouteSmokeTest,
 * LiquidGlassVisualProofTest, ThemeDemoRendersCompletelyTest's layout-native
 * block) so the sequence is implemented once instead of re-derived per file.
 *
 * @return array{0: PageUrl, 1: string}
 */
function layoutNativeThemeCreatePage(string $themeKey, string $siteNameSuffix = 'Layout Native Route Smoke'): array
{
    $studio = Str::studly($themeKey);
    $providerClass = "Capell\\ThemeStudio\\{$studio}\\{$studio}ThemeServiceProvider";
    $demoProviderClass = "Capell\\ThemeStudio\\{$studio}\\Support\\Demo\\{$studio}DemoContent";
    $viewRoot = dirname(__DIR__, 3) . "/packages/theme-{$themeKey}/resources";

    throw_unless(class_exists($providerClass), RuntimeException::class, "{$providerClass} must exist.");
    throw_unless(class_exists($demoProviderClass), RuntimeException::class, "{$demoProviderClass} must exist.");

    CapellCore::forcePackageInstalled($providerClass::$packageName);

    View::addNamespace("capell-theme-{$themeKey}", $viewRoot . '/views');
    Lang::addNamespace("capell-theme-{$themeKey}", $viewRoot . '/lang');

    $registry = resolve(ThemeRegistry::class);
    $provider = new $providerClass(app());
    $provider->register();
    $provider->boot($registry);

    $demoProvider = new $demoProviderClass;
    throw_unless($demoProvider instanceof ProvidesThemeDemoContent, RuntimeException::class, "{$demoProviderClass} must implement ProvidesThemeDemoContent.");

    ThemeDemoPageInstaller::run(
        data: new ThemeDemoInstallData(
            siteNames: [Str::headline($themeKey) . ' ' . $siteNameSuffix],
            languageCodes: ['en'],
            baseUrl: "https://{$themeKey}." . Str::slug($siteNameSuffix) . '.test',
        ),
        themeKey: $themeKey,
        themeName: Str::headline($themeKey),
        contentProvider: $demoProvider,
    );

    $homepage = Page::query()
        ->where('meta->theme_demo->theme_key', $themeKey)
        ->where('meta->theme_demo->surface', 'homepage')
        ->first();

    throw_unless($homepage instanceof Page, RuntimeException::class, "Theme [{$themeKey}] did not seed a homepage.");

    $homepage->loadMissing('site.theme');

    layoutNativeDisableThemeChrome($homepage);

    $homepage->loadMissing(['pageUrl.siteDomain', 'translations']);

    $pageUrl = $homepage->pageUrl;

    throw_unless($pageUrl instanceof PageUrl, RuntimeException::class, "Theme [{$themeKey}] homepage did not resolve a PageUrl.");

    $title = (string) ($homepage->translation?->title ?? $homepage->name);

    return [$pageUrl, $title];
}

/**
 * Disables the default Foundation header/footer chrome on a seeded demo
 * homepage's theme.
 *
 * Foundation's default header/footer chrome renders the search package's
 * RegisterHeaderSearchHook, which independently issues its own database
 * queries (e.g. a distinct `taggable_type` lookup for search facets) —
 * unrelated to a layout-native theme's own containers/widgets, but enough to
 * trip PublicViewQueryGuard on a page that keeps the default chrome.
 * Disabling header/footer here mirrors the same sidestep
 * FrontendWebsiteAccessTest uses for its own real-HTTP smoke coverage; the
 * guard's own dedicated coverage lives in the public render tests for those
 * packages, not here.
 *
 * Expects `$homepage->site.theme` to already be loaded (or loadable).
 */
function layoutNativeDisableThemeChrome(Page $homepage): void
{
    $theme = $homepage->site?->theme;

    if (! $theme instanceof Theme) {
        return;
    }

    $theme->forceFill([
        'meta' => [
            ...(is_array($theme->meta) ? $theme->meta : []),
            'header' => false,
            'footer' => false,
        ],
    ])->save();
}
