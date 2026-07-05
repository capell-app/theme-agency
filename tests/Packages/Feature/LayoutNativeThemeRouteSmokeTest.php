<?php

declare(strict_types=1);

use function Pest\Laravel\get;

require_once __DIR__ . '/../Support/ThemeFrontendTestSupport.php';
require_once __DIR__ . '/../Support/ThemeLayoutNativeSupport.php';

/*
|--------------------------------------------------------------------------
| Layout-native theme route smoke test (Phase C)
|--------------------------------------------------------------------------
|
| Structurally mirrors ThemeFrontendRouteSmokeTest, but exercises themes
| converted to render through x-capell::layout + layout-builder instead of
| the legacy section-rendering pipeline: no `data-section=` markers should
| survive on a converted theme's real frontend page route.
|
| themesConvertedToLayoutBuilder() is empty today, so this dataset has zero
| live cases until Phase C converts its first theme — that is expected.
|
| PHPUnit 12 treats an empty data provider as a hard error rather than a
| silent no-op, so while the exemption list is empty this file registers a
| single explanatory placeholder test instead of the dataset test; Phase C's
| first entry switches this back to the real dataset-driven smoke test.
|
*/

if (themesConvertedToLayoutBuilder() === []) {
    it('has no layout-native themes to smoke test yet', function (): void {
        expect(themesConvertedToLayoutBuilder())->toBe([]);
    });
} else {
    /*
     * themeFrontendCreatePage() (tests/Packages/Support/ThemeFrontendTestSupport.php) is
     * NOT layout-native-compatible: themeFrontendBootTheme() only recognizes the 7-theme
     * allowlist in themeFrontendFirstPartyThemes() and unconditionally re-registers
     * whatever theme it boots with a LEGACY BladeThemeRenderer backed by the
     * ThemeFrontendStringSectionRenderer fixture — a fixture whose entire purpose is to
     * emit `data-section="..."` markers, exactly what this test asserts is ABSENT. So
     * layout-native themes use the shared layoutNativeThemeCreatePage() helper instead
     * (tests/Packages/Support/ThemeLayoutNativeSupport.php): it boots the theme's REAL
     * service provider (no fixture renderer) and seeds a real homepage via
     * ThemeDemoPageInstaller + the theme's own demo content provider, mirroring the same
     * boot/install sequence ThemeDemoRendersCompletelyTest's layout-native block and
     * LiquidGlassVisualProofTest use for real (non-fixture) theme providers.
     *
     * Layout-native pages render through the shared `x-capell::layout` component, which
     * emits no `data-theme="..."` attribute at all (that marker is unique to the legacy
     * ThemeFrontendStringSectionRenderer/theme-frontend-route fixtures) — so this smoke
     * test asserts the real, achievable signal instead: a 200 response, no leftover
     * `data-section=` markers, and the seeded page's own title appearing in the response
     * (proof the page-content widget rendered the page's real content, not a fixture).
     */
    dataset('layout-native frontend themes', fn (): array => array_combine(
        themesConvertedToLayoutBuilder(),
        array_map(static fn (string $themeKey): array => [$themeKey], themesConvertedToLayoutBuilder()),
    ));

    it('renders layout-native themes through the real frontend page route without legacy section markers', function (string $themeKey): void {
        [$pageUrl, $pageTitle] = layoutNativeThemeCreatePage($themeKey);

        $response = get($pageUrl->full_url);

        $response->assertOk();

        expect($response->getContent())
            ->not->toContain('data-section=')
            ->toContain(e($pageTitle));

        assertThemeFrontendPublicHtmlIsSafe($response);
    })->with('layout-native frontend themes');
}
