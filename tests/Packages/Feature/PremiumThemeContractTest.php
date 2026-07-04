<?php

declare(strict_types=1);

require_once __DIR__ . '/../Support/ThemeManifestContracts.php';

function premiumThemePackagePath(string $path): string
{
    return dirname(__DIR__, 3) . '/' . $path;
}

function premiumThemeKeyFromPath(string $path): string
{
    return str_replace('theme-', '', basename($path));
}

/**
 * Foundation's shared base wrapper class, carried by some child themes
 * alongside their own unique `<prefix>-shell` — not itself the prefix.
 */
const PREMIUM_THEME_SHARED_SHELL_CLASS = 'site-theme-shell';

function premiumThemeShellClass(string $path): string
{
    $themeDirectory = premiumThemePackagePath($path);
    $page = (string) file_get_contents($themeDirectory . '/resources/views/page.blade.php');

    preg_match_all('/\b([a-z][a-z0-9-]*-shell)\b/', $page, $matches);

    $shellClasses = array_values(array_unique(array_diff(
        $matches[1],
        [PREMIUM_THEME_SHARED_SHELL_CLASS],
    )));

    if (count($shellClasses) !== 1) {
        throw new RuntimeException(sprintf(
            'Expected exactly one unique <prefix>-shell class in %s/resources/views/page.blade.php, found: %s',
            $path,
            $shellClasses === [] ? 'none' : implode(', ', $shellClasses),
        ));
    }

    return $shellClasses[0];
}

it('treats every documented premium lane theme as a premium theme package', function (string $path, string $providerClass): void {
    $manifest = capell_json_file_array(premiumThemePackagePath($path) . '/capell.json');
    $definition = $providerClass::definition();
    $manifests = capell_theme_manifest_entries();
    $manifestsByName = capell_theme_manifests_by_name($manifests);

    expect($manifest['product']['group'])->toBe('Capell Themes')
        ->and($manifest['product']['tier'])->toBe('premium')
        ->and($manifest['product']['bundle'])->toBe('themes')
        ->and(capell_theme_manifest_definition_issues($providerClass, $manifest, $definition, $manifestsByName))->toBe([]);
})->with('premium themes');

it('keeps premium theme preset customization keys consistent', function (string $path, string $providerClass): void {
    $presets = $providerClass::definition()->presets;

    expect($presets)->not->toBeEmpty();

    foreach ($presets as $preset) {
        expect(array_keys($preset->values))->toContain(
            'primaryColor',
            'accentColor',
            'neutralColor',
            'surfaceColor',
            'foregroundColor',
            'headingFont',
            'bodyFont',
            'spacing',
            'cardStyle',
            'navigationStyle',
            'layoutPresentation',
            'motionIntensity',
            'mediaTreatment',
            'radius',
            'headingScale',
            'cardDensity',
        );
    }

    expect(count($presets))->toBeGreaterThanOrEqual(2);
})->with('premium themes');

it('declares the planned premium layout sections', function (string $path, string $providerClass, array $sections): void {
    expect($providerClass::definition()->includedSections)->toContain(...$sections);
})->with('premium themes');

it('defines every premium theme translation key referenced by Blade views', function (string $path): void {
    $themeDirectory = premiumThemePackagePath($path);
    $translationPath = $themeDirectory . '/resources/lang/en/generic.php';
    $viewsDirectory = $themeDirectory . '/resources/views';
    $translations = require $translationPath;
    $missingKeys = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($viewsDirectory, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($files as $fileInfo) {
        if (! $fileInfo instanceof SplFileInfo) {
            continue;
        }

        if ($fileInfo->getExtension() !== 'php') {
            continue;
        }

        $contents = (string) file_get_contents($fileInfo->getPathname());
        preg_match_all(
            "/__\\(\\s*['\"]capell-theme-[^:]+::generic\\.(\\w+)['\"]/",
            $contents,
            $matches,
        );

        foreach ($matches[1] as $translationKey) {
            if (! array_key_exists($translationKey, $translations)) {
                $missingKeys[] = $fileInfo->getPathname() . ':' . $translationKey;
            }
        }
    }

    expect($missingKeys)->toBe([]);
})->with('premium themes');

it('keeps premium theme stylesheets aligned with their public shell wrappers', function (string $path): void {
    $themeDirectory = premiumThemePackagePath($path);
    $themeKey = premiumThemeKeyFromPath($path);
    $shellClass = premiumThemeShellClass($path);
    $skipLinkClass = str_replace('-shell', '-skip-link', $shellClass);
    $page = (string) file_get_contents($themeDirectory . '/resources/views/page.blade.php');
    $css = (string) file_get_contents($themeDirectory . '/resources/css/theme-' . $themeKey . '.css');

    expect($page)
        ->toContain($shellClass)
        ->toContain($skipLinkClass)
        ->and($css)
        ->toContain('.' . $shellClass)
        ->toContain('.' . $skipLinkClass)
        ->toContain('focus-visible')
        ->not->toContain($themeKey . '-theme-shell');
})->with('premium themes');

dataset('premium themes', function (): array {
    $root = dirname(__DIR__, 3);
    $catalogue = capell_json_file_array($root . '/docs/themes.json');
    $manifestsByPath = capell_theme_manifest_entries($root);
    $cases = [];

    $themes = $catalogue['themes'] ?? [];

    if (! is_array($themes)) {
        throw new RuntimeException('docs/themes.json is missing a "themes" array.');
    }

    foreach ($themes as $theme) {
        if (! is_array($theme)) {
            throw new RuntimeException('docs/themes.json contains a theme entry that is not an object.');
        }

        if (($theme['tier'] ?? null) !== 'premium') {
            continue;
        }

        $themeKey = $theme['themeKey'] ?? null;

        if (! is_string($themeKey) || $themeKey === '') {
            throw new RuntimeException('A premium theme entry in docs/themes.json is missing its themeKey.');
        }

        $manifestRelativePath = "packages/theme-{$themeKey}/capell.json";
        $manifestEntry = $manifestsByPath[$manifestRelativePath] ?? null;

        if ($manifestEntry === null) {
            throw new RuntimeException("Premium theme [{$themeKey}] is missing a manifest at {$manifestRelativePath}.");
        }

        $providerClasses = capell_theme_manifest_provider_classes($manifestEntry['manifest']);
        $providerClass = $providerClasses[0] ?? null;

        if ($providerClass === null) {
            throw new RuntimeException("Premium theme [{$themeKey}] declares no runtime provider in its manifest.");
        }

        $customSections = $theme['customSections'] ?? [];

        $cases[$themeKey] = [
            "packages/theme-{$themeKey}",
            $providerClass,
            is_array($customSections) ? $customSections : [],
        ];
    }

    return $cases;
});
