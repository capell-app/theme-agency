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

it('keeps public Blade free of authoring or package metadata', function (): void {
    $blade = portfolioThemeBladeViews();

    expect($blade)
        ->not->toContain('capell-app/theme-portfolio')
        ->not->toContain('Filament')
        ->not->toContain('signed')
        ->not->toContain('wire:')
        ->not->toContain('data-field')
        ->not->toContain('data-model')
        ->not->toContain('permission');
});

it('keeps public Blade free of database query calls', function (): void {
    $blade = portfolioThemeBladeViews();

    expect($blade)
        ->not->toContain('::query(')
        ->not->toContain('DB::')
        ->not->toContain('loadMissing(')
        ->not->toContain('find(');
});
