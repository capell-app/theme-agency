<?php

declare(strict_types=1);

function agencyThemeBladeViews(): string
{
    $rootViews = glob(__DIR__ . '/../../resources/views/*.blade.php') ?: [];
    $sectionViews = glob(__DIR__ . '/../../resources/views/**/*.blade.php') ?: [];

    return implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        [...$rootViews, ...$sectionViews],
    ));
}

it('keeps public Blade free of authoring or package metadata', function (): void {
    $blade = agencyThemeBladeViews();

    expect($blade)
        ->not->toContain('capell-app/theme-agency')
        ->not->toContain('Filament')
        ->not->toContain('signed')
        ->not->toContain('wire:')
        ->not->toContain('data-field')
        ->not->toContain('data-model')
        ->not->toContain('permission');
});

it('keeps public Blade free of database query calls', function (): void {
    $blade = agencyThemeBladeViews();

    expect($blade)
        ->not->toContain('::query(')
        ->not->toContain('DB::')
        ->not->toContain('loadMissing(')
        ->not->toContain('find(');
});
