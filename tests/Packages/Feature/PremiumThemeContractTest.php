<?php

declare(strict_types=1);

use Capell\ThemeStudio\Commerce\CommerceThemeServiceProvider;
use Capell\ThemeStudio\Education\EducationThemeServiceProvider;
use Capell\ThemeStudio\Healthcare\HealthcareThemeServiceProvider;
use Capell\ThemeStudio\Knowledge\KnowledgeThemeServiceProvider;
use Capell\ThemeStudio\LocalServices\LocalServicesThemeServiceProvider;
use Capell\ThemeStudio\Nonprofit\NonprofitThemeServiceProvider;
use Capell\ThemeStudio\Portfolio\PortfolioThemeServiceProvider;
use Capell\ThemeStudio\Saas\SaasThemeServiceProvider;

require_once __DIR__ . '/../Support/ThemeManifestContracts.php';

function premiumThemePackagePath(string $path): string
{
    return dirname(__DIR__, 3) . '/' . $path;
}

function premiumThemeKeyFromPath(string $path): string
{
    return str_replace('theme-', '', basename($path));
}

function premiumThemeShellClass(string $path): string
{
    $themeKey = premiumThemeKeyFromPath($path);

    return $themeKey === 'commerce' ? 'retail-shell' : $themeKey . '-shell';
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
    $preset = $providerClass::definition()->presets[0];

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

dataset('premium themes', [
    'commerce' => ['packages/theme-commerce', CommerceThemeServiceProvider::class, ['lookbook', 'promotion', 'buying-guide']],
    'education' => ['packages/theme-education', EducationThemeServiceProvider::class, ['pathway-comparison', 'outcomes', 'admissions-checklist']],
    'healthcare' => ['packages/theme-healthcare', HealthcareThemeServiceProvider::class, ['care-pathway', 'locations', 'insurance-trust']],
    'knowledge' => ['packages/theme-knowledge', KnowledgeThemeServiceProvider::class, ['reading-path', 'source-map', 'topic-index']],
    'local-services' => ['packages/theme-local-services', LocalServicesThemeServiceProvider::class, ['quote-estimator', 'service-packages', 'locality-proof']],
    'nonprofit' => ['packages/theme-nonprofit', NonprofitThemeServiceProvider::class, ['donation-impact', 'volunteer-shifts', 'annual-report-proof']],
    'portfolio' => ['packages/theme-portfolio', PortfolioThemeServiceProvider::class, ['case-study-detail', 'process', 'availability']],
    'saas' => ['packages/theme-saas', SaasThemeServiceProvider::class, ['pricing', 'docs-onboarding', 'demo-request']],
]);
