<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

it('keeps companion package source imports aligned with composer requirements', function (): void {
    $packages = companionDependencyBoundaryPackages();
    $violations = [];

    foreach ($packages as $packageName => $package) {
        $allowedPackageNames = [
            $packageName => true,
            ...array_fill_keys($package['requires'], true),
        ];

        $forbiddenPackages = array_filter(
            $packages,
            fn (string $candidatePackageName): bool => ! isset($allowedPackageNames[$candidatePackageName]),
            ARRAY_FILTER_USE_KEY,
        );

        $sourcePath = companionDependencyBoundaryRootPath() . '/' . $package['path'] . '/src';

        if (! is_dir($sourcePath)) {
            continue;
        }

        foreach ((new Finder)->files()->in($sourcePath)->name('*.php') as $file) {
            $contents = $file->getContents();
            $relativePath = str_replace(companionDependencyBoundaryRootPath() . '/', '', $file->getPathname());

            foreach ($forbiddenPackages as $forbiddenPackageName => $forbiddenPackage) {
                $pattern = '#(?<![A-Za-z0-9_\\\\])' . preg_quote($forbiddenPackage['namespace'], '#') . '\\\\#';

                if (preg_match($pattern, $contents) !== 1) {
                    continue;
                }

                $violations[] = sprintf(
                    '%s imports %s but %s does not require %s in composer.json.',
                    $relativePath,
                    $forbiddenPackage['namespace'],
                    $packageName,
                    $forbiddenPackageName,
                );
            }
        }
    }

    sort($violations);

    expect($violations)->toBe(
        [],
        'Companion package source may import another companion package only when composer.json requires it:' .
        PHP_EOL . json_encode($violations, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    );
});

/**
 * @return array<string, array{namespace: non-empty-string, path: non-empty-string, requires: list<string>}>
 */
function companionDependencyBoundaryPackages(): array
{
    $packages = [];

    foreach (companionDependencyBoundaryComposerFiles() as $composerPath) {
        $composer = json_decode((string) file_get_contents($composerPath), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($composer)) {
            continue;
        }

        $packageName = $composer['name'] ?? null;
        $autoload = $composer['autoload']['psr-4'] ?? null;
        if (! is_string($packageName)) {
            continue;
        }

        if (! is_array($autoload)) {
            continue;
        }

        $namespace = companionDependencyBoundarySourceNamespace($autoload);

        if ($namespace === null) {
            continue;
        }

        $packagePath = str_replace(companionDependencyBoundaryRootPath() . '/', '', dirname($composerPath));

        if ($packagePath === '') {
            continue;
        }

        $packages[$packageName] = [
            'namespace' => $namespace,
            'path' => $packagePath,
            'requires' => companionDependencyBoundaryRequiredPackageNames($composer['require'] ?? []),
        ];
    }

    ksort($packages);

    return $packages;
}

/**
 * @return list<string>
 */
function companionDependencyBoundaryComposerFiles(): array
{
    $files = (new Finder)
        ->files()
        ->in(companionDependencyBoundaryRootPath() . '/packages')
        ->depth('== 1')
        ->name('composer.json');

    $paths = [];

    foreach ($files as $file) {
        $paths[] = $file->getPathname();
    }

    sort($paths);

    return $paths;
}

/**
 * @param  array<string, mixed>  $autoload
 * @return non-empty-string|null
 */
function companionDependencyBoundarySourceNamespace(array $autoload): ?string
{
    foreach ($autoload as $namespace => $path) {
        if ($path !== 'src') {
            continue;
        }

        if (! is_string($namespace)) {
            continue;
        }

        $namespace = rtrim($namespace, '\\');

        return $namespace !== '' ? $namespace : null;
    }

    return null;
}

/**
 * @return list<string>
 */
function companionDependencyBoundaryRequiredPackageNames(mixed $requires): array
{
    if (! is_array($requires)) {
        return [];
    }

    $packageNames = [];

    foreach (array_keys($requires) as $packageName) {
        if (! is_string($packageName)) {
            continue;
        }

        if (! str_starts_with($packageName, 'capell-app/')) {
            continue;
        }

        if (in_array($packageName, companionDependencyBoundaryHostPackageNames(), true)) {
            continue;
        }

        $packageNames[] = $packageName;
    }

    sort($packageNames);

    return array_values(array_unique($packageNames));
}

/**
 * @return list<string>
 */
function companionDependencyBoundaryHostPackageNames(): array
{
    return [
        'capell-app/admin',
        'capell-app/core',
        'capell-app/frontend',
        'capell-app/installer',
        'capell-app/marketplace',
    ];
}

function companionDependencyBoundaryRootPath(): string
{
    return dirname(__DIR__, 3);
}
