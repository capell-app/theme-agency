<?php

declare(strict_types=1);

function knowledgeThemeBladeViews(): string
{
    $rootViews = glob(__DIR__ . '/../../resources/views/*.blade.php') ?: [];
    $sectionViews = glob(__DIR__ . '/../../resources/views/**/*.blade.php') ?: [];

    return implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        [...$rootViews, ...$sectionViews],
    ));
}

function knowledgeThemePublicOutputAssets(): string
{
    $translationFiles = glob(__DIR__ . '/../../resources/lang/en/*.php') ?: [];

    return knowledgeThemeBladeViews() . "\n" . implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        $translationFiles,
    ));
}

it('uses the premium page wrapper with brand tokens and skip link', function (): void {
    $blade = knowledgeThemeBladeViews();

    expect($blade)
        ->toContain('$brand->tokens()')
        ->toContain('skip_to_content')
        ->toContain('knowledge-shell');
});

it('keeps public Blade free of authoring or package metadata', function (): void {
    $publicOutput = knowledgeThemePublicOutputAssets();

    expect($publicOutput)
        ->not->toContain('capell-app/theme-knowledge')
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
    $blade = knowledgeThemeBladeViews();

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
