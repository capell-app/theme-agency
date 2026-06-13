<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

/**
 * Extension points must be wired through typed contracts and registrar methods,
 * not hand-written container tag strings. A raw dotted tag literal such as
 * `app()->tag([$class], 'capell.admin.user-form-extender')` bypasses the
 * contract that owns the tag and silently rots when the constant changes.
 *
 * Reference the owning contract constant instead, e.g.
 * `UserFormExtender::TAG`, or route through an AdminBridgeRegistrar /
 * PackageSurfaceRegistrar method.
 */
it('bans raw string extension tags in package source', function (): void {
    $violations = [];

    foreach (rawStringExtensionTagSourceFiles() as $relativePath => $contents) {
        foreach (rawStringExtensionTagMatches($contents) as $line => $literal) {
            $violations[] = sprintf('%s:%d uses raw extension tag %s', $relativePath, $line, $literal);
        }
    }

    sort($violations);

    expect($violations)->toBe(
        [],
        'Container ->tag() calls must reference an owning contract ::TAG constant, never a raw dotted string literal:'
        . PHP_EOL . json_encode($violations, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    );
});

/**
 * @return array<string, string> relative path => file contents
 */
function rawStringExtensionTagSourceFiles(): array
{
    $rootPath = dirname(__DIR__, 3);
    $files = [];

    foreach ((new Finder)->files()->in($rootPath . '/packages')->path('/src/')->name('*.php') as $file) {
        $files[str_replace($rootPath . '/', '', $file->getPathname())] = $file->getContents();
    }

    ksort($files);

    return $files;
}

/**
 * Returns container tag literals keyed by 1-based line number. A literal is a
 * dotted/namespaced lowercase string (the Capell extension-tag shape) passed to
 * a singular `->tag(` call — the container tagging API. Plural `->tags(` (cache,
 * queue) and constant references such as `Contract::TAG` are intentionally allowed.
 *
 * @return array<int, string>
 */
function rawStringExtensionTagMatches(string $contents): array
{
    $matches = [];

    foreach (preg_split('/\R/', $contents) ?: [] as $index => $line) {
        if (! preg_match('/->tag\(/', $line)) {
            continue;
        }

        if (preg_match('/->tag\([^)]*([\'"])(?<literal>[a-z][a-z0-9]*(?:[._-][a-z0-9]+)+)\1/', $line, $found) !== 1) {
            continue;
        }

        $matches[$index + 1] = $found['literal'];
    }

    return $matches;
}
