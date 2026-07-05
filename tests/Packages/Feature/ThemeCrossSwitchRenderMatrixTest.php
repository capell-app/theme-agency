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
use ReflectionMethod;
use RuntimeException;
use Throwable;

require_once __DIR__ . '/../Support/ThemeLayoutNativeSupport.php';

/*
|--------------------------------------------------------------------------
| Cross-theme render matrix (Wave 11.1 theme-switch resilience)
|--------------------------------------------------------------------------
|
| ThemeSwitchFallbackResilienceTest proves every theme registers the
| 'content-listing' renderer that custom sections fall back to. This test
| proves the fallback actually WORKS end to end: every theme's real demo
| homepage — built from its own custom section keys — is rendered through
| every OTHER theme's real BladeThemeRenderer + registered section renderers,
| exactly as it would be the moment a site switches themes. No pairing may
| throw, and every render must produce non-empty HTML.
|
| All themes are booted once into a single shared ThemeRegistry (keyed per
| theme, so registrations cannot collide) rather than per-pair, keeping an
| N*(N-1) matrix cheap and fully in-memory — no HTTP, no database.
|
*/

/**
 * @return array<string, array{0: string}>
 */
function themeSwitchMatrixSlugs(): array
{
    $root = dirname(__DIR__, 3);
    $cases = [];

    foreach (glob($root . '/packages/theme-*/src/Support/Demo/*DemoContent.php') ?: [] as $providerPath) {
        $slug = substr(basename(dirname($providerPath, 4)), mb_strlen('theme-'));
        $cases[$slug] = [$slug];
    }

    return $cases;
}

it('renders every theme\'s demo homepage under every other theme\'s renderer without error', function (): void {
    $slugs = array_column(themeSwitchMatrixSlugs(), 0);

    CapellCore::clearPackages();

    $registry = new ThemeRegistry;
    app()->instance(ThemeRegistry::class, $registry);

    $pages = [];

    foreach ($slugs as $slug) {
        $studio = Str::studly($slug);
        $providerClass = "Capell\\ThemeStudio\\{$studio}\\Support\\Demo\\{$studio}DemoContent";
        $serviceProviderClass = "Capell\\ThemeStudio\\{$studio}\\{$studio}ThemeServiceProvider";
        $viewRoot = dirname(__DIR__, 3) . "/packages/theme-{$slug}/resources";

        throw_unless(class_exists($providerClass), RuntimeException::class, "{$providerClass} must exist.");
        throw_unless(class_exists($serviceProviderClass), RuntimeException::class, "{$serviceProviderClass} must exist.");

        CapellCore::forcePackageInstalled($serviceProviderClass::$packageName);

        View::addNamespace("capell-theme-{$slug}", $viewRoot . '/views');
        Lang::addNamespace("capell-theme-{$slug}", $viewRoot . '/lang');

        $serviceProvider = new $serviceProviderClass(app());
        (new ReflectionMethod($serviceProvider, 'boot'))->invoke($serviceProvider, $registry);

        // Layout-native themes (converted to render through x-capell::layout
        // + layout-builder) register no ThemeRenderer, so they have nothing
        // for this legacy section-render matrix to exercise — skip them.
        if (themeIsLayoutNative($slug)) {
            continue;
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

        $pages[$slug] = new ThemePageData(
            title: is_string($seededBrand) ? $seededBrand : Str::headline($slug),
            brand: new BrandProfileData(primaryColor: '#2563eb', surfaceColor: '#0b0b0f', foregroundColor: '#f8fafc'),
            sections: $sections,
            navigation: NavigationData::from($renderData['navigation']),
            footer: FooterData::from($renderData['footer']),
        );
    }

    $failures = [];
    $rendererBackedSlugs = array_values(array_filter($slugs, static fn (string $slug): bool => ! themeIsLayoutNative($slug)));

    foreach ($pages as $sourceSlug => $page) {
        foreach ($rendererBackedSlugs as $targetSlug) {
            if ($targetSlug === $sourceSlug) {
                continue;
            }

            try {
                $html = $registry->renderer($targetSlug)->render($page);
            } catch (Throwable $exception) {
                $failures[] = "[{$sourceSlug}] -> [{$targetSlug}] threw: {$exception->getMessage()}";

                continue;
            }

            if ($html === '') {
                $failures[] = "[{$sourceSlug}] -> [{$targetSlug}] rendered empty output.";
            }
        }
    }

    expect($failures)->toBe([], 'Theme-switch render matrix found ' . count($failures) . " broken pairing(s):\n" . implode("\n", $failures));
});
