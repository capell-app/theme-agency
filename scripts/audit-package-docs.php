<?php

declare(strict_types=1);

$rootPath = dirname(__DIR__);
$packagesPath = $rootPath . '/packages';
$failures = [];
$warnings = [];

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
    $names = [];

    foreach (scandir($packagesPath) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        if (is_dir($packagesPath . '/' . $entry)) {
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

function packageHasOperationalFailureMode(string $packagePath): bool
{
    foreach ([
        'config',
        'routes',
        'src/Console',
        'src/Jobs',
        'src/Health',
    ] as $relativePath) {
        if (is_dir($packagePath . '/' . $relativePath)) {
            return true;
        }
    }

    return false;
}

function checkTroubleshootingHeadings(string $rootPath, array &$warnings): void
{
    foreach (packageNames($rootPath . '/packages') as $packageName) {
        $packagePath = $rootPath . '/packages/' . $packageName;
        $readmePath = $packagePath . '/README.md';

        if (! packageHasOperationalFailureMode($packagePath) || ! is_file($readmePath)) {
            continue;
        }

        $contents = file_get_contents($readmePath) ?: '';

        if (! preg_match('/^#{2,3}\s+Troubleshooting\b/mi', $contents)) {
            addWarning($warnings, 'packages/' . $packageName . '/README.md has operational surfaces but no Troubleshooting heading');
        }
    }
}

$packageNames = packageNames($packagesPath);

foreach ($packageNames as $packageName) {
    $packagePath = $packagesPath . '/' . $packageName;

    foreach (['README.md', 'docs/README.md', 'docs/overview.md', 'capell.json'] as $relativeFile) {
        if (! is_file($packagePath . '/' . $relativeFile)) {
            addFailure($failures, 'packages/' . $packageName . '/' . $relativeFile . ' is missing');
        }
    }

    $capellManifestPath = $packagePath . '/capell.json';

    if (is_file($capellManifestPath)) {
        readJson('packages/' . $packageName . '/capell.json', $failures);
    }
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

    return EXIT_FAILURE;
}

fwrite(STDOUT, 'Package docs audit passed for ' . count($packageNames) . ' packages.');

if ($warnings !== []) {
    fwrite(STDOUT, ' Warnings: ' . count($warnings) . '.');
}

fwrite(STDOUT, PHP_EOL);
