<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

it('keeps every package manifest in an approved product group', function (): void {
    $allowedProductGroups = [
        'admin' => 'Capell Admin',
        'automation' => 'Capell Automation',
        'collaboration' => 'Capell Collaboration',
        'commerce' => 'Capell Commerce',
        'commercial' => 'Capell Commercial',
        'comments' => 'Capell Engagement',
        'communications' => 'Capell Communications',
        'content-product' => 'Capell Content',
        'form-builder' => 'Capell FormBuilder',
        'foundation' => 'Capell Foundation',
        'frontend' => 'Capell Frontend',
        'growth' => 'Capell Growth',
        'growth-product' => 'Capell Growth',
        'media' => 'Capell Media',
        'newsletter' => 'Capell Marketing',
        'operations' => 'Capell Operations',
        'publishing-pro' => 'Capell Publishing Pro',
        'search-seo' => 'Capell Search & SEO',
        'themes' => 'Capell Themes',
    ];

    $manifests = packageManifestPayloads();

    $invalid = [];

    foreach ($manifests as $path => $manifest) {
        $product = $manifest['product'] ?? [];
        $bundle = is_array($product) ? ($product['bundle'] ?? null) : null;

        if (! is_string($bundle) || ! isset($allowedProductGroups[$bundle])) {
            $invalid[$path] = 'Unknown bundle.';

            continue;
        }

        if (($product['group'] ?? null) !== $allowedProductGroups[$bundle]) {
            $invalid[$path] = 'Product group does not match bundle.';
        }

        if (! in_array($product['tier'] ?? null, ['core', 'free', 'premium'], true)) {
            $invalid[$path] = 'Tier must be core, free, or premium.';
        }
    }

    expect($invalid)->toBe(
        [],
        'Package manifests must use the approved Capell product groups: ' .
        json_encode($invalid, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    );
});

it('groups packages into the current product bundles', function (): void {
    $manifests = packageManifestPayloads();
    $packagesByBundle = [];

    foreach ($manifests as $path => $manifest) {
        $bundle = $manifest['product']['bundle'] ?? 'missing';
        $bundle = is_string($bundle) ? $bundle : 'missing';

        $packagesByBundle[$bundle][] = $path;
    }

    ksort($packagesByBundle);

    foreach (array_keys($packagesByBundle) as $bundle) {
        sort($packagesByBundle[$bundle]);
    }

    $themeBundlePackages = $packagesByBundle['themes'] ?? [];
    unset($packagesByBundle['themes']);

    $invalidThemeBundlePackages = [];

    foreach ($themeBundlePackages as $path) {
        $manifest = $manifests[$path] ?? null;
        $product = is_array($manifest) ? ($manifest['product'] ?? null) : null;
        $group = is_array($product) ? ($product['group'] ?? null) : null;

        if (! str_starts_with($path, 'theme-') || $group !== 'Capell Themes') {
            $invalidThemeBundlePackages[] = $path;
        }
    }

    expect($invalidThemeBundlePackages)->toBe(
        [],
        'The themes product bundle should only contain Capell Themes theme-* manifests.',
    );

    expect($packagesByBundle)->toBe([
        'admin' => [
            'translation-manager/capell.json',
        ],
        'automation' => [
            'automation-studio/capell.json',
            'public-actions/capell.json',
        ],
        'collaboration' => [
            'notes/capell.json',
        ],
        'comments' => [
            'comments/capell.json',
        ],
        'commerce' => [
            'payments/capell.json',
            'shopify-commerce/capell.json',
        ],
        'commercial' => [
            'ai-creator/capell.json',
            'ai-orchestrator/capell.json',
        ],
        'communications' => [
            'email-studio/capell.json',
        ],
        'content-product' => [
            'contacts/capell.json',
            'customer-portal/capell.json',
            'events/capell.json',
            'knowledge-base/capell.json',
        ],
        'form-builder' => [
            'form-builder/capell.json',
        ],
        'foundation' => [
            'address/capell.json',
            'block-library/capell.json',
            'content-sections/capell.json',
            'demo-kit/capell.json',
            'filament-peek/capell.json',
            'frontend-authoring/capell.json',
            'frontend-optimizer/capell.json',
            'hero/capell.json',
            'html-cache/capell.json',
            'layout-builder/capell.json',
            'media-library/capell.json',
            'navigation/capell.json',
            'record-switcher/capell.json',
            'structured-content-library/capell.json',
            'tags/capell.json',
            'theme-foundation/capell.json',
            'theme-liquid-glass/capell.json',
            'welcome-tour/capell.json',
        ],
        'frontend' => [
            'inertia-react-adapter/capell.json',
            'inertia-vue-adapter/capell.json',
            'inertia/capell.json',
        ],
        'growth' => [
            'campaign-studio/capell.json',
            'experiments/capell.json',
            'ga4-reports/capell.json',
            'insights/capell.json',
            'social-feeds/capell.json',
        ],
        'growth-product' => [
            'live-chat/capell.json',
        ],
        'media' => [
            'media-ai/capell.json',
        ],
        'newsletter' => [
            'newsletter/capell.json',
        ],
        'operations' => [
            'access-gate/capell.json',
            'agent-bridge/capell.json',
            'bookings/capell.json',
            'dashboard-reports/capell.json',
            'deployments/capell.json',
            'diagnostics/capell.json',
            'document-lifecycle/capell.json',
            'equestrian-clinics/capell.json',
            'exception-reports/capell.json',
            'login-audit/capell.json',
            'migration-assistant/capell.json',
            'password-policy/capell.json',
            'privacy-center/capell.json',
            'site-monitor/capell.json',
            'wordpress-importer/capell.json',
        ],
        'publishing-pro' => [
            'agent-delivery/capell.json',
            'api/capell.json',
            'blog/capell.json',
            'publishing-studio/capell.json',
        ],
        'search-seo' => [
            'search/capell.json',
            'seo-suite/capell.json',
            'site-discovery/capell.json',
            'url-manager/capell.json',
        ],
    ]);
});

it('removes business solutions package references', function (): void {
    expect(is_dir(packageRepositoryPath('packages/theme-business-solutions')))->toBeFalse()
        ->and(file_get_contents(packageRepositoryPath('composer.json')))->not->toContain('ThemeStudio\\\\BusinessSolutions')
        ->and(file_get_contents(packageRepositoryPath('composer.local.json')))->not->toContain('theme-business-solutions')
        ->and(file_get_contents(packageRepositoryPath('README.md')))->not->toContain('theme-business-solutions')
        ->and(file_get_contents(packageRepositoryPath('docs/README.md')))->not->toContain('Theme Business Solutions');
});

it('keeps theme marketplace screenshots backed by committed assets', function (): void {
    $missing = [];

    foreach (packageManifestPayloads() as $path => $manifest) {
        if (($manifest['kind'] ?? null) !== 'theme') {
            continue;
        }

        $screenshots = data_get($manifest, 'marketplace.screenshots', []);

        if (! is_array($screenshots) || $screenshots === []) {
            continue;
        }

        foreach ($screenshots as $index => $screenshot) {
            $assetPath = is_array($screenshot) ? ($screenshot['path'] ?? null) : null;

            if (! is_string($assetPath) || $assetPath === '') {
                $missing[$path][] = sprintf('marketplace.screenshots.%d.path is missing.', $index);

                continue;
            }

            if (preg_match('/\.(jpe?g|png|svg|webp)$/i', $assetPath) !== 1) {
                $missing[$path][] = sprintf('%s is not a supported image asset.', $assetPath);

                continue;
            }

            $absolutePath = packageRepositoryPath('packages/' . dirname($path) . '/' . $assetPath);

            if (! is_file($absolutePath)) {
                $missing[$path][] = sprintf('%s does not exist.', $assetPath);
            }
        }
    }

    expect($missing)->toBe(
        [],
        'Theme marketplace screenshot paths must resolve to committed package assets: ' .
        json_encode($missing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    );
});

/**
 * @return array<string, array<string, mixed>>
 */
function packageManifestPayloads(): array
{
    $finder = (new Finder)
        ->in(__DIR__ . '/../../../packages')
        ->name('capell.json')
        ->depth('< 4');

    $payloads = [];

    foreach ($finder as $manifest) {
        $payloads[$manifest->getRelativePathname()] = json_decode(
            $manifest->getContents(),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }

    ksort($payloads);

    return $payloads;
}

function packageRepositoryPath(string $path): string
{
    return __DIR__ . '/../../..' . DIRECTORY_SEPARATOR . $path;
}
