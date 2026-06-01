<?php

declare(strict_types=1);

function healthcareThemeBladeViews(): string
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

function healthcareThemePublicOutputAssets(): string
{
    $translationFiles = glob(__DIR__ . '/../../resources/lang/en/*.php') ?: [];

    return healthcareThemeBladeViews() . "\n" . implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        $translationFiles,
    ));
}

it('keeps public Blade free of authoring or package metadata', function (): void {
    $publicOutput = healthcareThemePublicOutputAssets();

    expect($publicOutput)
        ->not->toContain('capell-app/theme-healthcare')
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
    $blade = healthcareThemeBladeViews();

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
