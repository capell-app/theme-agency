<?php

declare(strict_types=1);

/*
 * The `sections/*.blade.php` views (still shipped, unchanged, per C2's
 * scope) receive fully pre-resolved DTOs (HeroSectionData, etc.) and must
 * never touch live Frontend::/DB/getMeta() APIs directly — that rule is
 * asserted below.
 *
 * `header/index.blade.php`, `footer.blade.php`, and `widget/*.blade.php` are
 * new, C2-introduced views that render through the *live* layout-builder /
 * frontend pipeline instead — exactly like theme-foundation's own
 * `components/header/index.blade.php`, `components/footer/index.blade.php`,
 * and `components/widget/asset/features.blade.php` already do (calling
 * `Frontend::theme()`, `Frontend::site()`, `$widget->getMeta()`, etc. is the
 * normal, safe, public-facing contract for this class of view — it is not
 * an authoring/admin-internal leak, which is what this file's rules guard
 * against). They are intentionally excluded from the DB/live-API check
 * below; the "no authoring/package metadata" and "no inline scripts" checks
 * still cover them.
 */
function liquidGlassThemeBladeViews(): string
{
    $rootViews = glob(__DIR__ . '/../../resources/views/*.blade.php') ?: [];
    $sectionViews = glob(__DIR__ . '/../../resources/views/**/*.blade.php') ?: [];

    return implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        [...$rootViews, ...$sectionViews],
    ));
}

function liquidGlassThemeLegacySectionBladeViews(): string
{
    $sectionViews = glob(__DIR__ . '/../../resources/views/sections/*.blade.php') ?: [];

    return implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        $sectionViews,
    ));
}

function liquidGlassThemePublicOutputAssets(): string
{
    $translationFiles = glob(__DIR__ . '/../../resources/lang/en/*.php') ?: [];

    return liquidGlassThemeBladeViews() . "\n" . implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        $translationFiles,
    ));
}

it('keeps public Blade free of authoring or package metadata', function (): void {
    $publicOutput = liquidGlassThemePublicOutputAssets();

    expect($publicOutput)
        ->not->toContain('capell-app/theme-liquid-glass')
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

it('keeps public Blade free of inline scripts', function (): void {
    $blade = liquidGlassThemeBladeViews();

    expect($blade)
        ->not->toContain('<script')
        ->not->toContain('</script>');
});

it('keeps legacy section Blade free of database query calls', function (): void {
    $blade = liquidGlassThemeLegacySectionBladeViews();

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
