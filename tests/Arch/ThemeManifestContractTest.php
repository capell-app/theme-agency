<?php

declare(strict_types=1);

namespace Capell\Tests\Arch;

/*
|--------------------------------------------------------------------------
| Theme package manifest contract (dataset-driven)
|--------------------------------------------------------------------------
|
| This single dataset test replaces the 72 near-identical per-theme
| tests/Unit/ManifestRequirementsTest.php files. Every standard theme manifest
| (capell.json) follows the same slug-derived contract, so one test asserting
| that contract over every conforming theme is both shorter and stronger than 72
| hand-maintained copies that differed only by slug.
|
| Bespoke manifest tests are intentionally NOT folded in and keep their own
| files: the 36 non-theme feature packages (payments, blog, …) assert
| package-specific model/route/setting/command contributions, and the
| inertia-bookings react/vue packs are kind="plugin" component packs with their
| own contribution contracts. Those are excluded below.
|
| As a pure static scan (no application boot) this runs in the Arch suite even
| while the app container is unbootable.
|
*/

/**
 * Theme packages whose manifest does NOT follow the standard slug contract and
 * therefore keep (or omit) their own bespoke test instead of being covered here:
 *   - inertia-bookings-react / -vue: kind="plugin" Inertia component packs.
 *   - inertia-bookings: theme base without the standard extends/frontend shape.
 *
 * @var list<string>
 */
const CAPELL_THEME_CONTRACT_EXCLUSIONS = [
    'inertia-bookings',
    'inertia-bookings-react',
    'inertia-bookings-vue',
];

dataset('conforming_theme_packages', function (): array {
    $root = dirname(__DIR__, 2);
    $cases = [];

    foreach (glob($root . '/packages/theme-*/capell.json') ?: [] as $manifestPath) {
        $slug = substr(basename(dirname($manifestPath)), mb_strlen('theme-'));

        if (in_array($slug, CAPELL_THEME_CONTRACT_EXCLUSIONS, true)) {
            continue;
        }

        /** @var array<string, mixed> $manifest */
        $manifest = json_decode((string) file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);

        // Only standard themes are covered by the shared contract.
        if (($manifest['kind'] ?? null) !== 'theme') {
            continue;
        }

        $cases[$slug] = [$slug, dirname($manifestPath)];
    }

    return $cases;
});

it('theme package manifest follows the shared theme contract', function (string $slug, string $directory): void {
    /** @var array<string, mixed> $manifest */
    $manifest = json_decode((string) file_get_contents($directory . '/capell.json'), true, flags: JSON_THROW_ON_ERROR);

    expect($manifest['manifest-version'])->toBe(3)
        ->and($manifest['kind'])->toBe('theme')
        ->and($manifest['themeKey'])->toBe($slug)
        ->and($manifest['extends'] ?? null)->toBeString()->not->toBe('')
        ->and(data_get($manifest, 'commands.demo'))->toBe("capell:theme-{$slug}-demo")
        ->and($manifest['capabilities'])->toContain("theme-{$slug}", "theme-{$slug}-frontend")
        ->and(data_get($manifest, 'security.publicOutput.cacheSafe'))->toBeTrue();

    $screenshots = data_get($manifest, 'marketplace.screenshots', []);

    foreach (is_array($screenshots) ? $screenshots : [] as $screenshot) {
        $path = is_array($screenshot) ? ($screenshot['path'] ?? null) : null;

        expect(is_string($path))->toBeTrue("Theme {$slug} has a screenshot entry without a string path.")
            ->and(is_file($directory . '/' . $path))->toBeTrue("Theme {$slug} screenshot missing on disk: " . (is_string($path) ? $path : ''));
    }
})->with('conforming_theme_packages');
