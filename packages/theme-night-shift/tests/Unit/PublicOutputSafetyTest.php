<?php

declare(strict_types=1);

/*
 * The `sections/*.blade.php` views (still shipped, unchanged, as inert
 * preserved copy per the layout-native conversion's documented scope)
 * receive plain `render_data`-shaped arrays and must never touch live
 * Frontend::/DB/getMeta() APIs directly — that rule is asserted below.
 *
 * `header/index.blade.php`, `footer.blade.php`, and `widget/*.blade.php` are
 * new, conversion-introduced views that render through the *live*
 * layout-builder / frontend pipeline instead — exactly like
 * theme-foundation's own `components/header/index.blade.php`,
 * `components/footer/index.blade.php`, and
 * `components/widget/asset/features.blade.php` already do (calling
 * `Frontend::theme()`, `Frontend::site()`, `$widget->getMeta()`, etc. is the
 * normal, safe, public-facing contract for this class of view — it is not
 * an authoring/admin-internal leak, which is what this file's rules guard
 * against). They are intentionally excluded from the DB/live-API check
 * below; the "no authoring/package metadata" check still covers them.
 */
function nightShiftThemeBladeViews(): string
{
    $paths = array_values(array_unique(array_merge(
        glob(__DIR__ . '/../../resources/views/*.blade.php') ?: [],
        glob(__DIR__ . '/../../resources/views/**/*.blade.php') ?: [],
    )));

    return implode(PHP_EOL, array_map(static fn (string $path): string => file_get_contents($path) ?: '', $paths));
}

function nightShiftThemeLegacySectionBladeViews(): string
{
    $sectionViews = glob(__DIR__ . '/../../resources/views/sections/*.blade.php') ?: [];

    return implode(PHP_EOL, array_map(static fn (string $path): string => file_get_contents($path) ?: '', $sectionViews));
}

it('keeps public Blade free of authoring or package metadata', function (): void {
    $publicOutput = nightShiftThemeBladeViews();

    expect($publicOutput)
        ->not->toContain('capell-app/theme-night-shift')
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

it('keeps legacy section Blade free of database query calls', function (): void {
    $blade = nightShiftThemeLegacySectionBladeViews();

    expect($blade)
        ->not->toContain('::query(')
        ->not->toContain('DB::')
        ->not->toContain('loadMissing(')
        ->not->toContain('relationLoaded(')
        ->not->toContain('getMeta(')
        ->not->toContain('Frontend::')
        ->not->toContain('find(');
});
