<?php

declare(strict_types=1);

use Capell\ThemeStudio\Nonprofit\NonprofitThemeServiceProvider;

function nonprofitThemeBladeViews(): string
{
    $rootViews = glob(__DIR__ . '/../../resources/views/*.blade.php') ?: [];
    $sectionViews = glob(__DIR__ . '/../../resources/views/**/*.blade.php') ?: [];

    return implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        [...$rootViews, ...$sectionViews],
    ));
}

function nonprofitThemeSectionBladeViews(): string
{
    $sectionViews = glob(__DIR__ . '/../../resources/views/sections/*.blade.php') ?: [];

    return implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        $sectionViews,
    ));
}

function nonprofitThemePublicOutputAssets(): string
{
    $translationFiles = glob(__DIR__ . '/../../resources/lang/en/*.php') ?: [];

    return nonprofitThemeBladeViews() . "\n" . implode("\n", array_map(
        static fn (string $path): string => file_get_contents($path) ?: '',
        $translationFiles,
    ));
}

it('uses the premium page wrapper with brand tokens and skip link', function (): void {
    $blade = nonprofitThemeBladeViews();

    expect($blade)
        ->toContain('$brand->tokens()')
        ->toContain('$token . \':\' . $value')
        ->toContain('skip_to_content')
        ->toContain('id="main-content"')
        ->toContain('<main')
        ->toContain('nonprofit-shell');
});

it('keeps section palette colours behind semantic token classes', function (): void {
    $sectionBlade = nonprofitThemeSectionBladeViews();

    expect($sectionBlade)
        ->toContain('nonprofit-bg-surface-warm')
        ->toContain('nonprofit-bg-primary-deep')
        ->toContain('nonprofit-text-accent')
        ->toContain('nonprofit-border-accent')
        ->not->toMatch('/#[0-9a-fA-F]{6}\b/')
        ->not->toMatch('/(?:bg|text|border|from|to|shadow|hover:border)-(?:emerald|green|amber|yellow|orange)-\d{2,3}(?:\/\d+)?/');
});

it('ships default theme tokens with WCAG AA contrast for dark, emerald, and amber pairings', function (): void {
    $primaryColor = nonprofitPresetColor('primaryColor');
    $neutralColor = nonprofitPresetColor('neutralColor');
    $accentColor = nonprofitPresetColor('accentColor');
    $surfaceColor = nonprofitPresetColor('surfaceColor');

    expect(nonprofitContrastRatio('#ffffff', $primaryColor))->toBeGreaterThanOrEqual(4.5)
        ->and(nonprofitContrastRatio('#ffffff', $neutralColor))->toBeGreaterThanOrEqual(4.5)
        ->and(nonprofitContrastRatio($accentColor, $neutralColor))->toBeGreaterThanOrEqual(4.5)
        ->and(nonprofitContrastRatio($primaryColor, $surfaceColor))->toBeGreaterThanOrEqual(4.5)
        ->and(nonprofitContrastRatio($neutralColor, $surfaceColor))->toBeGreaterThanOrEqual(4.5)
        ->and(nonprofitContrastRatio('#7c2d12', $surfaceColor))->toBeGreaterThanOrEqual(4.5);
});

function nonprofitPresetColor(string $key): string
{
    $value = NonprofitThemeServiceProvider::definition()->presets[0]->values[$key] ?? null;

    throw_unless(is_string($value), RuntimeException::class, sprintf('Theme Nonprofit preset color [%s] must be a string.', $key));

    return $value;
}

function nonprofitContrastRatio(string $foreground, string $background): float
{
    $foregroundLuminance = nonprofitRelativeLuminance($foreground);
    $backgroundLuminance = nonprofitRelativeLuminance($background);

    return (max($foregroundLuminance, $backgroundLuminance) + 0.05) / (min($foregroundLuminance, $backgroundLuminance) + 0.05);
}

function nonprofitRelativeLuminance(string $hex): float
{
    $channels = nonprofitRgbChannels($hex);
    $linearChannels = array_map(
        static fn (int $channel): float => nonprofitLinearChannel($channel / 255),
        $channels,
    );

    return (0.2126 * $linearChannels[0]) + (0.7152 * $linearChannels[1]) + (0.0722 * $linearChannels[2]);
}

/**
 * @return array{0: int, 1: int, 2: int}
 */
function nonprofitRgbChannels(string $hex): array
{
    $normalized = ltrim($hex, '#');

    return [
        nonprofitHexPairToInteger(substr($normalized, 0, 2)),
        nonprofitHexPairToInteger(substr($normalized, 2, 2)),
        nonprofitHexPairToInteger(substr($normalized, 4, 2)),
    ];
}

function nonprofitHexPairToInteger(string $hex): int
{
    $value = hexdec($hex);

    if (! is_int($value)) {
        throw new RuntimeException(sprintf('Hex pair [%s] must decode to an integer.', $hex));
    }

    return $value;
}

function nonprofitLinearChannel(float $channel): float
{
    if ($channel <= 0.03928) {
        return $channel / 12.92;
    }

    return (($channel + 0.055) / 1.055) ** 2.4;
}

it('keeps public Blade free of authoring or package metadata', function (): void {
    $publicOutput = nonprofitThemePublicOutputAssets();

    expect($publicOutput)
        ->not->toContain('capell-app/theme-nonprofit')
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
    $blade = nonprofitThemeBladeViews();

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
        ->not->toContain('CapellCore::isPackageInstalled')
        ->not->toContain('Route::getRoutes()')
        ->not->toContain('refreshNameLookups')
        ->not->toContain('->translation')
        ->not->toContain('->assets')
        ->not->toContain('->media->')
        ->not->toContain('find(');
});

it('ships a scoped dark-mode layer for the Nonprofit shell', function (): void {
    $css = file_get_contents(__DIR__ . '/../../resources/css/theme-nonprofit.css') ?: '';

    expect($css)
        ->toContain('.dark .nonprofit-shell')
        ->toContain('.nonprofit-shell.dark')
        ->toContain('@media (prefers-color-scheme: dark)')
        ->toContain('--nonprofit-surface-warm')
        ->toContain('--nonprofit-on-dark-muted')
        ->toContain('color-scheme: dark')
        ->toContain('.text-slate-600')
        ->toContain('.border-slate-200');
});

it('keeps theme Tailwind classes unprefixed because package assets are isolated', function (): void {
    $provider = file_get_contents(__DIR__ . '/../../src/NonprofitThemeServiceProvider.php') ?: '';
    $blade = nonprofitThemeBladeViews();

    expect($provider)
        ->toContain("VendorAssetData::tailwindImport('resources/css/theme-nonprofit.css'")
        ->toContain("VendorAssetData::tailwindSource('resources/views/**/*.blade.php'")
        ->and($blade)->not->toContain('tw:');
});
