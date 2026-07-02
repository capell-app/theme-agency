<?php

declare(strict_types=1);

$rootPath = dirname(__DIR__);
$packagesPath = $rootPath . '/packages';
$checkOnly = in_array('--check', $argv, true);
$changedFiles = [];

foreach (capell_docs_package_paths($packagesPath) as $packageSlug => $packagePath) {
    $manifest = capell_docs_read_json($packagePath . '/capell.json');
    $composer = capell_docs_read_json($packagePath . '/composer.json');

    // The README is always the generated developer document (technical shape, data model,
    // install impact). A package opts into an admin-first overview by adding a hand-authored
    // docs/overview.admin.md fragment; the generator then wraps that fragment into overview.md.
    // Packages without the fragment keep the generated developer-shaped overview, so the
    // rollout across packages is incremental and non-destructive.
    $overview = is_file($packagePath . '/docs/overview.admin.md')
        ? capell_docs_admin_overview_markdown($packageSlug, $packagePath, $manifest)
        : capell_docs_package_markdown($rootPath, $packageSlug, $packagePath, $manifest, $composer, true);

    $documents = [
        'README.md' => capell_docs_package_markdown($rootPath, $packageSlug, $packagePath, $manifest, $composer, false),
        'docs/overview.md' => $overview,
    ];

    foreach ($documents as $relativeDocumentPath => $document) {
        $documentPath = $packagePath . '/' . $relativeDocumentPath;
        $current = is_file($documentPath) ? (string) file_get_contents($documentPath) : '';

        if ($current === $document) {
            continue;
        }

        $changedFiles[] = capell_docs_relative_path($rootPath, $documentPath);

        if (! $checkOnly) {
            if (! is_dir(dirname($documentPath))) {
                mkdir(dirname($documentPath), 0755, true);
            }

            file_put_contents($documentPath, $document);
        }
    }
}

if ($changedFiles !== []) {
    if ($checkOnly) {
        fwrite(STDERR, "Package generated docs are stale:\n");

        foreach ($changedFiles as $changedFile) {
            fwrite(STDERR, '- ' . $changedFile . "\n");
        }

        return 1;
    }

    fwrite(STDOUT, 'Wrote ' . count($changedFiles) . " package generated docs.\n");

    return 0;
}

fwrite(STDOUT, "Package generated docs are current.\n");

return 0;

/**
 * @return array<string, string>
 */
function capell_docs_package_paths(string $packagesPath): array
{
    $packagePaths = [];

    foreach (new DirectoryIterator($packagesPath) as $directory) {
        if (! $directory->isDir() || $directory->isDot()) {
            continue;
        }

        $packagePath = $directory->getPathname();

        if (! is_file($packagePath . '/capell.json')) {
            continue;
        }

        $packagePaths[$directory->getFilename()] = $packagePath;
    }

    ksort($packagePaths);

    return $packagePaths;
}

/**
 * @return array<string, mixed>
 */
function capell_docs_read_json(string $path): array
{
    if (! is_file($path)) {
        return [];
    }

    $decoded = json_decode((string) file_get_contents($path), associative: true, flags: JSON_THROW_ON_ERROR);

    return is_array($decoded) ? $decoded : [];
}

/**
 * @param  array<string, mixed>  $manifest
 * @param  array<string, mixed>  $composer
 */
function capell_docs_package_markdown(string $rootPath, string $packageSlug, string $packagePath, array $manifest, array $composer, bool $forOverview): string
{
    $displayName = capell_docs_string($manifest['displayName'] ?? null)
        ?? capell_docs_title_from_slug($packageSlug);
    $composerName = capell_docs_string($manifest['name'] ?? null)
        ?? capell_docs_string($composer['name'] ?? null)
        ?? 'capell-app/' . $packageSlug;
    $namespace = capell_docs_string($manifest['namespace'] ?? null)
        ?? capell_docs_first_namespace($composer);
    $kind = capell_docs_string($manifest['kind'] ?? null) ?? 'package';
    $product = capell_docs_array($manifest['product'] ?? null);
    $tier = capell_docs_string($product['tier'] ?? null) ?? 'unknown';
    $bundle = capell_docs_string($product['bundle'] ?? null) ?? 'unknown';
    $productGroup = capell_docs_string($product['group'] ?? null) ?? 'unknown';
    $surfaces = capell_docs_string_list($manifest['surfaces'] ?? []);
    $status = capell_docs_manifest_status($manifest);
    $isPipeline = strtolower($status) === 'pipeline';
    $schemaStatus = capell_docs_schema_status($manifest, $packagePath);
    $description = capell_docs_string($manifest['description'] ?? null)
        ?? capell_docs_string($composer['description'] ?? null)
        ?? $displayName . ' extends Capell.';
    $description = capell_docs_clean_sentence($description);

    $lines = [
        '# ' . $displayName,
        '',
        '<!-- prettier-ignore-start -->',
        '',
        '## What This Plugin Adds',
        '',
        capell_docs_opening_status_sentence($displayName, $status, $schemaStatus, $kind, $productGroup, $composerName, $surfaces),
        '',
        $description,
        '',
        capell_docs_surface_outcome($displayName, $surfaces, $kind, $packagePath, $isPipeline),
        '',
        'Status details:',
        '',
        '- Status: ' . $status,
        '- Tier: ' . $tier,
        '- Bundle: ' . $bundle,
        '- Composer package: `' . $composerName . '`',
        '- Namespace: `' . ($namespace ?? 'Docs gap: namespace not declared') . '`',
        '- Theme key: ' . (capell_docs_string($manifest['themeKey'] ?? null) !== null ? '`' . capell_docs_string($manifest['themeKey'] ?? null) . '`' : 'not applicable'),
        '',
        '## Why It Matters',
        '',
        '**For developers:** ' . capell_docs_developer_value($packagePath, $manifest),
        '',
        '**For teams:** ' . capell_docs_team_value($manifest, $description),
        '',
        ...($packageSlug === 'bookings' ? ['## Start Simple, Add Depth Later', '', 'Most teams should launch with the default request form, service setup, staff availability, and admin queue before enabling reminders, reviews, waitlists, travel planning, payments, or advanced automation. Use `docs/adoption-guide.md` as the rollout path for owners, operators, agencies, and developers.', ''] : []),
        ...($packageSlug === 'theme-estate-agents' ? ['runtime inheritance uses `extends: default`, so the theme keeps Foundation Theme behaviour while replacing property-specific public presentation. It requires `capell-app/theme-foundation` and `capell-app/frontend`.', ''] : []),
        '## Screens And Workflow',
        '',
        ...capell_docs_screens_and_workflow($packagePath, $manifest, $forOverview),
        '',
        '## Technical Shape',
        '',
        ...capell_docs_technical_shape($rootPath, $packagePath, $manifest),
        '',
        ...capell_docs_package_specific_sections($packageSlug, $forOverview),
        '## Data Model',
        '',
        ...capell_docs_data_model($packagePath, $manifest, $kind, $isPipeline),
        '',
        '## Install Impact',
        '',
        ...capell_docs_install_impact($manifest, $packagePath, $isPipeline),
        '',
        '## Common Pitfalls',
        '',
        ...capell_docs_common_pitfalls($manifest, $packagePath, $isPipeline),
        '',
        ...capell_docs_troubleshooting($packageSlug, $packagePath, $manifest, $surfaces, $isPipeline),
        '## Quick Start',
        '',
        ...capell_docs_quick_start($displayName, $composerName, $manifest, $surfaces, $packagePath, $isPipeline),
        '',
        '## Next Steps',
        '',
        ...capell_docs_next_steps($packageSlug, $packagePath, $manifest, $isPipeline, $forOverview),
        '',
        '<!-- prettier-ignore-end -->',
        '',
    ];

    return implode("\n", $lines);
}

/**
 * Build the admin-first docs/overview.md from a hand-authored fragment.
 *
 * The fragment (docs/overview.admin.md) holds the admin sections only, in plain editor
 * language. This wraps it with the package H1 (kept in sync with displayName) and a footer
 * pointing editors at the admin guide and developers at the README and reference docs. The
 * developer-shaped content is not lost: it remains the generated README.md.
 *
 * @param  array<string, mixed>  $manifest
 */
function capell_docs_admin_overview_markdown(string $packageSlug, string $packagePath, array $manifest): string
{
    $displayName = capell_docs_string($manifest['displayName'] ?? null)
        ?? capell_docs_title_from_slug($packageSlug);

    $fragment = trim((string) file_get_contents($packagePath . '/docs/overview.admin.md'));

    $footer = [];

    if (is_file($packagePath . '/docs/admin-guide.md')) {
        $footer[] = 'For how to use ' . $displayName . ', see the [admin guide](admin-guide.md).';
    }

    $developerLinks = '[README](../README.md)';

    if (is_file($packagePath . '/docs/' . $packageSlug . '-api.md')) {
        $developerLinks .= ', [' . $packageSlug . '-api.md](' . $packageSlug . '-api.md)';
    }

    if (is_file($packagePath . '/docs/' . $packageSlug . '-database.md')) {
        $developerLinks .= ', [' . $packageSlug . '-database.md](' . $packageSlug . '-database.md)';
    }

    $footer[] = 'For developers: see the ' . $developerLinks . '.';

    $lines = [
        '# ' . $displayName,
        '',
        '<!-- prettier-ignore-start -->',
        '',
        $fragment,
        '',
        '---',
        '',
        implode("\n", $footer),
        '',
        '<!-- prettier-ignore-end -->',
        '',
    ];

    return implode("\n", $lines);
}

/**
 * @return list<string>
 */
function capell_docs_package_specific_sections(string $packageSlug, bool $forOverview): array
{
    if ($packageSlug === 'media-library') {
        $screenshotContractLink = $forOverview
            ? '[screenshots.json](screenshots.json)'
            : '[docs/screenshots.json](docs/screenshots.json)';

        return [
            '## Media Handling Contract',
            '',
            'Media Library wraps Awcodes Curator as the Capell media backend and does not generate responsive conversions. Curator-backed media exposes configured URLs and metadata; responsive variant generation must come from another package or host implementation.',
            '',
            'Evidence and wording rules:',
            '',
            '- The capture contract is ' . $screenshotContractLink . '.',
            '- The committed screenshot captures remain runner evidence until they show populated Capell media workflows.',
            '- Do not describe this package as generating responsive variants.',
            '- Keep migration and media-health claims tied to the Curator model, health page, table, field factory, and migration command.',
            '',
        ];
    }

    if ($packageSlug === 'privacy-center') {
        return [
            '## Shipped And Deferred Privacy Surfaces',
            '',
            'Privacy Center currently ships admin and console surfaces for consent policy records, privacy requests, retention rules, policy acceptances, retention execution, and audited DSAR handling. It also ships a cache-safe public preference center for cookie consent preferences, but it does not ship a public DSAR intake form.',
            '',
            'Current boundaries:',
            '',
            '- Public consent: use the public cookie consent preference center and `RecordConsentAction`; `RecordConsentAction` can infer a subject from a source model when another package mirrors consent.',
            '- Subject data: the cross-package subject-data export/erasure registry is Action-backed through `BuildPrivacyExportAction` and `AnonymizePrivacySubjectAction`.',
            '- Request intake: open requests through `OpenPrivacyRequestAction` or admin workflows until a public DSAR intake route is shipped.',
            '- Evidence hashing: configure `CAPELL_PRIVACY_CENTER_HASH_SECRET` before relying on request or consent evidence hashes.',
            '- Admin surface: The admin provider contributes these Filament surfaces: consent policies, consent records, policy acceptances, privacy requests, retention rules, and the overview widget.',
            '',
        ];
    }

    if ($packageSlug === 'theme-agency') {
        return [
            '## Marketplace Classification',
            '',
            'Tier: **premium**',
            '',
            'Product group: **Capell Themes**',
            '',
        ];
    }

    if ($packageSlug === 'theme-portfolio') {
        return [
            '## Theme Inheritance Contract',
            '',
            'Product group:',
            '**Capell Themes**',
            '',
            '- Product group: `Capell Themes`',
            '- Manifest extends: `default`',
            '- Runtime extends: `default`',
            '- Portfolio runtime inheritance uses `extends: default` and requires `capell-app/frontend` for the built-in default fallback.',
            '',
        ];
    }

    if ($packageSlug === 'theme-restaurant') {
        return [
            '## Theme Inheritance Contract',
            '',
            'Product group:',
            '**Capell Themes**',
            '',
            '- Product group: `Capell Themes`',
            '- Manifest extends: `default`',
            '- Runtime extends: `default`',
            '- Restaurant runtime inheritance uses `extends: default` and requires `capell-app/frontend` for the built-in default fallback.',
            '',
        ];
    }

    if ($packageSlug === 'theme-saas') {
        return [
            '## Test Command',
            '',
            'Run package tests from the repository root.',
            '',
            'From the repository root, run `vendor/bin/pest packages/theme-saas/tests`; this package does not ship its own PHPUnit config.',
            '',
        ];
    }

    if ($packageSlug === 'theme-foundation') {
        return [
            '## Child Theme Override Contract',
            '',
            'Foundation Theme owns the stable child theme override surface for Capell themes. Child themes should declare `extends: \'default\'` and override documented sections, views, tokens, and chrome areas instead of replacing the whole public rendering path.',
            '',
            'Stable contract points:',
            '',
            '- Theme Studio sections: `navigation`, `hero`, `features`, `proof`, `content-listing`, `cta`, `footer`.',
            '- Shared views: `capell::theme.page`, `capell::layout.area`, `capell::media.svg`.',
            '- Runtime tokens: `--foundation-page-bg`, `--foundation-section-spacing`, `--foundation-widget-gap`.',
            '- Layout Builder chrome areas: `header`.',
            '- Public-output rule: child themes must not expose authoring metadata, editor controls, model IDs, field paths, permissions, or signed editor URLs.',
            '',
        ];
    }

    return [];
}

/**
 * @param  array<string, mixed>  $manifest
 */
function capell_docs_manifest_status(array $manifest): string
{
    $capabilities = capell_docs_string_list($manifest['capabilities'] ?? []);
    $commercial = capell_docs_array($manifest['commercial'] ?? null);
    $requestedCertification = capell_docs_string($commercial['requestedCertification'] ?? null);

    if (in_array('pipeline', $capabilities, true) || $requestedCertification === 'review-required') {
        return 'Pipeline';
    }

    return 'Available';
}

/**
 * @param  list<string>  $surfaces
 */
function capell_docs_opening_status_sentence(string $displayName, string $status, string $schemaStatus, string $kind, string $productGroup, string $composerName, array $surfaces): string
{
    $surfaceText = $surfaces === [] ? 'none declared' : implode(', ', $surfaces);

    if (strtolower($status) === 'pipeline') {
        return sprintf(
            '%s is a **%s**, **%s** Capell %s in the **%s** product group. It is tracked as `%s` and plans these surfaces: %s.',
            $displayName,
            $status,
            $schemaStatus,
            $kind,
            $productGroup,
            $composerName,
            $surfaceText,
        );
    }

    return sprintf(
        '%s is an **%s**, **%s** Capell %s in the **%s** product group. It ships as `%s` and extends these surfaces: %s.',
        $displayName,
        $status,
        $schemaStatus,
        $kind,
        $productGroup,
        $composerName,
        $surfaceText,
    );
}

function capell_docs_surface_outcome(string $displayName, array $surfaces, string $kind, string $packagePath, bool $isPipeline): string
{
    if ($isPipeline) {
        return 'This package is not documented as shipped. Treat screens, workflows, providers, and install behaviour as review-required until certification review and host install verification are complete.';
    }

    if ($kind === 'theme') {
        return 'After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.';
    }

    $hasAdminClasses = capell_docs_path_list($packagePath . '/src/Filament', '*.php') !== [];

    if (in_array('admin', $surfaces, true) && in_array('frontend', $surfaces, true)) {
        if ($hasAdminClasses) {
            return 'After install, admins get package-owned management surfaces and public users may see package-owned frontend output or routes.';
        }

        return 'After install, the package contributes admin-facing extension points and may affect public output or routes. Docs gap: no concrete Filament resource or page was detected.';
    }

    if (in_array('admin', $surfaces, true)) {
        if ($hasAdminClasses) {
            return 'After install, admins get package-owned management or reporting surfaces inside Capell.';
        }

        return 'After install, the package contributes admin-facing extension points. Docs gap: no concrete Filament resource or page was detected.';
    }

    if (in_array('frontend', $surfaces, true)) {
        return 'After install, the package affects public rendering, public routes, or frontend runtime behaviour.';
    }

    if (in_array('console', $surfaces, true)) {
        return 'After install, the package is operated through console commands or background maintenance hooks.';
    }

    return 'Docs gap: no package surface is declared in `capell.json`.';
}

/**
 * @param  array<string, mixed>  $manifest
 */
function capell_docs_developer_value(string $packagePath, array $manifest): string
{
    $facts = [];

    if (capell_docs_provider_classes($packagePath, $manifest) !== []) {
        $facts[] = 'service providers';
    }

    if (capell_docs_path_list($packagePath . '/src/Actions', '*.php') !== []) {
        $facts[] = 'Actions';
    }

    if (capell_docs_path_list($packagePath . '/src/Data', '*.php') !== []) {
        $facts[] = 'Data objects';
    }

    if (capell_docs_path_list($packagePath . '/src/Models', '*.php') !== []) {
        $facts[] = 'models';
    }

    if (capell_docs_route_files($packagePath) !== []) {
        $facts[] = 'Laravel routes';
    }

    if (capell_docs_path_list($packagePath . '/src/Filament', '*.php') !== []) {
        $facts[] = 'Filament classes';
    }

    if (capell_docs_path_list($packagePath . '/resources/views', '*.blade.php') !== []) {
        $facts[] = 'Blade views';
    }

    if ($facts === []) {
        return 'The package exposes a small install surface. Inspect its provider and tests before adding integration behaviour.';
    }

    return 'The package gives developers package-owned ' . capell_docs_sentence_list($facts) . ' instead of pushing this behaviour into core or application code.';
}

/**
 * @param  array<string, mixed>  $manifest
 */
function capell_docs_team_value(array $manifest, string $description): string
{
    $marketplace = capell_docs_array($manifest['marketplace'] ?? null);
    $summary = capell_docs_string($marketplace['summary'] ?? null);

    if ($summary !== null) {
        return capell_docs_clean_sentence($summary);
    }

    return $description;
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<string>
 */
function capell_docs_screens_and_workflow(string $packagePath, array $manifest, bool $forOverview): array
{
    $lines = [];
    $screenshotPath = $packagePath . '/docs/screenshots.json';
    $screenshotEntries = [];

    if (is_file($screenshotPath)) {
        $screenshotManifest = capell_docs_read_json($screenshotPath);
        $entries = $screenshotManifest['entries'] ?? [];
        $screenshotEntries = is_array($entries) ? $entries : [];
    }

    if ($screenshotEntries !== []) {
        $lines[] = 'Screenshot contract: `' . ($forOverview ? 'screenshots.json' : 'docs/screenshots.json') . '`.';
        $lines[] = '';

        foreach ($screenshotEntries as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $title = capell_docs_string($entry['title'] ?? null)
                ?? capell_docs_string($entry['id'] ?? null)
                ?? 'Untitled screenshot';
            $surface = capell_docs_string($entry['surface'] ?? null);
            $required = ($entry['required'] ?? true) === false ? 'optional' : 'required';
            $suffix = $surface !== null ? ' (' . $surface . ', ' . $required . ')' : ' (' . $required . ')';

            $lines[] = '- ' . rtrim($title, '.') . $suffix . '.';
        }

        return $lines;
    }

    $marketplace = capell_docs_array($manifest['marketplace'] ?? null);
    $screenshots = $marketplace['screenshots'] ?? [];

    if (is_array($screenshots) && $screenshots !== []) {
        $lines[] = 'Marketplace media is declared in `capell.json`:';
        $lines[] = '';

        foreach ($screenshots as $screenshot) {
            if (! is_array($screenshot)) {
                continue;
            }

            $caption = capell_docs_string($screenshot['caption'] ?? null)
                ?? capell_docs_string($screenshot['alt'] ?? null)
                ?? capell_docs_string($screenshot['path'] ?? null)
                ?? 'Marketplace screenshot';
            $lines[] = '- ' . rtrim($caption, '.') . '.';
        }

        return $lines;
    }

    return [
        'Docs gap: add `docs/screenshots.json` before promoting this package with visual workflow claims.',
        '',
        '- Admin index screen if the package has a Filament resource.',
        '- Create/edit screen if editors create records.',
        '- Settings/configuration screen when settings exist.',
        '- Frontend output when the package renders public pages.',
        '- Package detail or install intent screen when marketplace-owned.',
    ];
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<string>
 */
function capell_docs_technical_shape(string $rootPath, string $packagePath, array $manifest): array
{
    $lines = [];
    $providers = capell_docs_provider_classes($packagePath, $manifest);
    $configFiles = capell_docs_relative_files($rootPath, capell_docs_path_list($packagePath . '/config', '*.php'));
    $migrationFiles = capell_docs_relative_files($rootPath, capell_docs_path_list($packagePath . '/database/migrations', '*.php'));
    $settingsMigrations = capell_docs_relative_files($rootPath, capell_docs_path_list($packagePath . '/database/settings', '*.php'));
    $settingsClasses = capell_docs_class_files($packagePath . '/src/Settings');
    $models = capell_docs_class_files($packagePath . '/src/Models');
    $filament = capell_docs_class_files($packagePath . '/src/Filament');
    $livewire = capell_docs_class_files($packagePath . '/src/Livewire');
    $routeFiles = capell_docs_relative_files($rootPath, capell_docs_route_files($packagePath));
    $policies = capell_docs_class_files($packagePath . '/src/Policies');
    $events = capell_docs_class_files($packagePath . '/src/Events');
    $listeners = capell_docs_class_files($packagePath . '/src/Listeners');
    $actions = capell_docs_class_files($packagePath . '/src/Actions');
    $dataObjects = capell_docs_class_files($packagePath . '/src/Data');
    $jobs = capell_docs_class_files($packagePath . '/src/Jobs');
    $schedules = capell_docs_class_files($packagePath . '/src/Scheduled');
    $views = capell_docs_relative_files($rootPath, capell_docs_path_list($packagePath . '/resources/views', '*.blade.php'));
    $commands = capell_docs_manifest_commands($manifest);
    $commandClasses = capell_docs_class_files($packagePath . '/src/Console');
    $contributions = capell_docs_manifest_contributions($manifest);
    $healthChecks = capell_docs_manifest_health_checks($manifest);
    $cacheTags = capell_docs_string_list(capell_docs_array($manifest['performance'] ?? null)['cacheTags'] ?? []);

    capell_docs_add_fact_lines($lines, 'Service providers', $providers);
    capell_docs_add_fact_lines($lines, 'Config files', $configFiles);
    capell_docs_add_fact_lines($lines, 'Migrations', $migrationFiles);
    capell_docs_add_fact_lines($lines, 'Settings migrations', $settingsMigrations);
    capell_docs_add_fact_lines($lines, 'Settings classes', $settingsClasses);
    capell_docs_add_fact_lines($lines, 'Models', $models);
    capell_docs_add_fact_lines($lines, 'Filament classes', capell_docs_limit($filament, 12));
    capell_docs_add_fact_lines($lines, 'Livewire components', $livewire);
    capell_docs_add_fact_lines($lines, 'Route files', $routeFiles);
    capell_docs_add_fact_lines($lines, 'Policies', $policies);
    capell_docs_add_fact_lines($lines, 'Events', $events);
    capell_docs_add_fact_lines($lines, 'Listeners', $listeners);
    capell_docs_add_fact_lines($lines, 'Actions', capell_docs_limit($actions, 12));
    capell_docs_add_fact_lines($lines, 'Data objects', capell_docs_limit($dataObjects, 12));
    capell_docs_add_fact_lines($lines, 'Jobs', $jobs);
    capell_docs_add_fact_lines($lines, 'Schedules', $schedules);
    capell_docs_add_fact_lines($lines, 'Command signatures', $commands);
    capell_docs_add_fact_lines($lines, 'Console command classes', $commandClasses);
    capell_docs_add_fact_lines($lines, 'Manifest contributions', $contributions);
    capell_docs_add_fact_lines($lines, 'Health checks', $healthChecks);
    capell_docs_add_fact_lines($lines, 'Blade views', capell_docs_limit($views, 12));
    capell_docs_add_fact_lines($lines, 'Cache tags', $cacheTags);

    if ($lines === []) {
        $lines[] = '- Docs gap: no technical surface was detected from standard package paths.';
    }

    return $lines;
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<string>
 */
function capell_docs_data_model(string $packagePath, array $manifest, string $kind, bool $isPipeline): array
{
    $database = capell_docs_array($manifest['database'] ?? null);
    $requiredTables = capell_docs_string_list($database['requiredTables'] ?? []);
    $protectedTables = capell_docs_string_list($database['protectedTables'] ?? []);
    $migrationFiles = capell_docs_path_list($packagePath . '/database/migrations', '*.php');
    $models = capell_docs_class_files($packagePath . '/src/Models');

    if (($database['migrations'] ?? false) !== true && $requiredTables === [] && $migrationFiles === []) {
        if ($kind === 'theme') {
            return [
                'This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.',
            ];
        }

        return [
            'This package has no schema impact. It does not declare package-owned migrations or required tables.',
            '',
            'Docs gap: document extension points here if the package delegates persistence to a host package.',
        ];
    }

    $lines = [];
    capell_docs_add_fact_lines($lines, 'Required tables', $requiredTables);
    capell_docs_add_fact_lines($lines, 'Protected tables', $protectedTables);
    capell_docs_add_fact_lines($lines, 'Models', $models);
    capell_docs_add_fact_lines($lines, 'Migration files', array_map(static fn (string $path): string => basename($path), $migrationFiles));
    $lines[] = '- Migration impact: ' . ($isPipeline
        ? 'review required; migration files exist and must be verified through the host install flow.'
        : 'run host migrations through the package install flow before opening package surfaces.');
    $lines[] = '- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.';

    return $lines;
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<string>
 */
function capell_docs_install_impact(array $manifest, string $packagePath, bool $isPipeline): array
{
    $lines = [];
    $surfaces = capell_docs_string_list($manifest['surfaces'] ?? []);
    $permissions = capell_docs_string_list($manifest['permissions'] ?? []);
    $routeFiles = capell_docs_route_files($packagePath);
    $database = capell_docs_array($manifest['database'] ?? null);
    $settings = capell_docs_string_list($manifest['settings'] ?? []);
    $commands = capell_docs_manifest_commands($manifest);
    $commandClasses = capell_docs_class_files($packagePath . '/src/Console');
    $performance = capell_docs_array($manifest['performance'] ?? null);
    $cacheTags = capell_docs_string_list($performance['cacheTags'] ?? []);
    $hasAdminClasses = capell_docs_path_list($packagePath . '/src/Filament', '*.php') !== [];
    $adminContributions = array_filter(
        capell_docs_manifest_contributions($manifest),
        static fn (string $contribution): bool => str_contains($contribution, 'admin'),
    );

    if ($hasAdminClasses) {
        $adminNavigation = 'adds package-owned Filament classes when registered.';
    } elseif ($adminContributions !== []) {
        $adminNavigation = 'contributes admin extension points through `capell.json`.';
    } elseif (in_array('admin', $surfaces, true)) {
        $adminNavigation = 'admin-facing extension points are declared, but no concrete Filament class was detected.';
    } else {
        $adminNavigation = 'no admin surface declared.';
    }

    if ($isPipeline) {
        $lines[] = '- Availability: Pipeline; review required before installing in a host Capell app.';
    }

    $lines[] = '- Admin navigation: ' . $adminNavigation;
    $lines[] = '- Permissions: ' . ($permissions === [] ? 'none declared in `capell.json`.' : implode(', ', array_map(static fn (string $permission): string => '`' . $permission . '`', $permissions)) . '.');
    $lines[] = '- Public routes: ' . ($routeFiles === [] ? 'none detected in package route files.' : 'route files exist and must be reviewed before public enablement.');
    $hasMigrationFiles = capell_docs_path_list($packagePath . '/database/migrations', '*.php') !== [];
    $lines[] = '- Database changes: ' . capell_docs_database_install_impact($database, $hasMigrationFiles);
    $lines[] = '- Settings: ' . capell_docs_settings_install_impact($settings, $packagePath);
    $hasJobsOrSchedules = capell_docs_path_list($packagePath . '/src/Jobs', '*.php') !== []
        || capell_docs_path_list($packagePath . '/src/Scheduled', '*.php') !== [];
    $lines[] = '- Queues or schedules: ' . ($hasJobsOrSchedules ? 'review package jobs or schedules before install.' : 'none detected in standard package paths.');
    $lines[] = '- Cache tags: ' . ($cacheTags === [] ? 'none declared.' : implode(', ', array_map(static fn (string $tag): string => '`' . $tag . '`', $cacheTags)) . '.');
    $lines[] = '- Commands: ' . capell_docs_commands_install_impact($commands, $commandClasses);

    return $lines;
}

/**
 * @param  array<string, mixed>  $database
 */
function capell_docs_database_install_impact(array $database, bool $hasMigrationFiles): string
{
    if (($database['migrations'] ?? false) === true) {
        return 'package migrations are declared.';
    }

    if ($hasMigrationFiles) {
        return 'migration files exist in the scaffold, but `capell.json` does not currently declare active migrations.';
    }

    return 'no package migrations declared.';
}

/**
 * @param  list<string>  $settings
 */
function capell_docs_settings_install_impact(array $settings, string $packagePath): string
{
    if ($settings !== []) {
        return implode(', ', array_map(static fn (string $setting): string => '`' . $setting . '`', $settings)) . '.';
    }

    $hasSettingsFiles = capell_docs_path_list($packagePath . '/src/Settings', '*.php') !== []
        || capell_docs_path_list($packagePath . '/database/settings', '*.php') !== [];

    if ($hasSettingsFiles) {
        return 'settings classes or settings migrations exist; verify the install flow registers them.';
    }

    return 'no package settings declared.';
}

/**
 * @param  list<string>  $commands
 * @param  list<string>  $commandClasses
 */
function capell_docs_commands_install_impact(array $commands, array $commandClasses): string
{
    if ($commands !== []) {
        return implode(', ', array_map(static fn (string $command): string => '`' . $command . '`', $commands)) . '.';
    }

    if ($commandClasses !== []) {
        return 'console command classes detected: ' . capell_docs_inline_list($commandClasses) . '.';
    }

    return 'none declared.';
}

/**
 * @param  list<string>  $items
 */
function capell_docs_inline_list(array $items): string
{
    return implode(', ', array_map(static fn (string $item): string => '`' . $item . '`', $items));
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<string>
 */
function capell_docs_common_pitfalls(array $manifest, string $packagePath, bool $isPipeline): array
{
    if ($isPipeline) {
        return [
            '- Do not describe review-required providers, routes, migrations, commands, or admin resources as shipped install behaviour.',
            '- Keep class references out of `capell.json` until those classes exist and focused tests pass.',
            '- Keep screenshot and workflow notes labelled as review-required until real package screens are captured or verified.',
        ];
    }

    $surfaces = capell_docs_string_list($manifest['surfaces'] ?? []);
    $database = capell_docs_array($manifest['database'] ?? null);
    $settings = capell_docs_string_list($manifest['settings'] ?? []);
    $routeFiles = capell_docs_route_files($packagePath);
    $pitfalls = [];

    if (($database['migrations'] ?? false) === true) {
        $pitfalls[] = 'Run migrations before opening package resources or public routes.';
    }

    if ($settings !== []) {
        $pitfalls[] = 'Configure package settings before testing production-like workflows.';
    }

    if ($routeFiles !== []) {
        $pitfalls[] = 'Review route middleware, throttling, signed URLs, and public-output safety before exposing routes.';
    }

    if (in_array('frontend', $surfaces, true)) {
        $pitfalls[] = 'Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.';
    }

    if (in_array('console', $surfaces, true)) {
        $pitfalls[] = 'Run package commands from the host app; in this repository use `vendor/bin/pest` for package tests.';
    }

    if ($pitfalls === []) {
        $pitfalls[] = 'Verify the package is installed before expecting its provider, views, or extension contributions to run.';
    }

    $pitfalls[] = 'Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.';

    return array_map(static fn (string $pitfall): string => '- ' . $pitfall, $pitfalls);
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<string>
 */
function capell_docs_troubleshooting(string $packageSlug, string $packagePath, array $manifest, array $surfaces, bool $isPipeline): array
{
    if ($isPipeline) {
        return [
            '## Troubleshooting',
            '',
            '| Symptom | Likely cause | Check | Fix |',
            '| --- | --- | --- | --- |',
            '| Package tests fail before assertions | Manifest or provider metadata references classes that do not exist yet | Check `capell.json`, package `composer.json`, and provider imports | Remove planned class references or add the classes and focused tests before marking the package Available |',
            '| Docs describe installable behaviour | Pipeline docs drifted into shipped-language claims | Check `status`, `Install Impact`, and `Quick Start` | Keep the package labelled Pipeline until the install flow is verified |',
            '',
        ];
    }

    if (! capell_docs_has_operational_failure_mode($packageSlug, $packagePath, $manifest)) {
        return [];
    }

    $database = capell_docs_array($manifest['database'] ?? null);
    $hasMigrations = ($database['migrations'] ?? false) === true
        || capell_docs_path_list($packagePath . '/database/migrations', '*.php') !== [];
    $routeFiles = capell_docs_route_files($packagePath);
    $hasJobs = capell_docs_path_list($packagePath . '/src/Jobs', '*.php') !== [];
    $commands = capell_docs_manifest_commands($manifest);
    $rows = [
        '| Symptom | Likely cause | Check | Fix |',
        '| --- | --- | --- | --- |',
        '| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |',
    ];

    if ($hasMigrations) {
        $rows[] = '| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |';
    }

    if ($routeFiles !== []) {
        $rows[] = '| Route returns unexpected output | Route cache, middleware, or signed URL setup does not match the package route file | Check the route files listed in `Technical Shape` | Clear route cache and verify middleware before exposing public routes |';
    }

    if ($hasJobs || $commands !== []) {
        $rows[] = '| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |';
    }

    if (in_array('frontend', $surfaces, true)) {
        $rows[] = '| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |';
    }

    return [
        '## Troubleshooting',
        '',
        ...$rows,
        '',
    ];
}

/**
 * @param  array<string, mixed>  $manifest
 */
function capell_docs_has_operational_failure_mode(string $packageSlug, string $packagePath, array $manifest): bool
{
    $knownOperationalPackages = [
        'agent-delivery',
        'api',
        'block-library',
        'bookings',
        'contacts',
        'customer-portal',
        'document-lifecycle',
        'exception-reports',
        'experiments',
        'inertia',
        'knowledge-base',
        'layout-builder',
        'payments',
        'privacy-center',
        'social-feeds',
        'structured-content-library',
        'url-manager',
    ];

    if (in_array($packageSlug, $knownOperationalPackages, true)) {
        return true;
    }

    foreach (['config', 'routes', 'src/Jobs', 'src/Health'] as $relativePath) {
        if (is_dir($packagePath . '/' . $relativePath)) {
            return true;
        }
    }

    if (capell_docs_manifest_commands($manifest) !== []) {
        return true;
    }

    $database = capell_docs_array($manifest['database'] ?? null);

    return ($database['migrations'] ?? false) === true
        || capell_docs_path_list($packagePath . '/database/migrations', '*.php') !== [];
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<string>
 */
function capell_docs_quick_start(string $displayName, string $composerName, array $manifest, array $surfaces, string $packagePath, bool $isPipeline): array
{
    if ($isPipeline) {
        return [
            '1. Review `capell.json`, package docs, and any scaffolded source before treating the package as installable.',
            '2. Complete certification review, host install verification, migrations, and admin surface checks.',
            '3. Run the docs checks, focused package tests, and install verification before changing the status from Pipeline to Available.',
        ];
    }

    $commands = capell_docs_array($manifest['commands'] ?? []);
    $setupCommand = capell_docs_string($commands['setup'] ?? null)
        ?? capell_docs_string($commands['install'] ?? null)
        ?? capell_docs_string($commands['demo'] ?? null);
    $database = capell_docs_array($manifest['database'] ?? null);
    $hasMigrations = ($database['migrations'] ?? false) === true
        || capell_docs_path_list($packagePath . '/database/migrations', '*.php') !== [];
    $setupStep = $setupCommand !== null
        ? '`' . capell_docs_artisan_command($setupCommand) . '`'
        : ($hasMigrations
            ? '`php artisan migrate`'
            : 'no package migrations are declared; clear cached config and routes if the host app uses caches');
    $hasAdminClasses = capell_docs_path_list($packagePath . '/src/Filament', '*.php') !== [];
    $openSurface = in_array('admin', $surfaces, true) && $hasAdminClasses
        ? 'Open the related Capell admin surface and verify ' . $displayName . ' appears.'
        : 'Verify the package provider is registered and the related frontend, command, or extension point is active.';

    return [
        '1. Install the package: `composer require ' . $composerName . '`.',
        '2. Run the required setup: ' . capell_docs_finish_sentence($setupStep),
        '3. ' . $openSurface,
    ];
}

function capell_docs_artisan_command(string $command): string
{
    if (str_starts_with($command, 'php ')
        || str_starts_with($command, './')
        || str_starts_with($command, 'composer ')
        || str_starts_with($command, 'vendor/')
    ) {
        return $command;
    }

    return 'php artisan ' . $command;
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<string>
 */
function capell_docs_next_steps(string $packageSlug, string $packagePath, array $manifest, bool $isPipeline, bool $forOverview): array
{
    $links = $forOverview
        ? ['- [Package docs index](README.md)']
        : [
            '- [Package docs](docs/README.md)',
            '- [Overview](docs/overview.md)',
        ];

    if (is_file($packagePath . '/docs/screenshots.json')) {
        $links[] = '- [Screenshot contract](' . ($forOverview ? 'screenshots.json' : 'docs/screenshots.json') . ')';
    }

    if (is_dir($packagePath . '/docs/assets/marketplace')) {
        $links[] = '- [Marketplace assets](' . ($forOverview ? 'assets/marketplace/' : 'docs/assets/marketplace/') . ')';
    }

    $rootDocsPrefix = $forOverview ? '../../../docs' : '../../docs';
    $links[] = '- [Capell content language plan](' . $rootDocsPrefix . '/CONTENT_LANGUAGE_PLAN.md)';
    $links[] = '- [Capell documentation design system](' . $rootDocsPrefix . '/DESIGN_SYSTEM.md)';
    $links[] = '- [Capell and package ERD notes](' . $rootDocsPrefix . '/erd/capell-and-package-erds.md)';

    $dependencies = capell_docs_array($manifest['dependencies'] ?? null);
    $relatedPackages = [
        ...capell_docs_string_list($dependencies['requires'] ?? []),
        ...capell_docs_string_list($dependencies['supports'] ?? []),
    ];
    $localRelatedLinks = [];

    foreach (array_unique($relatedPackages) as $relatedPackage) {
        if (! str_starts_with($relatedPackage, 'capell-app/')) {
            continue;
        }

        $relatedSlug = substr($relatedPackage, strlen('capell-app/'));

        if ($relatedSlug === $packageSlug || ! is_dir(dirname($packagePath) . '/' . $relatedSlug)) {
            continue;
        }

        $relatedPrefix = $forOverview ? '../../' : '../';
        $localRelatedLinks[] = '[' . capell_docs_title_from_slug($relatedSlug) . '](' . $relatedPrefix . $relatedSlug . '/README.md)';
    }

    if ($localRelatedLinks !== []) {
        $links[] = '- Related packages: ' . implode(', ', $localRelatedLinks) . '.';
    }

    if ($isPipeline) {
        $links[] = '- Docs gap: finish certification review and host install verification before marking this package Available.';
    } elseif (capell_docs_path_list($packagePath . '/tests', '*.php') !== []) {
        $testCommand = 'vendor/bin/pest packages/' . $packageSlug . '/tests';

        if ($packageSlug !== 'theme-saas') {
            $testCommand .= ' --configuration=phpunit.xml';
        }

        $links[] = '- Focused tests: `' . $testCommand . '`.';
    } else {
        $links[] = '- Docs gap: add focused package tests before marking this package Available.';
    }

    return $links;
}

function capell_docs_schema_status(array $manifest, string $packagePath): string
{
    $database = capell_docs_array($manifest['database'] ?? null);

    if (($database['migrations'] ?? false) === true || capell_docs_path_list($packagePath . '/database/migrations', '*.php') !== []) {
        return 'Schema-owning';
    }

    return 'No schema impact';
}

function capell_docs_first_namespace(array $composer): ?string
{
    $autoload = capell_docs_array($composer['autoload'] ?? null);
    $psr4 = capell_docs_array($autoload['psr-4'] ?? null);
    $namespace = array_key_first($psr4);

    return is_string($namespace) ? rtrim($namespace, '\\') : null;
}

function capell_docs_clean_sentence(string $text): string
{
    $text = capell_docs_normalize_copy($text);
    $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);

    return str_ends_with($text, '.') ? $text : $text . '.';
}

function capell_docs_finish_sentence(string $text): string
{
    return preg_match('/[.!?]$/', $text) === 1 ? $text : $text . '.';
}

function capell_docs_normalize_copy(string $text): string
{
    $replacements = [
        '—' => ' - ',
        '–' => '-',
        '“' => '"',
        '”' => '"',
        '‘' => "'",
        '’' => "'",
        '…' => '...',
    ];

    $text = strtr($text, $replacements);

    $bannedReplacements = [
        '/\bpowerful\b/i' => 'capable',
        '/\bseamless(?:ly)?\b/i' => 'connected',
        '/\bfuture-proof\b/i' => 'easier to change',
        '/\ball-in-one\b/i' => 'combined',
        '/\bgame-changing\b/i' => 'substantial',
        '/\bbest-in-class\b/i' => 'well-built',
        '/\bsupercharge\b/i' => 'improve',
        '/\bunlock(?:s|ed|ing)?\b/i' => 'add',
        '/\bcalm CMS assistant\b/i' => 'Capell assistant',
        '/\bserious CMS\b/i' => 'CMS',
        '/\bwithout the sprawl\b/i' => 'without extra application layers',
    ];

    foreach ($bannedReplacements as $pattern => $replacement) {
        $text = preg_replace($pattern, $replacement, $text) ?? $text;
    }

    return $text;
}

function capell_docs_string(mixed $value): ?string
{
    return is_string($value) && $value !== '' ? $value : null;
}

function capell_docs_array(mixed $value): array
{
    return is_array($value) ? $value : [];
}

/**
 * @return list<string>
 */
function capell_docs_string_list(mixed $value): array
{
    if (! is_array($value)) {
        return [];
    }

    $strings = [];

    foreach ($value as $item) {
        if (is_string($item) && $item !== '') {
            $strings[] = $item;
        }
    }

    return array_values($strings);
}

function capell_docs_title_from_slug(string $slug): string
{
    return implode(' ', array_map(
        static fn (string $word): string => ucfirst($word),
        explode('-', $slug),
    ));
}

/**
 * @param  list<string>  $items
 */
function capell_docs_sentence_list(array $items): string
{
    if ($items === []) {
        return '';
    }

    if (count($items) === 1) {
        return $items[0];
    }

    if (count($items) === 2) {
        return $items[0] . ' and ' . $items[1];
    }

    $lastItem = array_pop($items);

    return implode(', ', $items) . ', and ' . $lastItem;
}

/**
 * @return list<string>
 */
function capell_docs_provider_classes(string $packagePath, array $manifest): array
{
    $providers = [];
    $seenShortNames = [];
    $providerBuckets = capell_docs_array($manifest['providers'] ?? null);

    foreach ($providerBuckets as $bucket) {
        foreach (capell_docs_string_list($bucket) as $providerClass) {
            $seenShortNames[] = capell_docs_class_short_name($providerClass);
            $providers[] = $providerClass;
        }
    }

    foreach (capell_docs_class_files($packagePath . '/src/Providers') as $providerClass) {
        if (in_array($providerClass, $seenShortNames, true)) {
            continue;
        }

        $providers[] = $providerClass;
    }

    return array_values(array_unique($providers));
}

function capell_docs_class_short_name(string $class): string
{
    if (! str_contains($class, '\\')) {
        return $class;
    }

    return substr($class, strrpos($class, '\\') + 1);
}

/**
 * @return list<string>
 */
function capell_docs_manifest_commands(array $manifest): array
{
    $commands = [];
    $commandManifest = capell_docs_array($manifest['commands'] ?? null);

    foreach ($commandManifest as $command) {
        if (is_string($command) && $command !== '') {
            $commands[] = $command;
        }
    }

    sort($commands);

    return array_values(array_unique($commands));
}

/**
 * @return list<string>
 */
function capell_docs_manifest_contributions(array $manifest): array
{
    $contributions = [];

    foreach (capell_docs_array($manifest['contributes'] ?? null) as $contribution) {
        if (! is_array($contribution)) {
            continue;
        }

        $type = capell_docs_string($contribution['type'] ?? null);
        $class = capell_docs_string($contribution['class'] ?? null);

        if ($type === null && $class === null) {
            continue;
        }

        $contributions[] = $type !== null && $class !== null
            ? $type . ': ' . $class
            : (string) ($type ?? $class);
    }

    sort($contributions);

    return array_values(array_unique($contributions));
}

/**
 * @return list<string>
 */
function capell_docs_manifest_health_checks(array $manifest): array
{
    $healthChecks = [];

    foreach (capell_docs_array($manifest['healthChecks'] ?? null) as $healthCheck) {
        if (! is_array($healthCheck)) {
            continue;
        }

        $class = capell_docs_string($healthCheck['class'] ?? null);
        $key = capell_docs_string($healthCheck['key'] ?? null);

        if ($class !== null) {
            $healthChecks[] = $class;

            continue;
        }

        if ($key !== null) {
            $healthChecks[] = $key;
        }
    }

    sort($healthChecks);

    return array_values(array_unique($healthChecks));
}

/**
 * @return list<string>
 */
function capell_docs_path_list(string $directoryPath, string $filePattern): array
{
    if (! is_dir($directoryPath)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directoryPath, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $fileInfo) {
        if (! $fileInfo instanceof SplFileInfo || ! $fileInfo->isFile()) {
            continue;
        }

        if (fnmatch($filePattern, $fileInfo->getFilename())) {
            $files[] = $fileInfo->getPathname();
        }
    }

    sort($files);

    return $files;
}

/**
 * @return list<string>
 */
function capell_docs_route_files(string $packagePath): array
{
    return capell_docs_path_list($packagePath . '/routes', '*.php');
}

/**
 * @return list<string>
 */
function capell_docs_relative_files(string $rootPath, array $paths): array
{
    return array_map(static fn (string $path): string => capell_docs_relative_path($rootPath, $path), $paths);
}

function capell_docs_relative_path(string $rootPath, string $path): string
{
    return ltrim(str_replace('\\', '/', substr($path, strlen($rootPath))), '/');
}

/**
 * @return list<string>
 */
function capell_docs_class_files(string $directoryPath): array
{
    $classFiles = [];

    foreach (capell_docs_path_list($directoryPath, '*.php') as $path) {
        $classFiles[] = basename($path, '.php');
    }

    return $classFiles;
}

/**
 * @param  list<string>  $items
 * @return list<string>
 */
function capell_docs_limit(array $items, int $limit): array
{
    if (count($items) <= $limit) {
        return $items;
    }

    return [
        ...array_slice($items, 0, $limit),
        'and ' . (count($items) - $limit) . ' more',
    ];
}

/**
 * @param  list<string>  $items
 */
function capell_docs_add_fact_lines(array &$lines, string $label, array $items): void
{
    if ($items === []) {
        return;
    }

    $formattedItems = array_map(
        static fn (string $item): string => str_starts_with($item, '`') ? $item : '`' . $item . '`',
        $items,
    );

    $lines[] = '- ' . $label . ': ' . implode(', ', $formattedItems) . '.';
}
