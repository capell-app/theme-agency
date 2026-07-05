<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Illuminate\Support\Str;

require_once __DIR__ . '/../Support/ThemeLayoutNativeSupport.php';
require_once __DIR__ . '/../Support/ThemeFrontendTestSupport.php';

/*
|--------------------------------------------------------------------------
| Layout-native declared list vs. live behavioral check tripwire
|--------------------------------------------------------------------------
|
| themeIsLayoutNative() derives from ThemeRegistry::hasRenderer() — a LIVE,
| behavioral check performed against whatever a theme's service provider
| actually registers. themesConvertedToLayoutBuilder() is a DECLARED,
| hardcoded list maintained by hand as Phase C converts themes one at a
| time. Nothing else in the suite asserts these two sources of truth agree.
|
| Concretely: if a future refactor accidentally drops the `themeRenderer:`
| argument from one theme's ThemeRegistry::register() call (a bug, not a
| Phase C conversion), themeIsLayoutNative() would wrongly report that theme
| as "layout-native" and behavioral tests such as ThemeDemoRendersCompletelyTest
| would silently SKIP real-render coverage for it — while every list-based
| guard would keep passing because the list itself was never touched. Real
| render coverage would evaporate with zero red anywhere.
|
| This test boots every catalogue theme's real service provider into a fresh
| ThemeRegistry (mirroring ThemeDemoRendersCompletelyTest's boot sequence) and
| asserts the set of themes ThemeRegistry::hasRenderer() reports as false is
| EXACTLY the declared themesConvertedToLayoutBuilder() list — in both
| directions. Today all 19 catalogue themes register a renderer and the list
| is empty, so this currently asserts "the empty set equals the empty list" —
| a trivial pass that genuinely trips the moment either side drifts from the
| other, whether that drift is a real Phase C conversion forgetting to update
| the list, or an unrelated bug that accidentally strips a renderer.
|
*/

/**
 * @return array<int, array{themeKey: string, package: string}>
 */
function themeLayoutNativeCatalogueThemes(): array
{
    $decoded = json_decode(
        (string) file_get_contents(dirname(__DIR__, 3) . '/docs/themes.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($decoded) && isset($decoded['themes']) && is_array($decoded['themes']), RuntimeException::class, 'docs/themes.json must contain a themes array.');

    $themes = [];

    foreach ($decoded['themes'] as $theme) {
        if (! is_array($theme)) {
            continue;
        }

        $themeKey = $theme['themeKey'] ?? null;
        $package = $theme['package'] ?? null;

        throw_unless(is_string($themeKey) && is_string($package), RuntimeException::class, 'Theme catalogue entries must have string themeKey and package.');

        $themes[] = ['themeKey' => $themeKey, 'package' => $package];
    }

    return $themes;
}

it('keeps themesConvertedToLayoutBuilder() in sync with ThemeRegistry::hasRenderer() for every catalogue theme', function (): void {
    CapellCore::clearPackages();

    $registry = new ThemeRegistry;
    app()->instance(ThemeRegistry::class, $registry);

    $rendererlessThemeKeys = [];

    foreach (themeLayoutNativeCatalogueThemes() as $theme) {
        $themeKey = $theme['themeKey'];
        $package = $theme['package'];

        CapellCore::forcePackageInstalled($package);

        if ($themeKey === 'default') {
            // Foundation boots through Spatie's PackageServiceProvider
            // lifecycle (packageBooted(), gated by afterResolving/resolved
            // hooks against ThemeRegistry) rather than the plain
            // register()/boot(ThemeRegistry) pair every other theme uses, so
            // it cannot be reflected the same way. themeFrontendBootTheme()
            // already encodes Foundation's real boot sequence — reuse it
            // rather than re-deriving it here.
            themeFrontendBootTheme('default');
        } else {
            $packageDirectory = 'theme-' . $themeKey;
            $studio = Str::studly($themeKey);
            $providerClass = "Capell\\ThemeStudio\\{$studio}\\{$studio}ThemeServiceProvider";

            throw_unless(class_exists($providerClass), RuntimeException::class, "{$providerClass} must exist for catalogue theme [{$themeKey}] (package directory [{$packageDirectory}]).");

            $provider = new $providerClass(app());
            $provider->register();

            throw_unless(method_exists($provider, 'boot'), RuntimeException::class, "{$providerClass} must declare boot(ThemeRegistry \$registry).");

            (new ReflectionMethod($provider, 'boot'))->invoke($provider, $registry);
        }

        if (! $registry->hasRenderer($themeKey)) {
            $rendererlessThemeKeys[] = $themeKey;
        }
    }

    sort($rendererlessThemeKeys);
    $declaredConvertedThemeKeys = themesConvertedToLayoutBuilder();
    sort($declaredConvertedThemeKeys);

    expect($rendererlessThemeKeys)->toBe(
        $declaredConvertedThemeKeys,
        'Every catalogue theme ThemeRegistry::hasRenderer() reports as false must be declared in themesConvertedToLayoutBuilder(), and vice versa. '
        . 'A theme appearing on only one side means either a Phase C conversion forgot to update the declared list, or an unrelated bug (e.g. a '
        . 'dropped `themeRenderer:` argument in ThemeRegistry::register()) silently made a theme look layout-native. '
        . 'Rendererless (live): [' . implode(', ', $rendererlessThemeKeys) . ']. Declared: [' . implode(', ', $declaredConvertedThemeKeys) . '].',
    );
});
