<?php

use Capell\Core\Enums\DefaultColorEnum;
use Capell\Core\Models\Site;
use Capell\Core\Models\Theme;
use Capell\FoundationTheme\Actions\ResolveSafeCssColorTokenAction;
use Capell\FoundationTheme\Settings\FoundationThemeSettings;
use Capell\Frontend\Facades\Frontend;

$theme = Frontend::theme();
$site = Frontend::site();
$foundationSettings = null;

try {
    $foundationSettings = resolve(FoundationThemeSettings::class);
} catch (Throwable) {
    $foundationSettings = null;
}

$brandColorMeta = $site instanceof Site ? $site->getMeta('brand_color') : null;
$linkColorMeta = $theme instanceof Theme ? $theme->getMeta('link_color') : null;
$linkColorActiveMeta = $theme instanceof Theme ? $theme->getMeta('link_color_active') : null;
$dividerColorMeta = $theme instanceof Theme ? $theme->getMeta('divider_color') : null;

$resolveColorToken = static fn (mixed $value, string $fallback): string => is_string($value) && $value !== '' ? $value : $fallback;
$convertColorToken = static fn (mixed $value, string $fallback): string => ResolveSafeCssColorTokenAction::run($value, $fallback);

$brandColor = $convertColorToken($resolveColorToken($brandColorMeta, '#111827'), '#111827');
$linkColor = $convertColorToken($resolveColorToken($linkColorMeta, '#1d4ed8'), '#1d4ed8');
$linkColorActive = $convertColorToken($resolveColorToken(
    $linkColorActiveMeta,
    $resolveColorToken($linkColorMeta, '#1e40af'),
), '#1e40af');
$dividerColor = $convertColorToken($resolveColorToken($dividerColorMeta, '#e5e7eb'), '#e5e7eb');
$resolveSettingColor = static function (string $property, string $fallback) use ($foundationSettings, $resolveColorToken, $convertColorToken): string {
    $value = $foundationSettings instanceof FoundationThemeSettings ? $foundationSettings->{$property} : null;

    try {
        return $convertColorToken($resolveColorToken($value, $fallback), $fallback);
    } catch (Throwable) {
        return $convertColorToken($fallback, $fallback);
    }
};

$foundationPageBackground = $resolveSettingColor('page_background_color', '#faf9f7');
$foundationSurfaceBackground = $resolveSettingColor('surface_background_color', '#ffffff');
$foundationMutedBackground = $resolveSettingColor('muted_background_color', '#f4f3f1');
$foundationHeaderBackground = $resolveSettingColor('header_background_color', '#ffffff');
$foundationBorderColor = $resolveSettingColor('border_color', '#e1e5eb');
$foundationBorderStrongColor = $resolveSettingColor('border_strong_color', '#c7ced8');
$foundationCardBackground = $resolveSettingColor('card_background_color', '#ffffff');
$foundationPrimaryAction = $resolveSettingColor('primary_action_color', '#3b5998');
$foundationBandBackground = $resolveSettingColor('band_background_color', '#faf9f7');
$foundationBandAlternateBackground = $resolveSettingColor('band_alternate_background_color', '#f4f3f1');
$foundationBandAccentBackground = $resolveSettingColor('band_accent_background_color', '#f4f3f1');
$foundationBandBorder = $resolveSettingColor('band_border_color', '#e1e5eb');
$foundationImageBorder = $resolveSettingColor('image_border_color', '#e1e5eb');
$foundationImageRadius = $foundationSettings instanceof FoundationThemeSettings && in_array($foundationSettings->image_radius, ['0rem', '0.25rem', '0.5rem'], true)
    ? $foundationSettings->image_radius
    : '0.5rem';
$foundationSectionSpacing = $foundationSettings instanceof FoundationThemeSettings
    ? $foundationSettings->sectionSpacingCssValue()
    : FoundationThemeSettings::sectionSpacingCssValueFor(null);
$foundationWidgetGap = $foundationSettings instanceof FoundationThemeSettings
    ? $foundationSettings->widgetGapCssValue()
    : FoundationThemeSettings::widgetGapCssValueFor(null);

$isSafeTokenName = static fn (string $name): bool => preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]*$/', $name) === 1;

$paletteColors = collect(DefaultColorEnum::getKeyValues())
    ->merge($theme instanceof Theme && is_array($theme->colors) ? $theme->colors : [])
    ->map(function (mixed $value, string $name) use ($isSafeTokenName): ?array {
        if (! is_string($value) || ! $isSafeTokenName($name)) {
            return null;
        }

        $convertedValue = ResolveSafeCssColorTokenAction::run($value, '');
        if ($convertedValue === '') {
            return null;
        }

        return ['name' => $name, 'value' => $convertedValue];
    })
    ->filter()
    ->values();

?>

<style>
    :root {
        @foreach ($paletteColors as $paletteColor)
        --color-{{ $paletteColor['name'] }}: {{ $paletteColor['value'] }};
        @endforeach
        --color-brand: {{ $brandColor }};
        --color-link: {{ $linkColor }};
        --color-link-active: {{ $linkColorActive }};
        --color-divider: {{ $dividerColor }};
        --foundation-page-bg: {{ $foundationPageBackground }};
        --foundation-surface-bg: {{ $foundationSurfaceBackground }};
        --foundation-muted-bg: {{ $foundationMutedBackground }};
        --foundation-header-bg: {{ $foundationHeaderBackground }};
        --foundation-border: {{ $foundationBorderColor }};
        --foundation-border-strong: {{ $foundationBorderStrongColor }};
        --foundation-card-bg: {{ $foundationCardBackground }};
        --foundation-primary-action: {{ $foundationPrimaryAction }};
        --foundation-band-bg: {{ $foundationBandBackground }};
        --foundation-band-alt-bg: {{ $foundationBandAlternateBackground }};
        --foundation-band-accent-bg: {{ $foundationBandAccentBackground }};
        --foundation-band-border: {{ $foundationBandBorder }};
        --foundation-image-border: {{ $foundationImageBorder }};
        --foundation-image-radius: {{ $foundationImageRadius }};
        --foundation-section-spacing: {{ $foundationSectionSpacing }};
        --foundation-widget-gap: {{ $foundationWidgetGap }};
        --foundation-radius: 0.5rem;
    }
</style>
