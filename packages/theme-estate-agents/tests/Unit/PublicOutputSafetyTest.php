<?php

declare(strict_types=1);

function estateAgentsThemeBladeViews(): string
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

function estateAgentsThemePublicOutputAssets(): string
{
    $translationFiles = glob(__DIR__ . '/../../resources/lang/en/*.php') ?: [];

    return estateAgentsThemeBladeViews() . "\n" . implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        $translationFiles,
    ));
}

function estateAgentsThemeCss(): string
{
    return file_get_contents(__DIR__ . '/../../resources/css/theme-estate-agents.css') ?: '';
}

it('keeps estate agents public Blade free of authoring or package metadata', function (): void {
    $publicOutput = estateAgentsThemePublicOutputAssets();

    expect($publicOutput)
        ->not->toContain('capell-app/theme-estate-agents')
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

it('keeps estate agents public Blade free of database query calls', function (): void {
    $blade = estateAgentsThemeBladeViews();

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
        ->not->toContain('->translation')
        ->not->toContain('->assets')
        ->not->toContain('->media->')
        ->not->toContain('find(');
});

it('renders a working skip link target in the public page wrapper', function (): void {
    $pageView = file_get_contents(__DIR__ . '/../../resources/views/page.blade.php') ?: '';

    expect($pageView)
        ->toContain('href="#main-content"')
        ->toContain('id="main-content"')
        ->toContain('<main');
});

it('keeps estate agents public Blade free of dead form actions', function (): void {
    $blade = estateAgentsThemeBladeViews();

    expect($blade)
        ->not->toContain('action="#"')
        ->not->toContain('href="#"')
        ->not->toContain('javascript:')
        ->not->toContain('signed')
        ->not->toContain('wire:');
});

it('keeps estate agents CSS tied to theme tokens with dark and mobile proof hooks', function (): void {
    $css = estateAgentsThemeCss();

    expect($css)
        ->toContain('--estate-ink: var(--theme-foreground')
        ->toContain('--estate-green: var(--theme-primary')
        ->toContain('--estate-lime: var(--theme-accent')
        ->toContain('--estate-surface: var(--theme-surface')
        ->toContain('--estate-paper: var(--theme-paper')
        ->toContain('color-mix(in srgb, var(--estate-lime) 90%, transparent)')
        ->toContain('.estate-dark-card')
        ->toContain('color-mix(in srgb, var(--estate-paper) 8%, transparent)')
        ->toContain('@media (max-width: 48rem)')
        ->toContain('grid-template-columns: 1fr')
        ->not->toContain('background: #ffffff')
        ->not->toContain('color: #ffffff');
});
