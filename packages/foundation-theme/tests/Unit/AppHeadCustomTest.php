<?php

declare(strict_types=1);

it('owns the opinionated public head behavior', function (): void {
    $component = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/app/head/custom.blade.php');
    $tokens = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/app/head/tokens.blade.php');

    expect($component)->toContain('localStorage.theme')
        ->and($component)->toContain('updateHeaderSticky')
        ->and($component)->toContain('<x-capell::app.head.tokens />')
        ->and($tokens)->toContain('--color-brand')
        ->and($tokens)->toContain('DefaultColorEnum::getKeyValues()')
        ->and($tokens)->toContain('->merge($theme instanceof Theme && is_array($theme->colors) ? $theme->colors : [])')
        ->and($tokens)->toContain('$linkColorActiveMeta = $theme instanceof Theme ? $theme->getMeta(\'link_color_active\') : null')
        ->and($tokens)->toContain('$resolveColorToken($linkColorMeta, \'#1e40af\')')
        ->and($tokens)->toContain('$dividerColor = $convertColorToken($resolveColorToken($dividerColorMeta, \'#e5e7eb\'), \'#e5e7eb\')')
        ->and($tokens)->toContain('resolve(FoundationThemeSettings::class)')
        ->and($tokens)->toContain('--foundation-page-bg')
        ->and($tokens)->toContain('--foundation-border')
        ->and($tokens)->toContain('--foundation-section-spacing')
        ->and($tokens)->toContain('--foundation-band-bg')
        ->and($tokens)->toContain('--foundation-image-border')
        ->and($tokens)->toContain('--foundation-image-radius')
        ->and($tokens)->toContain('--foundation-widget-gap');
});

it('maps foundation design settings into public CSS hooks', function (): void {
    $settings = file_get_contents(dirname(__DIR__, 2) . '/src/Settings/FoundationThemeSettings.php');
    $schema = file_get_contents(dirname(__DIR__, 2) . '/src/Filament/Settings/FoundationThemeSettingsSchema.php');
    $styles = file_get_contents(dirname(__DIR__, 2) . '/resources/css/theme/theme.css');
    $tokens = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/app/head/tokens.blade.php');

    expect($settings)->toContain('public string $page_background_color')
        ->and($settings)->toContain('public string $border_color')
        ->and($settings)->toContain('public string $band_background_color')
        ->and($settings)->toContain('public string $image_border_color')
        ->and($settings)->toContain('public string $image_radius')
        ->and($settings)->toContain('SECTION_SPACING_OPTIONS')
        ->and($settings)->toContain('WIDGET_GAP_OPTIONS')
        ->and($schema)->toContain("ColorPicker::make('page_background_color')")
        ->and($schema)->toContain("ColorPicker::make('border_color')")
        ->and($schema)->toContain("ColorPicker::make('band_background_color')")
        ->and($schema)->toContain("ColorPicker::make('image_border_color')")
        ->and($schema)->toContain("Select::make('image_radius')")
        ->and($schema)->toContain("Select::make('section_spacing')")
        ->and($schema)->toContain("Select::make('widget_gap')")
        ->and($tokens)->toContain('--foundation-band-alt-bg')
        ->and($tokens)->toContain('--foundation-widget-gap')
        ->and($styles)->toContain('background: var(--foundation-page-bg)')
        ->and($styles)->toContain('var(--foundation-section-spacing)')
        ->and($styles)->toContain('var(--foundation-card-bg)')
        ->and($styles)->toContain('var(--foundation-primary-action)')
        ->and($styles)->toContain('var(--foundation-image-border)')
        ->and($styles)->toContain('var(--foundation-image-radius)');
});
