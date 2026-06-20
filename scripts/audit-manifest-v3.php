<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

$autoload = dirname(__DIR__) . '/vendor/autoload.php';

if (is_file($autoload)) {
    require_once $autoload;
}

const CAPELL_MANIFEST_V3_PROVIDER_BUCKETS = [
    'metadata',
    'install',
    'runtime',
    'admin',
    'frontend',
];

const CAPELL_MANIFEST_V3_OPTIONAL_PROVIDER_BUCKETS = [
    'auth',
];

const CAPELL_MANIFEST_V3_REQUIRED_ROOT_FIELDS = [
    'manifest-version',
    'name',
    'slug',
    'displayName',
    'kind',
    'capellApiVersion',
    'version',
    'description',
    'product',
    'namespace',
    'surfaces',
    'dependencies',
    'providers',
    'contributes',
    'database',
    'commands',
    'settings',
    'permissions',
    'capabilities',
    'performance',
    'healthChecks',
    'commercial',
    'marketplace',
];

const CAPELL_MANIFEST_V3_MIGRATION_GROUPS = [
    'foundation' => [
        'blog',
        'block-library',
        'structured-content-library',
        'content-sections',
        'demo-kit',
        'filament-peek',
        'foundation-theme',
        'frontend-authoring',
        'frontend-optimizer',
        'hero',
        'html-cache',
        'layout-builder',
        'media-library',
        'navigation',
        'site-discovery',
        'tags',
        'welcome-tour',
    ],
    'comments' => [
        'comments',
    ],
    'operations' => [
        'access-gate',
        'agent-delivery',
        'bookings',
        'dashboard-reports',
        'deployments',
        'diagnostics',
        'document-lifecycle',
        'equestrian-clinics',
        'ga4-reports',
        'insights',
        'login-audit',
        'migration-assistant',
        'notes',
        'password-policy',
        'privacy-center',
        'publishing-studio',
        'record-switcher',
        'site-monitor',
        'translation-manager',
    ],
    'publishing-pro' => [
        'api',
    ],
    'content-product' => [
        'address',
        'agent-bridge',
        'ai-orchestrator',
        'automation-studio',
        'campaign-studio',
        'contacts',
        'customer-portal',
        'email-studio',
        'events',
        'exception-reports',
        'experiments',
        'form-builder',
        'knowledge-base',
        'inertia',
        'inertia-react-adapter',
        'inertia-vue-adapter',
        'live-chat',
        'media-ai',
        'newsletter',
        'payments',
        'public-actions',
        'search',
        'seo-suite',
        'shopify-commerce',
        'social-feeds',
        'url-manager',
        'wordpress-importer',
    ],
    'themes' => [
        'theme-agency',
        'theme-commerce',
        'theme-corporate',
        'theme-education',
        'theme-estate-agents',
        'theme-healthcare',
        'theme-inertia-bookings',
        'theme-inertia-bookings-react',
        'theme-inertia-bookings-vue',
        'theme-knowledge',
        'theme-liquid-glass',
        'theme-local-services',
        'theme-nonprofit',
        'theme-portfolio',
        'theme-restaurant',
        'theme-saas',
    ],
];

const CAPELL_MANIFEST_V3_CONTRIBUTION_PATTERNS = [
    'admin-page' => 'registerExtensionPage',
    'dashboard-widget' => 'registerDashboardFilamentWidget',
    'overview-stat' => 'registerOverviewStat',
    'admin-resource' => 'AdminSurfaceContributionData::resource',
    'configurator' => 'AdminSurfaceContributionData::configurator',
    'schema-extender' => 'SchemaExtenderEnum::',
    'model' => 'CapellCore::registerModels',
    'section' => 'CapellCore::registerSection',
    'page-type' => 'CapellCore::registerPageType',
    'permission' => 'CapellCore::registerPermission',
    'route' => 'Route::',
    'setting' => 'CapellCore::registerSettings',
    'page-variation' => 'CapellCore::registerPageVariation',
    'asset' => 'CapellCore::registerAsset',
    'migration' => 'loadMigrationsFrom',
    'scheduled-job' => 'Schedule::',
    'agent-capability' => 'CapellAgentBridgeCapabilityRegistry',
];

const CAPELL_MANIFEST_V3_KNOWN_CONTRIBUTION_TYPES = [
    'admin-page',
    'dashboard-widget',
    'overview-stat',
    'admin-resource',
    'configurator',
    'schema-extender',
    'model',
    'section',
    'page-type',
    'permission',
    'route',
    'setting',
    'page-variation',
    'asset',
    'migration',
    'scheduled-job',
    'frontend-component',
    'settings',
    'agent-delivery-contract',
    'dashboard-block',
    'agent-capability',
    'public-api-endpoint',
    'ai-discovery-output',
    'calendar-export',
    'content-section-adapter',
    'deduplication-rules',
    'form-field',
    'frontend-route',
    'notification',
    'privacy-tools',
    'public-form',
    'queue-job',
    'search-index',
    'source-package-sync',
    'theme-adapter',
    'theme-integration',
    'webhook',
    'paid-access-checkout-creation',
];

/**
 * @return array{
 *     packages: array<string, array<string, mixed>>,
 *     errors: array<string, list<string>>,
 *     unassignedPackages: list<string>,
 *     duplicateAssignments: array<string, list<string>>
 * }
 */
function capell_manifest_v3_audit(string $root): array
{
    $packagesPath = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'packages';
    $assignments = capell_manifest_v3_package_assignments();
    $errors = [];
    $packages = [];

    foreach (capell_manifest_v3_package_directories($packagesPath) as $slug => $packagePath) {
        $manifestPath = $packagePath . DIRECTORY_SEPARATOR . 'capell.json';
        $manifest = is_file($manifestPath)
            ? json_decode((string) file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR)
            : [];

        $discoveredTypes = capell_manifest_v3_discovered_contribution_types($packagePath);
        $declaredTypes = capell_manifest_v3_declared_contribution_types($manifest);
        $deferredTypes = capell_manifest_v3_deferred_contribution_types($manifest);
        $missingTypes = array_values(array_diff($discoveredTypes, [...$declaredTypes, ...$deferredTypes]));
        sort($missingTypes);

        $packageErrors = [
            ...capell_manifest_v3_missing_field_errors($manifest),
            ...capell_manifest_v3_provider_bucket_errors($manifest),
        ];

        if (! array_key_exists($slug, $assignments)) {
            $packageErrors[] = 'package is not assigned to a migration group';
        }

        foreach ($missingTypes as $type) {
            $packageErrors[] = "discovered {$type} contribution is not declared or deferred";
        }

        if ($packageErrors !== []) {
            $errors[$slug] = $packageErrors;
        }

        $packages[$slug] = [
            'name' => $manifest['name'] ?? null,
            'manifestVersion' => $manifest['manifest-version'] ?? null,
            'migrationGroup' => $assignments[$slug][0] ?? null,
            'manifestPath' => is_file($manifestPath) ? $manifestPath : null,
            'missingFields' => capell_manifest_v3_missing_fields($manifest),
            'discoveredContributionTypes' => $discoveredTypes,
            'declaredContributionTypes' => $declaredTypes,
            'deferredContributionTypes' => $deferredTypes,
            'missingContributionTypes' => $missingTypes,
        ];
    }

    ksort($packages);
    ksort($errors);

    return [
        'packages' => $packages,
        'errors' => $errors,
        'unassignedPackages' => capell_manifest_v3_unassigned_packages($packagesPath),
        'duplicateAssignments' => capell_manifest_v3_duplicate_assignments(),
    ];
}

/**
 * @return array<string, string>
 */
function capell_manifest_v3_product_groups(): array
{
    $groups = [];

    foreach (CAPELL_MANIFEST_V3_MIGRATION_GROUPS as $group => $packages) {
        foreach ($packages as $package) {
            $groups[$package] = $group;
        }
    }

    ksort($groups);

    return $groups;
}

/**
 * @return array<string, list<string>>
 */
function capell_manifest_v3_package_assignments(): array
{
    $assignments = [];

    foreach (CAPELL_MANIFEST_V3_MIGRATION_GROUPS as $group => $packages) {
        foreach ($packages as $package) {
            $assignments[$package][] = $group;
        }
    }

    ksort($assignments);

    return $assignments;
}

/**
 * @return array<string, string>
 */
function capell_manifest_v3_package_directories(string $packagesPath): array
{
    $directories = [];
    $rootPath = dirname($packagesPath);
    $trackedComposerPaths = capell_manifest_v3_git_tracked_files($rootPath, 'packages/*/composer.json');

    if ($trackedComposerPaths !== []) {
        foreach ($trackedComposerPaths as $trackedComposerPath) {
            $pathParts = explode('/', $trackedComposerPath);

            if (($pathParts[0] ?? null) !== 'packages' || ! isset($pathParts[1])) {
                continue;
            }

            $packagePath = $packagesPath . DIRECTORY_SEPARATOR . $pathParts[1];

            if (is_dir($packagePath)) {
                $directories[$pathParts[1]] = $packagePath;
            }
        }

        ksort($directories);

        return $directories;
    }

    $packagePaths = glob($packagesPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);

    foreach ($packagePaths !== false ? $packagePaths : [] as $packagePath) {
        if (! is_file($packagePath . DIRECTORY_SEPARATOR . 'composer.json')) {
            continue;
        }

        $directories[basename($packagePath)] = $packagePath;
    }

    ksort($directories);

    return $directories;
}

/**
 * @return array<string, array<string, mixed>>
 */
function capell_manifest_v3_manifest_payloads(string $root): array
{
    $trackedManifestPaths = capell_manifest_v3_git_tracked_files($root, 'packages/*/capell.json');
    $payloads = [];

    if ($trackedManifestPaths !== []) {
        foreach ($trackedManifestPaths as $trackedManifestPath) {
            $manifestPath = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $trackedManifestPath;

            if (! is_file($manifestPath)) {
                continue;
            }

            $payloads[$trackedManifestPath] = json_decode(
                (string) file_get_contents($manifestPath),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        }

        ksort($payloads);

        return $payloads;
    }

    $finder = (new Finder)
        ->in(rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'packages')
        ->name('capell.json')
        ->depth('< 4');

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

/**
 * @return list<string>
 */
function capell_manifest_v3_git_tracked_files(string $rootPath, string $pathspec): array
{
    $command = sprintf(
        'git -C %s ls-files -- %s 2>/dev/null',
        escapeshellarg($rootPath),
        escapeshellarg($pathspec),
    );
    $output = [];
    $exitCode = 0;

    exec($command, $output, $exitCode);

    if ($exitCode !== 0) {
        return [];
    }

    $files = array_values(array_filter(
        $output,
        static fn (string $trackedPath): bool => $trackedPath !== '',
    ));
    sort($files);

    return $files;
}

/**
 * @return list<string>
 */
function capell_manifest_v3_unassigned_packages(string $packagesPath): array
{
    $assignments = capell_manifest_v3_package_assignments();
    $unassigned = array_values(array_diff(array_keys(capell_manifest_v3_package_directories($packagesPath)), array_keys($assignments)));
    sort($unassigned);

    return $unassigned;
}

/**
 * @return array<string, list<string>>
 */
function capell_manifest_v3_duplicate_assignments(): array
{
    return array_filter(
        capell_manifest_v3_package_assignments(),
        static fn (array $groups): bool => count($groups) !== 1,
    );
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<string>
 */
function capell_manifest_v3_missing_fields(array $manifest): array
{
    return array_values(array_filter(
        CAPELL_MANIFEST_V3_REQUIRED_ROOT_FIELDS,
        static fn (string $field): bool => ! array_key_exists($field, $manifest),
    ));
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<string>
 */
function capell_manifest_v3_missing_field_errors(array $manifest): array
{
    return array_map(
        static fn (string $field): string => "missing {$field}",
        capell_manifest_v3_missing_fields($manifest),
    );
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<string>
 */
function capell_manifest_v3_provider_bucket_errors(array $manifest): array
{
    if (! is_array($manifest['providers'] ?? null)) {
        return ['missing providers'];
    }

    $actual = array_keys($manifest['providers']);
    sort($actual);
    $expected = [
        ...CAPELL_MANIFEST_V3_PROVIDER_BUCKETS,
        ...array_intersect(CAPELL_MANIFEST_V3_OPTIONAL_PROVIDER_BUCKETS, $actual),
    ];
    sort($expected);

    return $actual === $expected
        ? []
        : ['providers must declare metadata, install, runtime, admin, and frontend buckets'];
}

/**
 * @return list<string>
 */
function capell_manifest_v3_discovered_contribution_types(string $packagePath): array
{
    $types = [];
    $scanPaths = array_values(array_filter([
        $packagePath . DIRECTORY_SEPARATOR . 'src',
        $packagePath . DIRECTORY_SEPARATOR . 'routes',
        $packagePath . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations',
        $packagePath . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'settings',
    ], static fn (string $path): bool => is_dir($path)));

    if ($scanPaths === []) {
        return [];
    }

    $finder = (new Finder)
        ->files()
        ->in($scanPaths)
        ->name('*.php')
        ->exclude(['vendor']);

    foreach ($finder as $file) {
        $contents = $file->getContents();

        foreach (CAPELL_MANIFEST_V3_CONTRIBUTION_PATTERNS as $type => $pattern) {
            if ($type === 'route' && ! capell_manifest_v3_registers_routes($contents)) {
                continue;
            }

            if (str_contains($contents, $pattern)) {
                $types[$type] = true;
            }
        }
    }

    $types = array_keys($types);
    sort($types);

    return $types;
}

function capell_manifest_v3_registers_routes(string $contents): bool
{
    return preg_match('/\bRoute::(?:any|delete|get|group|match|middleware|name|patch|permanentRedirect|post|prefix|put|redirect|view)\s*\(/', $contents) === 1;
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<string>
 */
function capell_manifest_v3_declared_contribution_types(array $manifest): array
{
    $types = [];

    if (($manifest['database']['migrations'] ?? false) === true) {
        $types['migration'] = true;
    }

    if (is_array($manifest['settings'] ?? null) && $manifest['settings'] !== []) {
        $types['setting'] = true;
    }

    if (is_array($manifest['security']['publicSurface']['routeNames'] ?? null) && $manifest['security']['publicSurface']['routeNames'] !== []) {
        $types['route'] = true;
    }

    if (is_array($manifest['healthChecks'] ?? null) && $manifest['healthChecks'] !== []) {
        $types['health-check'] = true;
    }

    if (is_array($manifest['contributes'] ?? null)) {
        foreach ($manifest['contributes'] as $contribution) {
            if (is_array($contribution) && is_string($contribution['type'] ?? null)) {
                $types[$contribution['type']] = true;
            }
        }
    }

    $types = array_keys($types);
    sort($types);

    return $types;
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<string>
 */
function capell_manifest_v3_deferred_contribution_types(array $manifest): array
{
    $runtime = $manifest['contributionTraceability'] ?? $manifest['runtime'] ?? [];

    if (! is_array($runtime) || ! is_array($runtime['deferredContributions'] ?? null)) {
        return [];
    }

    $types = array_values(array_filter(
        $runtime['deferredContributions'],
        static fn (mixed $type): bool => is_string($type) && $type !== '',
    ));
    sort($types);

    return $types;
}

if (realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    $report = capell_manifest_v3_audit(dirname(__DIR__));

    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

    $exitCode = $report['errors'] === [] && $report['unassignedPackages'] === [] && $report['duplicateAssignments'] === [] ? 0 : 1;

    exit /* status */ ($exitCode);
}
