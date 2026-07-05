<?php

declare(strict_types=1);

namespace Capell\Tests\Packages\Feature;

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Capell\Core\ThemeStudio\Data\FooterData;
use Capell\Core\ThemeStudio\Data\GenericSectionData;
use Capell\Core\ThemeStudio\Data\NavigationData;
use Capell\Core\ThemeStudio\Data\ThemePageData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

use function Pest\Laravel\get;

use ReflectionMethod;
use RuntimeException;

require_once __DIR__ . '/../Support/ThemeLayoutNativeSupport.php';

/*
|--------------------------------------------------------------------------
| Theme demo renders completely (dataset-driven, real renderer)
|--------------------------------------------------------------------------
|
| The structural completeness contract (Arch) proves the SHAPE of a theme's
| seeded demo. This proves the demo actually RENDERS: each provider's homepage
| is rendered through the theme's real BladeThemeRenderer + section views, and
| every seeded section heading must appear in the output. A payload whose keys a
| view cannot read would render an empty section and be caught here, even though
| it passes the structural contract.
|
| Only themes that already ship a demo content provider are exercised, so this
| grows automatically as providers are added.
|
*/

dataset('themes_with_demo_content', function (): array {
    $root = dirname(__DIR__, 3);
    $cases = [];

    foreach (glob($root . '/packages/theme-*/src/Support/Demo/*DemoContent.php') ?: [] as $providerPath) {
        $slug = substr(basename(dirname($providerPath, 4)), mb_strlen('theme-'));
        $cases[$slug] = [$slug];
    }

    return $cases;
});

it('renders a complete homepage through the real theme renderer', function (string $slug): void {
    $studio = Str::studly($slug);
    $providerClass = "Capell\\ThemeStudio\\{$studio}\\Support\\Demo\\{$studio}DemoContent";
    $serviceProviderClass = "Capell\\ThemeStudio\\{$studio}\\{$studio}ThemeServiceProvider";
    $viewRoot = dirname(__DIR__, 3) . "/packages/theme-{$slug}/resources";

    expect(class_exists($providerClass))->toBeTrue()
        ->and(class_exists($serviceProviderClass))->toBeTrue();

    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled($serviceProviderClass::$packageName);

    View::addNamespace("capell-theme-{$slug}", $viewRoot . '/views');
    Lang::addNamespace("capell-theme-{$slug}", $viewRoot . '/lang');

    $registry = new ThemeRegistry;
    $serviceProvider = new $serviceProviderClass(app());
    (new ReflectionMethod($serviceProvider, 'boot'))->invoke($serviceProvider, $registry);
    app()->instance(ThemeRegistry::class, $registry);

    // Layout-native themes (converted to render through x-capell::layout +
    // layout-builder) register no ThemeRenderer, so this legacy-renderer
    // completeness check does not apply — see the dedicated
    // "layout-native theme demo renders completely" test below instead.
    if (themeIsLayoutNative($slug)) {
        expect(true)->toBeTrue();

        return;
    }

    $provider = new $providerClass;
    throw_unless($provider instanceof ProvidesThemeDemoContent, RuntimeException::class, "{$providerClass} must implement ProvidesThemeDemoContent.");

    $homepage = $provider->definitions($slug, Str::headline($slug), "https://{$slug}.test")[0];
    $renderData = $homepage->renderData;

    $sections = [];
    foreach ($homepage->sections() as $entry) {
        $type = $entry['type'] ?? null;
        $type = is_string($type) ? $type : '';
        unset($entry['type']);
        $sections[] = new GenericSectionData($type, $entry);
    }

    $seededBrand = data_get($renderData, 'navigation.brandName');

    $page = new ThemePageData(
        title: is_string($seededBrand) ? $seededBrand : Str::headline($slug),
        brand: new BrandProfileData(primaryColor: '#2563eb', surfaceColor: '#0b0b0f', foregroundColor: '#f8fafc'),
        sections: $sections,
        navigation: NavigationData::from($renderData['navigation']),
        footer: FooterData::from($renderData['footer']),
    );

    $html = $registry->renderer($slug)->render($page);

    // A complete multi-section page produces substantial output.
    expect(mb_strlen($html))->toBeGreaterThan(4000, "Theme [{$slug}] homepage rendered too little HTML — likely empty sections.");

    // Every seeded section whose view echoes its heading must surface that
    // heading — proof the view actually consumed the payload rather than
    // rendering empty. Views that lead with metrics/media (e.g. some proof
    // views) legitimately omit the heading, so only assert when the view shows
    // it; this self-adjusts per theme and stays strict for bespoke views.
    foreach ($homepage->sections() as $entry) {
        $heading = $entry['heading'] ?? null;
        $sectionType = $entry['type'] ?? null;

        if (! is_string($heading) || $heading === '' || ! is_string($sectionType)) {
            continue;
        }

        $viewFile = $viewRoot . '/views/sections/' . $sectionType . '.blade.php';

        if (! is_file($viewFile) || ! str_contains((string) file_get_contents($viewFile), '->heading')) {
            continue;
        }

        expect(str_contains($html, e($heading)))->toBeTrue("Theme [{$slug}] section [{$sectionType}] heading did not render: [{$heading}].");
    }

    // The seeded navigation brand must reach the rendered nav. Many nav views
    // read `data_get($section, 'brand', <translation default>)`, but NavigationData
    // exposes `brandName`; without the `brand` alias these render the generic
    // default instead of the seeded demo brand. Assert only when this theme's nav
    // view uses that read-path and a brand was seeded.
    $brandName = data_get($renderData, 'navigation.brandName');
    $navView = $viewRoot . '/views/sections/navigation.blade.php';

    if (is_string($brandName) && $brandName !== '' && is_file($navView)
        && str_contains((string) file_get_contents($navView), "data_get(\$section, 'brand'")
    ) {
        expect(str_contains($html, e($brandName)))->toBeTrue("Theme [{$slug}] navigation did not render the seeded brand: [{$brandName}].");
    }
})->with('themes_with_demo_content');

/*
|--------------------------------------------------------------------------
| Layout-native theme demo renders completely (Phase C)
|--------------------------------------------------------------------------
|
| Themes converted to render through x-capell::layout + layout-builder no
| longer register a legacy ThemeRenderer, so the real-renderer completeness
| check above does not apply to them. Instead this seeds each converted
| theme's demo content (which supplies layout-builder containers, per
| ThemeDemoPageInstaller) and asserts the demo homepage actually serves a 200
| over HTTP with no `data-section=` markers left over from the legacy
| section-rendering pipeline.
|
| themesConvertedToLayoutBuilder() is empty today, so this dataset has zero
| live cases until Phase C converts its first theme — that is expected.
|
| PHPUnit 12 treats an empty data provider as a hard error rather than a
| silent no-op, so the dataset() and it()->with() registration below is
| skipped entirely while the exemption list is empty; Phase C's first entry
| makes this block register the real dataset test again.
|
*/

if (themesConvertedToLayoutBuilder() !== []) {
    dataset('themes_converted_to_layout_builder', fn (): array => array_combine(
        themesConvertedToLayoutBuilder(),
        array_map(static fn (string $themeKey): array => [$themeKey], themesConvertedToLayoutBuilder()),
    ));

    it('renders a layout-native theme demo homepage with no legacy section markers', function (string $themeKey): void {
        [$pageUrl] = layoutNativeThemeCreatePage($themeKey, 'Layout Native Demo');

        $response = get($pageUrl->full_url);

        $response->assertOk();

        expect($response->getContent())->not->toContain('data-section=');
    })->with('themes_converted_to_layout_builder');
}
