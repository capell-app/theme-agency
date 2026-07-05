<?php

declare(strict_types=1);

namespace Capell\Tests\Arch;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/*
|--------------------------------------------------------------------------
| Feature ↔ feature dependency boundary
|--------------------------------------------------------------------------
|
| A feature package may only import the `Capell\*` namespace of ANOTHER
| feature package if it declares that package in its own composer.json
| "require". The monorepo packages (Capell\Core / Admin / Frontend / Installer
| / Marketplace) live in capell-4 — they are not feature packages here and are
| always allowed. A package's own namespace(s) are always allowed.
|
| This is the Pest-arch equivalent of a manifest-vs-imports dependency guard
| (no deptrac): it reuses the composer-aware static-scan style of the app
| boundary tests. It prevents NEW undeclared cross-package coupling.
|
| The capellPackageDependencyBaseline() below records the ~100 pre-existing
| undeclared couplings that exist today (dominated by theme-* packages
| importing Capell\FoundationTheme). They are a documented burn-down backlog:
| as each offender adds the dependency to its composer.json "require" (and the
| monorepo is re-locked), delete its entry here. New violations that are not in
| the baseline fail this test.
|
*/

/**
 * Map of `Capell\Namespace` (trimmed) => composer package name, built from every
 * package's autoload psr-4. Only production autoload namespaces define "known
 * feature packages".
 *
 * @return array<string, string>
 */
function capellPackageNamespaceToName(): array
{
    static $map = null;

    if ($map !== null) {
        return $map;
    }

    $map = [];

    foreach (glob(capellPackagesRoot() . '/packages/*/composer.json') ?: [] as $composerPath) {
        $composer = capellReadJson($composerPath);
        $name = $composer['name'] ?? null;

        if (! is_string($name)) {
            continue;
        }

        $autoload = is_array($composer['autoload'] ?? null) ? $composer['autoload'] : [];
        $psr4 = is_array($autoload['psr-4'] ?? null) ? $autoload['psr-4'] : [];
        foreach (array_keys($psr4) as $namespace) {
            $map[rtrim((string) $namespace, '\\')] = $name;
        }
    }

    return $map;
}

function capellPackagesRoot(): string
{
    return dirname(__DIR__, 2);
}

/**
 * @return array<string, mixed>
 */
function capellReadJson(string $path): array
{
    /** @var array<string, mixed> $decoded */
    $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

    return $decoded;
}

/**
 * Longest known feature-package namespace prefix that $import belongs to, or null.
 *
 * @param  list<string>  $prefixesLongestFirst
 */
function capellMatchNamespacePrefix(string $import, array $prefixesLongestFirst): ?string
{
    foreach ($prefixesLongestFirst as $prefix) {
        if ($import === $prefix || str_starts_with($import, $prefix . '\\')) {
            return $prefix;
        }
    }

    return null;
}

/**
 * Pre-existing undeclared cross-package couplings. Burn down — do not grow.
 *
 * @return array<string, list<string>>
 */
function capellPackageDependencyBaseline(): array
{
    return [
        'access-gate' => ['capell-app/customer-portal', 'capell-app/payments', 'capell-app/public-actions'],
        'agent-bridge' => ['capell-app/navigation'],
        'agent-delivery' => ['capell-app/site-discovery'],
        'automation-studio' => ['capell-app/agent-bridge', 'capell-app/contacts', 'capell-app/email-studio', 'capell-app/newsletter', 'capell-app/public-actions'],
        'blog' => ['capell-app/theme-foundation', 'capell-app/insights', 'capell-app/publishing-studio', 'capell-app/site-discovery'],
        'campaign-studio' => ['capell-app/experiments', 'capell-app/publishing-studio', 'capell-app/site-discovery'],
        'comments' => ['capell-app/blog', 'capell-app/email-studio'],
        'contacts' => ['capell-app/access-gate', 'capell-app/campaign-studio', 'capell-app/comments', 'capell-app/events', 'capell-app/shopify-commerce'],
        'content-sections' => ['capell-app/public-actions', 'capell-app/publishing-studio'],
        'demo-kit' => ['capell-app/html-cache', 'capell-app/layout-builder', 'capell-app/navigation'],
        'diagnostics' => ['capell-app/block-library'],
        'document-lifecycle' => ['capell-app/customer-portal'],
        'events' => ['capell-app/customer-portal', 'capell-app/seo-suite', 'capell-app/site-discovery'],
        'experiments' => ['capell-app/insights'],
        'filament-peek' => ['capell-app/layout-builder', 'capell-app/publishing-studio'],
        'theme-foundation' => ['capell-app/navigation'],
        'frontend-authoring' => ['capell-app/publishing-studio'],
        'html-cache' => ['capell-app/site-discovery'],
        'insights' => ['capell-app/privacy-center'],
        'knowledge-base' => ['capell-app/search', 'capell-app/site-discovery'],
        'layout-builder' => ['capell-app/content-sections', 'capell-app/filament-peek', 'capell-app/frontend-authoring', 'capell-app/html-cache', 'capell-app/navigation', 'capell-app/publishing-studio'],
        'live-chat' => ['capell-app/agent-bridge', 'capell-app/ai-orchestrator', 'capell-app/knowledge-base'],
        'media-ai' => ['capell-app/ai-orchestrator'],
        'newsletter' => ['capell-app/contacts', 'capell-app/customer-portal', 'capell-app/publishing-studio'],
        'payments' => ['capell-app/customer-portal'],
        'search' => ['capell-app/site-discovery'],
        'seo-suite' => ['capell-app/publishing-studio'],
        'site-monitor' => ['capell-app/site-discovery'],
        'theme-art-paper' => ['capell-app/theme-foundation'],
        'theme-deep-bench' => ['capell-app/theme-foundation'],
        'theme-far-field' => ['capell-app/theme-foundation'],
        'theme-field-guide' => ['capell-app/theme-foundation'],
        'theme-first-light' => ['capell-app/theme-foundation'],
        'theme-front-row' => ['capell-app/theme-foundation'],
        'theme-ink-press' => ['capell-app/theme-foundation'],
        'theme-launch-pad' => ['capell-app/theme-foundation'],
        'theme-liquid-glass' => ['capell-app/theme-foundation'],
        'theme-night-shift' => ['capell-app/theme-foundation'],
        'theme-off-grid' => ['capell-app/theme-foundation'],
        'theme-one-take' => ['capell-app/theme-foundation'],
        'theme-open-studio' => ['capell-app/theme-foundation'],
        'theme-quiet-type' => ['capell-app/theme-foundation'],
        'theme-soft-focus' => ['capell-app/theme-foundation'],
        'theme-reel-room' => ['capell-app/theme-foundation'],
        'theme-gold-rush' => ['capell-app/theme-foundation'],
        'theme-wild-card' => ['capell-app/theme-foundation'],
        'translation-manager' => ['capell-app/ai-orchestrator'],
        'url-manager' => ['capell-app/seo-suite'],
        'wordpress-importer' => ['capell-app/url-manager'],
    ];
}

it('feature packages only import Capell namespaces declared in their composer require', function (): void {
    $namespaceToName = capellPackageNamespaceToName();
    $prefixesLongestFirst = array_keys($namespaceToName);
    usort($prefixesLongestFirst, fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));

    /** @var array<string, list<string>> $nameToNamespaces */
    $nameToNamespaces = [];
    foreach ($namespaceToName as $namespace => $name) {
        $nameToNamespaces[$name][] = $namespace;
    }

    $baseline = capellPackageDependencyBaseline();
    $newViolations = [];

    foreach (glob(capellPackagesRoot() . '/packages/*/composer.json') ?: [] as $composerPath) {
        $packageDirectory = basename(dirname($composerPath));
        $composer = capellReadJson($composerPath);

        $autoload = is_array($composer['autoload'] ?? null) ? $composer['autoload'] : [];
        $autoloadDev = is_array($composer['autoload-dev'] ?? null) ? $composer['autoload-dev'] : [];
        $psr4Autoload = is_array($autoload['psr-4'] ?? null) ? $autoload['psr-4'] : [];
        $psr4AutoloadDev = is_array($autoloadDev['psr-4'] ?? null) ? $autoloadDev['psr-4'] : [];
        $ownNamespaces = [];
        foreach ([$psr4Autoload, $psr4AutoloadDev] as $psr4) {
            foreach (array_keys($psr4) as $namespace) {
                $ownNamespaces[] = rtrim((string) $namespace, '\\');
            }
        }

        $allowedNamespaces = $ownNamespaces;
        $require = is_array($composer['require'] ?? null) ? $composer['require'] : [];
        foreach (array_keys($require) as $requirement) {
            foreach ($nameToNamespaces[$requirement] ?? [] as $namespace) {
                $allowedNamespaces[] = $namespace;
            }
        }

        $sourcePath = dirname($composerPath) . '/src';
        if (! is_dir($sourcePath)) {
            continue;
        }

        $offendingPackageNames = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourcePath, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo) {
                continue;
            }

            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = (string) file_get_contents($file->getPathname());

            if (preg_match_all('/use\s+(Capell\\\\[A-Za-z0-9_\\\\]+)/', $contents, $matches) === false) {
                continue;
            }

            foreach ($matches[1] as $import) {
                $prefix = capellMatchNamespacePrefix($import, $prefixesLongestFirst);

                if ($prefix === null || in_array($prefix, $allowedNamespaces, true)) {
                    continue;
                }

                $offendingPackageNames[$namespaceToName[$prefix]] = true;
            }
        }

        $offenders = array_keys($offendingPackageNames);
        sort($offenders);

        $baselinedForPackage = $baseline[$packageDirectory] ?? [];
        $undocumented = array_values(array_diff($offenders, $baselinedForPackage));

        if ($undocumented !== []) {
            $newViolations[$packageDirectory] = $undocumented;
        }
    }

    expect($newViolations)->toBe(
        [],
        'These packages import Capell namespaces they do not declare in composer.json "require". '
        . 'Either add the dependency to "require", relocate the shared code to a package both already '
        . 'depend on, or (only for a reviewed, deliberate exception) add it to '
        . "capellPackageDependencyBaseline().\n"
        . capellFormatDependencyViolations($newViolations),
    );
});

/**
 * @param  array<string, list<string>>  $violations
 */
function capellFormatDependencyViolations(array $violations): string
{
    $lines = [];

    foreach ($violations as $packageDirectory => $names) {
        $lines[] = sprintf('  %s → %s', $packageDirectory, implode(', ', $names));
    }

    return implode("\n", $lines);
}
