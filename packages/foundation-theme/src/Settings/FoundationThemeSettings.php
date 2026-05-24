<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\Settings;

use Capell\Core\Contracts\SettingsContract;
use Capell\FoundationTheme\Filament\Settings\FoundationThemeSettingsSchema;
use Spatie\LaravelSettings\Settings;

class FoundationThemeSettings extends Settings implements SettingsContract
{
    public const array SECTION_SPACING_OPTIONS = [
        'comfortable' => 'clamp(2.75rem, 5vw, 4.75rem)',
        'relaxed' => 'clamp(3.5rem, 6vw, 6rem)',
        'spacious' => 'clamp(4.5rem, 7vw, 7.25rem)',
    ];

    public const array WIDGET_GAP_OPTIONS = [
        'compact' => 'clamp(1rem, 2vw, 1.5rem)',
        'balanced' => 'clamp(1.25rem, 2.5vw, 2rem)',
        'airy' => 'clamp(1.75rem, 3vw, 2.75rem)',
    ];

    public bool $enable_lazy_loading = true;

    public bool $minify_assets = true;

    public string $page_background_color = '#faf9f7';

    public string $surface_background_color = '#ffffff';

    public string $muted_background_color = '#f4f3f1';

    public string $header_background_color = '#fbfaf7';

    public string $border_color = '#e1e5eb';

    public string $border_strong_color = '#c7ced8';

    public string $card_background_color = '#ffffff';

    public string $primary_action_color = '#315f8f';

    public string $band_background_color = '#faf9f7';

    public string $band_alternate_background_color = '#f4f3f1';

    public string $band_accent_background_color = '#f4f3f1';

    public string $band_border_color = '#e1e5eb';

    public string $image_border_color = '#e1e5eb';

    public string $image_radius = '0.5rem';

    public string $section_spacing = 'relaxed';

    public string $widget_gap = 'balanced';

    public static function group(): string
    {
        return 'foundation_theme';
    }

    public static function schema(): string
    {
        return FoundationThemeSettingsSchema::class;
    }

    public static function sectionSpacingCssValueFor(?string $sectionSpacing): string
    {
        return self::SECTION_SPACING_OPTIONS[$sectionSpacing ?? 'relaxed']
            ?? self::SECTION_SPACING_OPTIONS['relaxed'];
    }

    public static function widgetGapCssValueFor(?string $widgetGap): string
    {
        return self::WIDGET_GAP_OPTIONS[$widgetGap ?? 'balanced']
            ?? self::WIDGET_GAP_OPTIONS['balanced'];
    }

    public function sectionSpacingCssValue(): string
    {
        return self::sectionSpacingCssValueFor($this->section_spacing);
    }

    public function widgetGapCssValue(): string
    {
        return self::widgetGapCssValueFor($this->widget_gap);
    }
}
