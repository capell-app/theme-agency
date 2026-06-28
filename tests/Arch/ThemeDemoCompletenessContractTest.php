<?php

declare(strict_types=1);

namespace Capell\Tests\Arch;

use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Theme demo completeness contract (dataset-driven, boot-free)
|--------------------------------------------------------------------------
|
| The high minimum standard every theme's demo must meet so the live
| /theme-<slug> render (and a real screenshot of it) reads as a complete,
| individual site — never the generic five-section skeleton.
|
| A theme is COMPLETE when it ships a ProvidesThemeDemoContent provider
| (Capell\ThemeStudio\<Studio>\Support\Demo\<Studio>DemoContent) whose
| definitions() return all seven foundation surfaces, and EACH surface:
|   - carries an ordered render_data['sections'] list of {type, ...} maps,
|   - opens with a hero and contains a cta,
|   - meets a per-surface minimum section count (no sparse surfaces),
|   - carries real navigation + footer chrome,
|   - is built only from section types the theme actually registers a
|     renderer for (so nothing 500s or silently falls back), and
|   - contains no placeholder / weak demo copy.
|
| Pure static instantiation (the providers only read static demo media), so
| this runs in the Arch suite without booting the application container.
|
*/

/** @var list<string> */
const FOUNDATION_SURFACES = ['homepage', 'directory', 'detail', 'contact', 'empty', 'not-found', 'cta'];

/** Minimum total sections per surface — a complete surface is never hero-only. */
const MIN_SECTIONS = [
    'homepage' => 6,
    'directory' => 3,
    'detail' => 3,
    'contact' => 3,
    'empty' => 3,
    'not-found' => 2,
    'cta' => 3,
];

/** Minimum bespoke "signature" sections (beyond the shared core types) per surface. */
const MIN_SIGNATURE_SECTIONS = [
    'homepage' => 2,
    'detail' => 1,
];

/** Section types shared by every theme — not counted as "signature". */
const CORE_SECTION_TYPES = ['hero', 'cta', 'proof', 'features', 'content-listing', 'navigation', 'footer'];

/** Lower-cased substrings that mark lazy placeholder copy. */
const WEAK_COPY_PATTERNS = [
    'lorem',
    'ipsum',
    'placeholder',
    'preview item',
    'preview content',
    'sample copy',
    'theme demo',
    'demo content',
    'generic',
    'page page',
    'todo',
    'fixme',
    'lentejas',
    'tbd',
];

/**
 * Themes excluded from the demo-content contract (non-standard packs).
 *
 * @var list<string>
 */
const DEMO_CONTRACT_EXCLUSIONS = [
    'inertia-bookings',
    'inertia-bookings-react',
    'inertia-bookings-vue',
];

dataset('themes_for_demo_contract', function (): array {
    $root = dirname(__DIR__, 2);
    $cases = [];

    foreach (glob($root . '/packages/theme-*/capell.json') ?: [] as $manifestPath) {
        $slug = substr(basename(dirname($manifestPath)), mb_strlen('theme-'));

        if (in_array($slug, DEMO_CONTRACT_EXCLUSIONS, true)) {
            continue;
        }

        /** @var array<string, mixed> $manifest */
        $manifest = json_decode((string) file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);

        if (($manifest['kind'] ?? null) !== 'theme') {
            continue;
        }

        $cases[$slug] = [$slug, dirname($manifestPath)];
    }

    return $cases;
});

/**
 * Section types the theme can render — every section blade view it ships.
 *
 * Each section renderer maps a type key to a `sections.<key>` view, so the set of
 * view files is the registration-style-agnostic source of truth for what renders
 * (works whether a provider registers literally or loops over includedSections).
 *
 * @return list<string>
 */
function renderableSectionTypes(string $directory): array
{
    $types = [];

    foreach (glob($directory . '/resources/views/sections/*.blade.php') ?: [] as $viewPath) {
        $types[] = basename($viewPath, '.blade.php');
    }

    return array_values(array_unique($types));
}

/**
 * Recursively collect every string scalar in a surface payload (for copy auditing).
 *
 * @return list<string>
 */
function collectStrings(mixed $value): array
{
    if (is_string($value)) {
        return [$value];
    }

    if (! is_array($value)) {
        return [];
    }

    $strings = [];

    foreach ($value as $item) {
        foreach (collectStrings($item) as $string) {
            $strings[] = $string;
        }
    }

    return $strings;
}

it('theme ships complete, individual demo content for every foundation surface', function (string $slug, string $directory): void {
    $studio = Str::studly($slug);
    $providerClass = "Capell\\ThemeStudio\\{$studio}\\Support\\Demo\\{$studio}DemoContent";

    expect(class_exists($providerClass))->toBeTrue(
        "Theme [{$slug}] has no demo content provider. Expected {$providerClass} implementing ProvidesThemeDemoContent.",
    );

    $provider = new $providerClass;
    expect($provider)->toBeInstanceOf(ProvidesThemeDemoContent::class);

    /** @var array<int, ThemeDemoPageDefinition> $definitions */
    $definitions = $provider->definitions($slug, Str::headline($slug), "https://{$slug}.test");

    $bySurface = [];
    foreach ($definitions as $definition) {
        $bySurface[$definition->surface] = $definition;
    }

    // All seven foundation surfaces present.
    expect(array_keys($bySurface))->toEqualCanonicalizing(FOUNDATION_SURFACES);

    $registered = renderableSectionTypes($directory);
    $brandNames = [];

    foreach (FOUNDATION_SURFACES as $surface) {
        $definition = $bySurface[$surface];
        $renderData = $definition->renderData;
        $sections = $renderData['sections'] ?? null;

        expect(is_array($sections) && array_is_list($sections) && $sections !== [])->toBeTrue(
            "Theme [{$slug}] surface [{$surface}] must carry an ordered render_data['sections'] list.",
        );

        $types = array_map(static fn (array $s): string => (string) ($s['type'] ?? ''), $sections);

        // Opens with a hero, contains a cta.
        expect($types[0])->toBe('hero', "Theme [{$slug}] surface [{$surface}] must open with a hero.");
        expect(in_array('cta', $types, true))->toBeTrue("Theme [{$slug}] surface [{$surface}] must contain a cta.");

        // Not sparse.
        expect(count($sections))->toBeGreaterThanOrEqual(
            MIN_SECTIONS[$surface],
            "Theme [{$slug}] surface [{$surface}] has " . count($sections) . ' sections; minimum is ' . MIN_SECTIONS[$surface] . '.',
        );

        // Individual: enough bespoke signature sections.
        if (isset(MIN_SIGNATURE_SECTIONS[$surface])) {
            $signature = array_values(array_filter($types, static fn (string $t): bool => $t !== '' && ! in_array($t, CORE_SECTION_TYPES, true)));
            expect(count($signature))->toBeGreaterThanOrEqual(
                MIN_SIGNATURE_SECTIONS[$surface],
                "Theme [{$slug}] surface [{$surface}] needs at least " . MIN_SIGNATURE_SECTIONS[$surface] . ' signature section(s); found [' . implode(', ', $signature) . '].',
            );
        }

        // Every seeded type renders: a registered renderer (or a core type) exists.
        foreach ($types as $type) {
            expect($type)->not->toBe('', "Theme [{$slug}] surface [{$surface}] has a section without a type.");
            expect(in_array($type, CORE_SECTION_TYPES, true) || in_array($type, $registered, true))->toBeTrue(
                "Theme [{$slug}] surface [{$surface}] seeds section type [{$type}] but the theme registers no renderer for it.",
            );
        }

        // Chrome.
        $navItems = $renderData['navigation']['items'] ?? [];
        $brandName = $renderData['navigation']['brandName'] ?? null;
        $footerColumns = $renderData['footer']['columns'] ?? [];

        expect(is_string($brandName) && $brandName !== '')->toBeTrue("Theme [{$slug}] surface [{$surface}] needs a navigation brandName.");
        expect(count(is_array($navItems) ? $navItems : []))->toBeGreaterThanOrEqual(3, "Theme [{$slug}] surface [{$surface}] needs at least 3 navigation items.");
        expect(count(is_array($footerColumns) ? $footerColumns : []))->toBeGreaterThanOrEqual(2, "Theme [{$slug}] surface [{$surface}] needs at least 2 footer columns.");

        $brandNames[$brandName] = true;

        // No placeholder copy.
        $haystack = mb_strtolower(implode("\n", collectStrings($sections)));
        foreach (WEAK_COPY_PATTERNS as $pattern) {
            expect(str_contains($haystack, $pattern))->toBeFalse(
                "Theme [{$slug}] surface [{$surface}] contains weak demo copy: [{$pattern}].",
            );
        }
    }

    // Brand is consistent across the whole demo site.
    expect(count($brandNames))->toBe(1, "Theme [{$slug}] uses inconsistent brand names across surfaces: [" . implode(', ', array_keys($brandNames)) . '].');
})->with('themes_for_demo_contract');
