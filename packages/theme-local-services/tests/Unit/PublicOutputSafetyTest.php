<?php

declare(strict_types=1);

function localServicesThemeBladeViews(): string
{
    $rootViews = glob(__DIR__ . '/../../resources/views/*.blade.php') ?: [];
    $sectionViews = glob(__DIR__ . '/../../resources/views/**/*.blade.php') ?: [];

    return implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        [...$rootViews, ...$sectionViews],
    ));
}

function localServicesThemePublicOutputAssets(): string
{
    $translationFiles = glob(__DIR__ . '/../../resources/lang/en/*.php') ?: [];

    return localServicesThemeBladeViews() . "\n" . implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        $translationFiles,
    ));
}

it('uses the premium page wrapper with brand tokens and skip link', function (): void {
    $blade = localServicesThemeBladeViews();

    expect($blade)
        ->toContain('$brand->tokens()')
        ->toContain('skip_to_content')
        ->toContain('local-services-shell');
});

it('keeps public Blade free of authoring or package metadata', function (): void {
    $publicOutput = localServicesThemePublicOutputAssets();

    expect($publicOutput)
        ->not->toContain('capell-app/theme-local-services')
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
    $blade = localServicesThemeBladeViews();

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

it('keeps theme Tailwind classes unprefixed because package assets are isolated', function (): void {
    $provider = file_get_contents(__DIR__ . '/../../src/LocalServicesThemeServiceProvider.php') ?: '';
    $blade = localServicesThemeBladeViews();

    expect($provider)
        ->toContain("VendorAssetData::tailwindImport('resources/css/theme-local-services.css'")
        ->toContain("VendorAssetData::tailwindSource('resources/views/**/*.blade.php'")
        ->and($blade)->not->toContain('tw:');
});

it('ships a scoped dark-mode layer for the Local Services shell', function (): void {
    $css = file_get_contents(__DIR__ . '/../../resources/css/theme-local-services.css') ?: '';

    expect($css)
        ->toContain('.dark .local-services-shell')
        ->toContain('.local-services-shell.dark')
        ->toContain('@media (prefers-color-scheme: dark)')
        ->toContain('--local-services-card')
        ->toContain('--local-services-field')
        ->toContain('color-scheme: dark')
        ->toContain('.text-\\[\\#13231f\\]')
        ->toContain('.bg-\\[\\#f8fafc\\]')
        ->toContain('.border-\\[\\#99f6e4\\]');
});
