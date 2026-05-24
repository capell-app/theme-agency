<?php

use Capell\Core\Actions\ColorConverterAction;
use Capell\Core\Enums\DefaultColorEnum;
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

$brandColorMeta = $site->getMeta('brand_color');
$linkColorMeta = $theme->getMeta('link_color');
$linkColorActiveMeta = $theme->getMeta('link_color_active');
$dividerColorMeta = $theme->getMeta('divider_color');

$resolveColorToken = static fn (mixed $value, string $fallback): string => is_string($value) && $value !== '' ? $value : $fallback;

$brandColor = ColorConverterAction::run($resolveColorToken($brandColorMeta, '#111827'));
$linkColor = ColorConverterAction::run($resolveColorToken($linkColorMeta, '#1d4ed8'));
$linkColorActive = ColorConverterAction::run($resolveColorToken(
    $linkColorActiveMeta,
    $resolveColorToken($linkColorMeta, '#1e40af'),
));
$dividerColor = ColorConverterAction::run($resolveColorToken($dividerColorMeta, '#e5e7eb'));
$resolveSettingColor = static function (string $property, string $fallback) use ($foundationSettings, $resolveColorToken): string {
    $value = $foundationSettings instanceof FoundationThemeSettings ? $foundationSettings->{$property} : null;

    try {
        return ColorConverterAction::run($resolveColorToken($value, $fallback));
    } catch (Throwable) {
        return ColorConverterAction::run($fallback);
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

$isSafeToken = static fn (string $name, string $value): bool => preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]*$/', $name) === 1
    && preg_match('/[\x00-\x1F\x7F;{}<>]/', $value) !== 1;

$paletteColors = collect(DefaultColorEnum::getKeyValues())
    ->merge($theme->colors)
    ->map(function (mixed $value, string $name) use ($isSafeToken): ?array {
        if (! is_string($value) || ! $isSafeToken($name, $value)) {
            return null;
        }

        try {
            $convertedValue = ColorConverterAction::run($value);
        } catch (Throwable) {
            return null;
        }

        if (! is_string($convertedValue) || ! $isSafeToken($name, $convertedValue)) {
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
