<?php

declare(strict_types=1);

namespace Capell\Tests\Arch;

use Capell\AgentBridge\Providers\AgentBridgeServiceProvider;
use Capell\FrontendAuthoring\Providers\FrontendAuthoringServiceProvider;
use Capell\MediaLibrary\MediaLibraryServiceProvider;
use Capell\Navigation\Providers\NavigationServiceProvider;
use Capell\PublishingStudio\Providers\ConsoleServiceProvider;
use Capell\PublishingStudio\Providers\PublishingStudioServiceProvider;

/*
|--------------------------------------------------------------------------
| Feature package structural invariants
|--------------------------------------------------------------------------
|
| Every feature package declares its service provider(s) in composer.json
| ("extra.laravel.providers"). This test holds those providers to two
| structural rules so new packages can't drift away from the shared shape:
|
|   1. The provider extends Capell\Core\Support\Packages\AbstractPackageServiceProvider
|      (the canonical package base in capell-4), not a bare Illuminate provider.
|   2. The provider class is final (or abstract) — package providers are not an
|      extension point.
|
| Rule 2 (final) is fully enforced — every non-theme provider was marked final.
| Rule 1 (extends base) keeps a documented burn-down baseline of the providers
| that predate it; as each is migrated, delete its entry. The themes
| (Capell\ThemeStudio\*) are a distinct provider category with their own
| registration shape and are excluded wholesale rather than line-by-line.
|
| The `routes/` + `config/` directory-presence checks from the original plan are
| deliberately NOT enforced here: 70+ theme packages legitimately ship neither,
| so a presence guard would baseline ~75% of the tree and hide real drift instead
| of catching it. Provider shape is the high-signal invariant; routes/config stay
| advisory (the make:capell-package generator scaffolds them for new packages).
|
*/

/**
 * Service providers that do not yet extend AbstractPackageServiceProvider.
 * Burn down — do not grow. (Themes are excluded by namespace, see below.)
 *
 * @return list<string>
 */
function capellStructProvidersNotExtendingBase(): array
{
    return [
        AgentBridgeServiceProvider::class,
        FrontendAuthoringServiceProvider::class,
        MediaLibraryServiceProvider::class,
        NavigationServiceProvider::class,
        ConsoleServiceProvider::class,
        PublishingStudioServiceProvider::class,
    ];
}

/**
 * Service providers that are not yet final. Burn down — do not grow.
 * (Themes are excluded by namespace, see below.)
 *
 * Fully burnt down: all 33 non-theme feature-package providers were marked final
 * once a static scan confirmed none are extended or mocked anywhere in the three
 * repos. New non-final providers (outside the theme namespace) now fail the test.
 *
 * @return list<string>
 */
function capellStructProvidersNotFinal(): array
{
    return [];
}

/**
 * Themes are a distinct provider category (their own theme-registration shape).
 * Any provider under this namespace is excluded from both invariants.
 */
const CAPELL_STRUCT_THEME_NAMESPACE = 'Capell\\ThemeStudio\\';

function capellStructPackagesRoot(): string
{
    return dirname(__DIR__, 2);
}

/**
 * Declared providers across every feature package, mapped to their source file.
 *
 * @return array<string, string> FQCN => absolute file path (only resolvable ones)
 */
function capellStructProviderFiles(): array
{
    $providers = [];

    foreach (glob(capellStructPackagesRoot() . '/packages/*/composer.json') ?: [] as $composerPath) {
        /** @var array<string, mixed> $composer */
        $composer = json_decode((string) file_get_contents($composerPath), true, flags: JSON_THROW_ON_ERROR);
        $directory = dirname($composerPath);

        /** @var list<string> $declared */
        $declared = $composer['extra']['laravel']['providers'] ?? [];
        /** @var array<string, string> $psr4 */
        $psr4 = $composer['autoload']['psr-4'] ?? [];

        foreach ($declared as $fqcn) {
            foreach ($psr4 as $namespace => $path) {
                $namespace = rtrim((string) $namespace, '\\');

                if (! str_starts_with($fqcn, $namespace . '\\')) {
                    continue;
                }

                $relative = str_replace('\\', '/', substr($fqcn, mb_strlen($namespace) + 1)) . '.php';
                $candidate = $directory . '/' . rtrim((string) $path, '/') . '/' . $relative;

                if (is_file($candidate)) {
                    $providers[$fqcn] = $candidate;

                    break;
                }
            }
        }
    }

    return $providers;
}

it('feature package service providers extend AbstractPackageServiceProvider', function (): void {
    $baseline = capellStructProvidersNotExtendingBase();
    $violations = [];

    foreach (capellStructProviderFiles() as $fqcn => $file) {
        if (str_starts_with($fqcn, CAPELL_STRUCT_THEME_NAMESPACE) || in_array($fqcn, $baseline, true)) {
            continue;
        }

        $source = (string) file_get_contents($file);

        if (! str_contains($source, 'extends AbstractPackageServiceProvider')
            && ! str_contains($source, 'extends \\Capell\\Core\\Support\\Packages\\AbstractPackageServiceProvider')) {
            $violations[] = $fqcn;
        }
    }

    sort($violations);

    expect($violations)->toBe(
        [],
        'These service providers do not extend Capell\Core\Support\Packages\AbstractPackageServiceProvider. '
        . 'Extend the shared base, or (for a reviewed exception) add the FQCN to '
        . "capellStructProvidersNotExtendingBase().\n  " . implode("\n  ", $violations),
    );
});

it('feature package service providers are final', function (): void {
    $baseline = capellStructProvidersNotFinal();
    $violations = [];

    foreach (capellStructProviderFiles() as $fqcn => $file) {
        if (str_starts_with($fqcn, CAPELL_STRUCT_THEME_NAMESPACE) || in_array($fqcn, $baseline, true)) {
            continue;
        }

        $source = (string) file_get_contents($file);

        if (preg_match('/\b(final|abstract)\s+class\s+\w+/', $source) !== 1) {
            $violations[] = $fqcn;
        }
    }

    sort($violations);

    expect($violations)->toBe(
        [],
        'These service providers are not final. Mark them final (package providers are not an '
        . "extension point), or add the FQCN to capellStructProvidersNotFinal().\n  " . implode("\n  ", $violations),
    );
});
