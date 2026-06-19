<?php

declare(strict_types=1);

function creativeMarketplaceThemeBladeViews(): string
{
    $paths = array_values(array_unique(array_merge(
        glob(__DIR__ . '/../../resources/views/*.blade.php') ?: [],
        glob(__DIR__ . '/../../resources/views/**/*.blade.php') ?: [],
    )));

    return implode(PHP_EOL, array_map(static fn (string $path): string => file_get_contents($path) ?: '', $paths));
}

it('keeps public Blade free of authoring or package metadata', function (): void {
    $publicOutput = creativeMarketplaceThemeBladeViews();

    expect($publicOutput)
        ->not->toContain('capell-app/theme-creative-marketplace')
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
    expect(creativeMarketplaceThemeBladeViews())
        ->not->toContain('::query(')
        ->not->toContain('DB::')
        ->not->toContain('loadMissing(')
        ->not->toContain('relationLoaded(')
        ->not->toContain('Frontend::')
        ->not->toContain('find(');
});
