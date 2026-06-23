<?php

declare(strict_types=1);

$rootPath = dirname(__DIR__);
$packagesPath = $rootPath . '/packages';
$failures = [];
$warnings = [];
$strictStructure = in_array('--strict-structure', $argv, true)
    || filter_var(getenv('CAPELL_DOCS_STRICT_STRUCTURE') ?: false, FILTER_VALIDATE_BOOL);

$tombstoneDocs = [
    'packages/address/docs/address-api.md',
    'packages/address/docs/address-database.md',
    'packages/blog/docs/blog-api.md',
    'packages/blog/docs/blog-database.md',
    'packages/blog/docs/media-attachment.md',
    'packages/campaign-studio/docs/campaign-studio-api.md',
    'packages/campaign-studio/docs/campaign-studio-database.md',
    'packages/migration-assistant/docs/migration-assistant.md',
    'packages/publishing-studio/docs/page-creation-and-approval-flow.md',
    'packages/publishing-studio/docs/page-drafts-and-publishing.md',
    'packages/publishing-studio/docs/publishing-studio-draftable-contract.md',
    'packages/publishing-studio/docs/publishing-studio.md',
    'packages/search/docs/search.md',
    'packages/seo-suite/docs/schema-templates.md',
    'packages/seo-suite/docs/search-console.md',
    'packages/seo-suite/docs/seo-intelligence.md',
    'packages/seo-suite/docs/seo-meta-and-discoverability.md',
    'packages/seo-suite/docs/sitemaps.md',
];

function packageNames(string $packagesPath): array
{
    $trackedManifestPaths = gitTrackedFiles(dirname($packagesPath), 'packages/*/capell.json');

    if ($trackedManifestPaths !== []) {
        $names = [];

        foreach ($trackedManifestPaths as $trackedManifestPath) {
            $pathParts = explode('/', $trackedManifestPath);

            if (($pathParts[0] ?? null) === 'packages' && isset($pathParts[1])) {
                $names[] = $pathParts[1];
            }
        }

        $names = array_values(array_unique($names));
        sort($names);

        return $names;
    }

    $names = [];

    foreach (scandir($packagesPath) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        if (is_dir($packagesPath . '/' . $entry) && is_file($packagesPath . '/' . $entry . '/capell.json')) {
            $names[] = $entry;
        }
    }

    sort($names);

    return $names;
}

function packageDirectoriesWithoutManifest(string $packagesPath): array
{
    $trackedPackagePaths = gitTrackedFiles(dirname($packagesPath), 'packages/*');

    if ($trackedPackagePaths !== []) {
        $packageNames = [];
        $manifestPackageNames = packageNames($packagesPath);

        foreach ($trackedPackagePaths as $trackedPackagePath) {
            $pathParts = explode('/', $trackedPackagePath);

            if (($pathParts[0] ?? null) === 'packages' && isset($pathParts[1])) {
                $packageNames[] = $pathParts[1];
            }
        }

        $names = array_values(array_diff(array_unique($packageNames), $manifestPackageNames));
        sort($names);

        return $names;
    }

    $names = [];

    foreach (scandir($packagesPath) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        if (! is_dir($packagesPath . '/' . $entry) || is_file($packagesPath . '/' . $entry . '/capell.json')) {
            continue;
        }

        $hasFiles = false;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($packagesPath . '/' . $entry, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo instanceof SplFileInfo && $fileInfo->isFile()) {
                $hasFiles = true;

                break;
            }
        }

        if ($hasFiles) {
            $names[] = $entry;
        }
    }

    sort($names);

    return $names;
}

function relativePath(string $rootPath, string $path): string
{
    $relativePath = str_replace('\\', '/', substr($path, strlen($rootPath) + 1));

    return $relativePath === false ? $path : $relativePath;
}

function addFailure(array &$failures, string $message): void
{
    $failures[] = $message;
}

function addWarning(array &$warnings, string $message): void
{
    $warnings[] = $message;
}

function readJson(string $path, array &$failures): ?array
{
    $contents = file_get_contents($path);

    if ($contents === false) {
        addFailure($failures, $path . ' could not be read');

        return null;
    }

    try {
        $decoded = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        addFailure($failures, $path . ' contains invalid JSON: ' . $exception->getMessage());

        return null;
    }

    if (! is_array($decoded)) {
        addFailure($failures, $path . ' must decode to a JSON object');

        return null;
    }

    return $decoded;
}

function markdownFiles(string $rootPath): array
{
    $trackedMarkdownPaths = gitTrackedFiles($rootPath, '*.md');

    if ($trackedMarkdownPaths !== []) {
        return array_map(
            static fn (string $trackedMarkdownPath): string => $rootPath . '/' . $trackedMarkdownPath,
            $trackedMarkdownPaths,
        );
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($rootPath, FilesystemIterator::SKIP_DOTS),
            static function (SplFileInfo $fileInfo): bool {
                if (! $fileInfo->isDir()) {
                    return true;
                }

                return ! in_array($fileInfo->getFilename(), [
                    '.git',
                    'node_modules',
                    'vendor',
                    'coverage',
                    'storage',
                    '.phpunit.cache',
                ], true);
            },
        ),
    );

    foreach ($iterator as $fileInfo) {
        if (! $fileInfo instanceof SplFileInfo || ! $fileInfo->isFile()) {
            continue;
        }

        if (strtolower($fileInfo->getExtension()) === 'md') {
            $files[] = $fileInfo->getPathname();
        }
    }

    sort($files);

    return $files;
}

/**
 * @return list<string>
 */
function gitTrackedFiles(string $rootPath, string $pathspec): array
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

function localMarkdownTargets(string $contents): array
{
    preg_match_all('/!?\[[^\]]*]\(([^)]+)\)/', $contents, $matches);

    return $matches[1] ?? [];
}

function isExternalLink(string $target): bool
{
    return preg_match('/^(?:https?:|mailto:|tel:|#)/i', $target) === 1;
}

function normalizeLocalTarget(string $target): string
{
    $target = trim($target);
    $target = trim($target, '<>');

    if (str_contains($target, ' "')) {
        $target = substr($target, 0, strpos($target, ' "'));
    }

    if (str_contains($target, " '")) {
        $target = substr($target, 0, strpos($target, " '"));
    }

    $target = preg_replace('/[?#].*$/', '', $target) ?? $target;

    return rawurldecode($target);
}

function resolvedPath(string $fromFile, string $target): string
{
    if (str_starts_with($target, '/')) {
        return $target;
    }

    return dirname($fromFile) . '/' . $target;
}

function canonicalPath(string $path): string
{
    $parts = [];

    foreach (explode('/', str_replace('\\', '/', $path)) as $part) {
        if ($part === '' || $part === '.') {
            continue;
        }

        if ($part === '..') {
            array_pop($parts);

            continue;
        }

        $parts[] = $part;
    }

    return (str_starts_with($path, '/') ? '/' : '') . implode('/', $parts);
}

function checkLocalMarkdownLinks(string $rootPath, array &$failures): void
{
    foreach (markdownFiles($rootPath) as $filePath) {
        $contents = file_get_contents($filePath);

        if ($contents === false) {
            addFailure($failures, relativePath($rootPath, $filePath) . ' could not be read');

            continue;
        }

        foreach (localMarkdownTargets($contents) as $rawTarget) {
            if (isExternalLink($rawTarget)) {
                continue;
            }

            $target = normalizeLocalTarget($rawTarget);

            if ($target === '' || str_starts_with($target, '#')) {
                continue;
            }

            $path = canonicalPath(resolvedPath($filePath, $target));

            if (! file_exists($path)) {
                addFailure(
                    $failures,
                    relativePath($rootPath, $filePath) . ' links to missing local target ' . $rawTarget,
                );
            }
        }
    }
}

function checkActiveTombstoneLinks(string $rootPath, array $tombstoneDocs, array &$failures): void
{
    $tombstoneSet = array_fill_keys($tombstoneDocs, true);
    $activeNavFiles = [];

    foreach (packageNames($rootPath . '/packages') as $packageName) {
        foreach (['README.md', 'docs/README.md'] as $relativeFile) {
            $path = $rootPath . '/packages/' . $packageName . '/' . $relativeFile;

            if (is_file($path)) {
                $activeNavFiles[] = $path;
            }
        }
    }

    foreach ($activeNavFiles as $filePath) {
        $contents = file_get_contents($filePath);

        if ($contents === false) {
            continue;
        }

        foreach (localMarkdownTargets($contents) as $rawTarget) {
            if (isExternalLink($rawTarget)) {
                continue;
            }

            $target = normalizeLocalTarget($rawTarget);
            $resolvedTarget = relativePath(
                $rootPath,
                canonicalPath(resolvedPath($filePath, $target)),
            );

            if (isset($tombstoneSet[$resolvedTarget])) {
                addFailure(
                    $failures,
                    relativePath($rootPath, $filePath) . ' actively links to consolidated tombstone ' . $resolvedTarget,
                );
            }
        }
    }
}

function checkScreenshotOutputs(string $rootPath, array &$failures, array &$warnings): void
{
    foreach (packageNames($rootPath . '/packages') as $packageName) {
        $screenshotsPath = $rootPath . '/packages/' . $packageName . '/docs/screenshots.json';

        if (! is_file($screenshotsPath)) {
            continue;
        }

        $manifest = readJson(relativePath($rootPath, $screenshotsPath), $failures);

        if ($manifest === null) {
            continue;
        }

        $entries = $manifest['entries'] ?? [];

        if (! is_array($entries)) {
            addFailure($failures, relativePath($rootPath, $screenshotsPath) . ' entries must be an array');

            continue;
        }

        foreach ($entries as $entryIndex => $entry) {
            if (! is_array($entry)) {
                addFailure($failures, relativePath($rootPath, $screenshotsPath) . ' entries[' . $entryIndex . '] must be an object');

                continue;
            }

            foreach (['screenshotPath', 'darkScreenshotPath'] as $field) {
                $screenshotPath = $entry[$field] ?? null;

                if (! is_string($screenshotPath) || $screenshotPath === '') {
                    continue;
                }

                $absoluteScreenshotPath = $rootPath . '/' . $screenshotPath;

                if (is_file($absoluteScreenshotPath)) {
                    continue;
                }

                $message = relativePath($rootPath, $screenshotsPath) . ' entries[' . $entryIndex . '].' . $field . ' points to missing output ' . $screenshotPath;

                if (($entry['required'] ?? true) === false) {
                    addWarning($warnings, $message);

                    continue;
                }

                addFailure($failures, $message);
            }
        }
    }
}

function packageHasOperationalFailureMode(string $packageName, string $packagePath): bool
{
    $packagesWithOperationalFailureModes = [
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

    if (in_array($packageName, $packagesWithOperationalFailureModes, true)) {
        return true;
    }

    foreach (['config', 'routes', 'src/Console', 'src/Jobs', 'src/Health'] as $relativePath) {
        if (is_dir($packagePath . '/' . $relativePath)) {
            return true;
        }
    }

    $capellManifestPath = $packagePath . '/capell.json';

    if (! is_file($capellManifestPath)) {
        return false;
    }

    $manifest = json_decode(file_get_contents($capellManifestPath) ?: '[]', true);

    if (! is_array($manifest)) {
        return false;
    }

    foreach (($manifest['commands'] ?? []) as $command) {
        if (is_string($command) && $command !== '') {
            return true;
        }
    }

    return false;
}

function checkTroubleshootingHeadings(string $rootPath, array &$warnings): void
{
    foreach (packageNames($rootPath . '/packages') as $packageName) {
        $packagePath = $rootPath . '/packages/' . $packageName;

        if (! packageHasOperationalFailureMode($packageName, $packagePath)) {
            continue;
        }

        // When a package ships an admin-first overview, its editor troubleshooting moves to
        // docs/admin-guide.md, so check there instead of the trimmed overview.
        $overviewFile = is_file($packagePath . '/docs/overview.admin.md')
            ? 'docs/admin-guide.md'
            : 'docs/overview.md';

        foreach (['README.md', $overviewFile] as $relativeFile) {
            $readmePath = $packagePath . '/' . $relativeFile;

            if (! is_file($readmePath)) {
                continue;
            }

            $contents = file_get_contents($readmePath) ?: '';

            // Tier 2 admin guides use the documented operator shape ("What to do when...")
            // instead of a Troubleshooting heading; accept either in docs/admin-guide.md.
            $pattern = $relativeFile === 'docs/admin-guide.md'
                ? '/^#{2,3}\s+(Troubleshooting\b|What to do when)/mi'
                : '/^#{2,3}\s+Troubleshooting\b/mi';

            if (! preg_match($pattern, $contents)) {
                addWarning($warnings, 'packages/' . $packageName . '/' . $relativeFile . ' has operational surfaces but no Troubleshooting heading');
            }
        }
    }
}

function markdownHeadings(string $contents): array
{
    preg_match_all('/^#{2}\s+(.+)$/m', $contents, $matches);

    return array_map(
        static fn (string $heading): string => trim($heading),
        $matches[1] ?? [],
    );
}

function checkRequiredMarkdownStructure(string $rootPath, string $packageName, string $relativeFile, array &$warnings, array &$failures, bool $strictStructure): void
{
    $readmePath = $rootPath . '/packages/' . $packageName . '/' . $relativeFile;

    if (! is_file($readmePath)) {
        return;
    }

    $contents = file_get_contents($readmePath) ?: '';
    $headings = markdownHeadings($contents);
    $requiredHeadings = [
        'What This Plugin Adds',
        'Why It Matters',
        'Screens And Workflow',
        'Technical Shape',
        'Data Model',
        'Install Impact',
        'Common Pitfalls',
        'Quick Start',
        'Next Steps',
    ];

    foreach ($requiredHeadings as $requiredHeading) {
        if (in_array($requiredHeading, $headings, true)) {
            continue;
        }

        $message = 'packages/' . $packageName . '/' . $relativeFile . ' is missing required heading "' . $requiredHeading . '"';

        if ($strictStructure) {
            addFailure($failures, $message);

            continue;
        }

        addWarning($warnings, $message);
    }

    if (! preg_match('/^#\s+.+/m', $contents)) {
        $message = 'packages/' . $packageName . '/' . $relativeFile . ' is missing an H1 title';

        if ($strictStructure) {
            addFailure($failures, $message);

            return;
        }

        addWarning($warnings, $message);
    }
}

/**
 * Validate an admin-first docs/overview.md (a package that ships docs/overview.admin.md).
 *
 * The admin overview must read for non-technical editors: it must keep an H1, must not carry
 * the developer-only sections (those live in README.md), and must not show install commands or
 * fenced code on the first screen. Per-tier section names are intentionally not pinned here so
 * full-guide, theme, and behind-the-scenes overviews can each use their own plain headings.
 */
function checkAdminOverviewStructure(string $rootPath, string $packageName, array &$warnings, array &$failures, bool $strictStructure): void
{
    $relativeFile = 'docs/overview.md';
    $readmePath = $rootPath . '/packages/' . $packageName . '/' . $relativeFile;

    if (! is_file($readmePath)) {
        return;
    }

    $contents = file_get_contents($readmePath) ?: '';

    if (! preg_match('/^#\s+.+/m', $contents)) {
        addFailure($failures, 'packages/' . $packageName . '/' . $relativeFile . ' is missing an H1 title');
    }

    $developerOnlyHeadings = [
        'What This Plugin Adds',
        'Technical Shape',
        'Data Model',
        'Install Impact',
        'Quick Start',
    ];

    foreach (markdownHeadings($contents) as $heading) {
        if (in_array($heading, $developerOnlyHeadings, true)) {
            $message = 'packages/' . $packageName . '/' . $relativeFile . ' is admin-first but still carries developer heading "' . $heading . '" (keep it in README.md)';

            $strictStructure ? addFailure($failures, $message) : addWarning($warnings, $message);
        }
    }

    if (preg_match('/\bcomposer require\b/i', $contents) === 1 || preg_match('/\bphp artisan\b/i', $contents) === 1) {
        addFailure($failures, 'packages/' . $packageName . '/' . $relativeFile . ' is admin-first but mentions an install command (composer/artisan); keep those in README.md');
    }
}

function checkReadmeVoiceRules(string $rootPath, string $packageName, array &$failures): void
{
    checkPackageMarkdownVoiceRules($rootPath, $packageName, 'README.md', $failures);
    checkPackageMarkdownVoiceRules($rootPath, $packageName, 'docs/overview.md', $failures);
}

function checkPackageMarkdownVoiceRules(string $rootPath, string $packageName, string $relativeFile, array &$failures): void
{
    $readmePath = $rootPath . '/packages/' . $packageName . '/' . $relativeFile;

    if (! is_file($readmePath)) {
        return;
    }

    $contents = file_get_contents($readmePath) ?: '';
    $bannedTerms = [
        'powerful',
        'seamless',
        'future-proof',
        'all-in-one',
        'game-changing',
        'best-in-class',
        'supercharge',
        'unlock',
        'calm CMS assistant',
        'serious CMS',
        'without the sprawl',
    ];

    foreach ($bannedTerms as $bannedTerm) {
        $pattern = '/\b' . preg_quote($bannedTerm, '/') . '(?:s|ed|ing)?\b/i';

        if (preg_match($pattern, $contents) === 1) {
            addFailure(
                $failures,
                'packages/' . $packageName . '/' . $relativeFile . ' uses banned docs term "' . $bannedTerm . '"',
            );
        }
    }

    if (preg_match('/[—–“”‘’]/u', $contents) === 1) {
        addFailure(
            $failures,
            'packages/' . $packageName . '/' . $relativeFile . ' uses non-ASCII punctuation that should be normalized',
        );
    }

    if (preg_match('/^```/m', $contents) === 1) {
        addFailure(
            $failures,
            'packages/' . $packageName . '/' . $relativeFile . ' contains fenced code blocks; use inline snippets unless essential',
        );
    }
}

function markdownSection(string $contents, string $heading): string
{
    $pattern = '/^##\s+' . preg_quote($heading, '/') . '\s*$\n(?P<body>.*?)(?=^##\s+|\z)/ms';

    if (preg_match($pattern, $contents, $matches) !== 1) {
        return '';
    }

    return trim((string) ($matches['body'] ?? ''));
}

function checkMarkdownOutputRules(string $rootPath, string $packageName, string $relativeFile, ?array $manifest, array &$failures): void
{
    $readmePath = $rootPath . '/packages/' . $packageName . '/' . $relativeFile;

    if (! is_file($readmePath)) {
        return;
    }

    $contents = file_get_contents($readmePath) ?: '';
    $displayName = is_array($manifest) && is_string($manifest['displayName'] ?? null)
        ? $manifest['displayName']
        : null;

    if ($displayName !== null && preg_match('/^#\s+(.+)$/m', $contents, $matches) === 1) {
        $actualTitle = trim((string) ($matches[1] ?? ''));

        if ($actualTitle !== $displayName) {
            addFailure(
                $failures,
                'packages/' . $packageName . '/' . $relativeFile . ' title "' . $actualTitle . '" does not match displayName "' . $displayName . '"',
            );
        }
    }

    $overview = markdownSection($contents, 'What This Plugin Adds');
    $wordCount = str_word_count(strip_tags($overview));

    if ($wordCount > 500) {
        addFailure(
            $failures,
            'packages/' . $packageName . '/' . $relativeFile . ' What This Plugin Adds is over 500 words',
        );
    }
}

$packageNames = packageNames($packagesPath);

foreach (packageDirectoriesWithoutManifest($packagesPath) as $packageDirectory) {
    addWarning($warnings, 'packages/' . $packageDirectory . ' has no capell.json and is skipped by the package docs audit');
}

foreach ($packageNames as $packageName) {
    $packagePath = $packagesPath . '/' . $packageName;
    $manifest = null;

    foreach (['README.md', 'docs/README.md', 'docs/overview.md', 'capell.json'] as $relativeFile) {
        if (! is_file($packagePath . '/' . $relativeFile)) {
            addFailure($failures, 'packages/' . $packageName . '/' . $relativeFile . ' is missing');
        }
    }

    $capellManifestPath = $packagePath . '/capell.json';

    if (is_file($capellManifestPath)) {
        $manifest = readJson('packages/' . $packageName . '/capell.json', $failures);
    }

    checkRequiredMarkdownStructure($rootPath, $packageName, 'README.md', $warnings, $failures, $strictStructure);

    if (is_file($packagePath . '/docs/overview.admin.md')) {
        checkAdminOverviewStructure($rootPath, $packageName, $warnings, $failures, $strictStructure);
    } else {
        checkRequiredMarkdownStructure($rootPath, $packageName, 'docs/overview.md', $warnings, $failures, $strictStructure);
    }
    checkReadmeVoiceRules($rootPath, $packageName, $failures);
    checkMarkdownOutputRules($rootPath, $packageName, 'README.md', $manifest, $failures);
    checkMarkdownOutputRules($rootPath, $packageName, 'docs/overview.md', $manifest, $failures);
}

$rootReadme = file_get_contents($rootPath . '/README.md') ?: '';
$docsReadme = file_get_contents($rootPath . '/docs/README.md') ?: '';

foreach ($packageNames as $packageName) {
    if (! str_contains($rootReadme, 'packages/' . $packageName . '/README.md')) {
        addFailure($failures, 'README.md package index is missing ' . $packageName);
    }

    if (! str_contains($docsReadme, '../packages/' . $packageName . '/README.md')) {
        addFailure($failures, 'docs/README.md package reference is missing ' . $packageName . ' README');
    }

    if (! str_contains($docsReadme, '../packages/' . $packageName . '/docs/overview.md')) {
        addFailure($failures, 'docs/README.md package reference is missing ' . $packageName . ' overview');
    }
}

checkLocalMarkdownLinks($rootPath, $failures);
checkActiveTombstoneLinks($rootPath, $tombstoneDocs, $failures);
checkScreenshotOutputs($rootPath, $failures, $warnings);
checkTroubleshootingHeadings($rootPath, $warnings);

foreach ($warnings as $warning) {
    fwrite(STDERR, '[warn] ' . $warning . PHP_EOL);
}

if ($failures !== []) {
    fwrite(STDERR, 'Package docs audit failed:' . PHP_EOL);

    foreach ($failures as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }

    exit(1);
}

fwrite(STDOUT, 'Package docs audit passed for ' . count($packageNames) . ' packages.');

if ($warnings !== []) {
    fwrite(STDOUT, ' Warnings: ' . count($warnings) . '.');
}

fwrite(STDOUT, PHP_EOL);

exit(0);
