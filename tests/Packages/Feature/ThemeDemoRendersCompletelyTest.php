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
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

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
    (new $serviceProviderClass(app()))->boot($registry);
    app()->instance(ThemeRegistry::class, $registry);

    $homepage = (new $providerClass)->definitions($slug, Str::headline($slug), "https://{$slug}.test")[0];
    $renderData = $homepage->renderData;

    $sections = [];
    foreach ($renderData['sections'] as $entry) {
        $type = (string) $entry['type'];
        unset($entry['type']);
        $sections[] = new GenericSectionData($type, $entry);
    }

    $page = new ThemePageData(
        title: (string) ($renderData['navigation']['brandName'] ?? Str::headline($slug)),
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
    foreach ($renderData['sections'] as $entry) {
        $heading = $entry['heading'] ?? null;

        if (! is_string($heading) || $heading === '') {
            continue;
        }

        $viewFile = $viewRoot . '/views/sections/' . $entry['type'] . '.blade.php';

        if (! is_file($viewFile) || ! str_contains((string) file_get_contents($viewFile), '->heading')) {
            continue;
        }

        expect(str_contains($html, e($heading)))->toBeTrue("Theme [{$slug}] section [{$entry['type']}] heading did not render: [{$heading}].");
    }
})->with('themes_with_demo_content');
