<?php

declare(strict_types=1);

function portfolioThemeBladeViews(): string
{
    $viewRoot = __DIR__ . '/../../resources/views';
    $paths = array_values(array_unique(array_merge(
        glob($viewRoot . '/*.blade.php') ?: [],
        glob($viewRoot . '/**/*.blade.php') ?: [],
    )));

    return implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        $paths,
    ));
}

function portfolioThemePublicOutputAssets(): string
{
    $translationFiles = glob(__DIR__ . '/../../resources/lang/en/*.php') ?: [];

    return portfolioThemeBladeViews() . "\n" . implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        $translationFiles,
    ));
}

/**
 * @param  list<string>  $sectionNames
 */
function portfolioThemeSectionBladeViews(array $sectionNames): string
{
    return implode("\n", array_map(
        static fn (string $sectionName): string => file_get_contents(__DIR__ . '/../../resources/views/sections/' . $sectionName . '.blade.php') ?: '',
        $sectionNames,
    ));
}

it('uses the premium page wrapper with brand tokens and skip link', function (): void {
    $blade = portfolioThemeBladeViews();

    expect($blade)
        ->toContain('$brand->tokens()')
        ->toContain('skip_to_content')
        ->toContain('portfolio-shell');
});

it('keeps public Blade free of authoring or package metadata', function (): void {
    $publicOutput = portfolioThemePublicOutputAssets();

    expect($publicOutput)
        ->not->toContain('capell-app/theme-portfolio')
        ->not->toContain('authoring')
        ->not->toContain('data-theme-key')
        ->not->toContain('Filament')
        ->not->toContain('Livewire')
        ->not->toContain('signed')
        ->not->toContain('wire:')
        ->not->toContain('data-field')
        ->not->toContain('data-model')
        ->not->toContain('field_path')
        ->not->toContain('model_id')
        ->not->toContain('permission');
});

it('keeps public Blade free of database query calls', function (): void {
    $blade = portfolioThemeBladeViews();

    expect($blade)
        ->not->toContain('::query(')
        ->not->toContain('DB::')
        ->not->toContain('loadMissing(')
        ->not->toContain('relationLoaded(')
        ->not->toContain('getMeta(')
        ->not->toContain('Frontend::')
        ->not->toContain('PageLoader::')
        ->not->toContain('SiteLoader::')
        ->not->toContain('NavigationLoader::')
        ->not->toContain('CapellCore::isPackageInstalled')
        ->not->toContain('Route::getRoutes()')
        ->not->toContain('refreshNameLookups')
        ->not->toContain('->translation')
        ->not->toContain('->assets')
        ->not->toContain('->media->')
        ->not->toContain('find(');
});

it('keeps translated service newsletter and stat copy out of Blade literals', function (): void {
    $blade = portfolioThemeSectionBladeViews(['services', 'newsletter', 'hero', 'work-grid']);

    expect($blade)
        ->not->toContain('What we build')
        ->not->toContain('A modular services layer built for portfolio storytelling')
        ->not->toContain('SERVICE')
        ->not->toContain('DISCOVERY')
        ->not->toContain('Brand systems')
        ->not->toContain('DESIGN')
        ->not->toContain('Conversion storytelling')
        ->not->toContain('LAUNCH')
        ->not->toContain('Performance tune-up')
        ->not->toContain('Connect a newsletter form action to capture subscribers.')
        ->not->toContain('Newsletter signup')
        ->not->toContain('+42%')
        ->not->toContain('120+')
        ->not->toContain('6h')
        ->not->toContain('30+ Projects')
        ->not->toContain('12+ Industries')
        ->not->toContain('97% Retention');
});

it('ships a scoped dark theme layer without public package detection in Blade', function (): void {
    $css = file_get_contents(__DIR__ . '/../../resources/css/theme-portfolio.css') ?: '';
    $provider = file_get_contents(__DIR__ . '/../../src/PortfolioThemeServiceProvider.php') ?: '';
    $blade = portfolioThemeBladeViews();

    expect($provider)
        ->toContain("key: 'portfolio-dark'")
        ->toContain("'colorScheme' => 'dark'")
        ->and($css)->toContain('.dark .portfolio-shell')
        ->and($css)->toContain('--portfolio-card')
        ->and($css)->toContain('--portfolio-border-strong')
        ->and($blade)->not->toContain('CapellCore::isPackageInstalled');
});

it('keeps section palette colours behind Portfolio token classes', function (): void {
    $blade = portfolioThemeBladeViews();
    $css = file_get_contents(__DIR__ . '/../../resources/css/theme-portfolio.css') ?: '';

    expect($blade)
        ->toContain('portfolio-bg-card-soft')
        ->toContain('portfolio-bg-deep')
        ->toContain('portfolio-text-ink')
        ->toContain('portfolio-text-highlight')
        ->not->toMatch('/#[0-9a-fA-F]{6}\b/')
        ->and($css)->toContain('--portfolio-highlight')
        ->and($css)->toContain('--portfolio-secondary')
        ->and($css)->toContain('.portfolio-hover-border-secondary:hover');
});
