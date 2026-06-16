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
