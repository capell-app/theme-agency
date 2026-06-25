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
        'blog' => ['capell-app/foundation-theme', 'capell-app/insights', 'capell-app/publishing-studio', 'capell-app/site-discovery'],
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
        'foundation-theme' => ['capell-app/navigation'],
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
        'theme-aeo-analytics' => ['capell-app/foundation-theme'],
        'theme-agency' => ['capell-app/foundation-theme'],
        'theme-ai-agent' => ['capell-app/foundation-theme'],
        'theme-ai-lab' => ['capell-app/foundation-theme'],
        'theme-api-platform' => ['capell-app/foundation-theme'],
        'theme-automotive-dealer' => ['capell-app/foundation-theme'],
        'theme-beauty-spa' => ['capell-app/foundation-theme'],
        'theme-bold-sport-commerce' => ['capell-app/foundation-theme'],
        'theme-case-study-platform' => ['capell-app/foundation-theme'],
        'theme-character-portfolio-index' => ['capell-app/foundation-theme'],
        'theme-commerce' => ['capell-app/foundation-theme'],
        'theme-conference-event' => ['capell-app/foundation-theme'],
        'theme-construction-trades' => ['capell-app/foundation-theme'],
        'theme-corporate' => ['capell-app/foundation-theme'],
        'theme-creative-culture-editorial' => ['capell-app/foundation-theme'],
        'theme-creative-marketplace' => ['capell-app/foundation-theme'],
        'theme-creator-newsletter' => ['capell-app/foundation-theme'],
        'theme-crypto-defi' => ['capell-app/foundation-theme'],
        'theme-dark-product-system' => ['capell-app/foundation-theme'],
        'theme-dense-news-analysis' => ['capell-app/foundation-theme'],
        'theme-design-led-magazine' => ['capell-app/foundation-theme'],
        'theme-design-studio' => ['capell-app/foundation-theme'],
        'theme-developer-infrastructure' => ['capell-app/foundation-theme'],
        'theme-devtool-oss' => ['capell-app/foundation-theme'],
        'theme-dog-walkers' => ['capell-app/foundation-theme'],
        'theme-editorial-crm' => ['capell-app/foundation-theme'],
        'theme-editorial-serif' => ['capell-app/foundation-theme'],
        'theme-education' => ['capell-app/foundation-theme'],
        'theme-experimental-directory' => ['capell-app/foundation-theme'],
        'theme-filter-gallery' => ['capell-app/foundation-theme'],
        'theme-financial-advisory' => ['capell-app/foundation-theme'],
        'theme-fintech-trust' => ['capell-app/foundation-theme'],
        'theme-fitness-wellness' => ['capell-app/foundation-theme'],
        'theme-global-culture-magazine' => ['capell-app/foundation-theme'],
        'theme-healthcare' => ['capell-app/foundation-theme'],
        'theme-interactive-builder' => ['capell-app/foundation-theme'],
        'theme-knowledge' => ['capell-app/foundation-theme'],
        'theme-landing-gallery' => ['capell-app/foundation-theme'],
        'theme-law-firm' => ['capell-app/foundation-theme'],
        'theme-local-services' => ['capell-app/foundation-theme'],
        'theme-manufacturing' => ['capell-app/foundation-theme'],
        'theme-minimal-curation-feed' => ['capell-app/foundation-theme'],
        'theme-minimal-fashion' => ['capell-app/foundation-theme'],
        'theme-motion-archive' => ['capell-app/foundation-theme'],
        'theme-newsroom-magazine' => ['capell-app/foundation-theme'],
        'theme-nonprofit' => ['capell-app/foundation-theme'],
        'theme-one-page-showcase' => ['capell-app/foundation-theme'],
        'theme-outdoor-mission' => ['capell-app/foundation-theme'],
        'theme-packaging-supplier' => ['capell-app/foundation-theme'],
        'theme-personal-dev' => ['capell-app/foundation-theme'],
        'theme-podcast-show' => ['capell-app/foundation-theme'],
        'theme-portfolio' => ['capell-app/foundation-theme'],
        'theme-portfolio-directory' => ['capell-app/foundation-theme'],
        'theme-premium-infrastructure' => ['capell-app/foundation-theme'],
        'theme-premium-portfolio-collection' => ['capell-app/foundation-theme'],
        'theme-premium-product-story' => ['capell-app/foundation-theme'],
        'theme-product-company-editorial' => ['capell-app/foundation-theme'],
        'theme-product-studio' => ['capell-app/foundation-theme'],
        'theme-property-developer' => ['capell-app/foundation-theme'],
        'theme-quant-trading' => ['capell-app/foundation-theme'],
        'theme-quiet-luxury-retail' => ['capell-app/foundation-theme'],
        'theme-quiet-web-gallery' => ['capell-app/foundation-theme'],
        'theme-raw-index' => ['capell-app/foundation-theme'],
        'theme-recruitment-jobs' => ['capell-app/foundation-theme'],
        'theme-resource-hub' => ['capell-app/foundation-theme'],
        'theme-robotics-hardware' => ['capell-app/foundation-theme'],
        'theme-saas' => ['capell-app/foundation-theme'],
        'theme-scoreboard-showcase' => ['capell-app/foundation-theme'],
        'theme-travel-tourism' => ['capell-app/foundation-theme'],
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
