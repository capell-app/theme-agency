<?php

declare(strict_types=1);

it('owns the opinionated public head behavior', function (): void {
    $component = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/app/head/custom.blade.php');
    $tokens = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/app/head/tokens.blade.php');
    $tokenAction = file_get_contents(dirname(__DIR__, 2) . '/src/Actions/ResolveFoundationThemeTokensAction.php');

    expect($component)->toContain('localStorage.theme')
        ->and($component)->toContain('updateHeaderSticky')
        ->and($component)->toContain('<x-capell::app.head.tokens />')
        ->and($tokens)->toContain('ResolveFoundationThemeTokensAction::run()')
        ->and($tokens)->toContain('--color-brand')
        ->and($tokenAction)->toContain('DefaultColorEnum::getKeyValues()')
        ->and($tokenAction)->toContain('->merge($theme instanceof Theme && is_array($theme->colors) ? $theme->colors : [])')
        ->and($tokenAction)->toContain('$linkColorActiveMeta = $theme instanceof Theme ? $theme->getMeta(\'link_color_active\') : null')
        ->and($tokenAction)->toContain('resolve(FoundationThemeSettings::class)')
        ->and($tokens)->toContain('--foundation-page-bg')
        ->and($tokens)->toContain('--foundation-body-fg')
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
    $tokenAction = file_get_contents(dirname(__DIR__, 2) . '/src/Actions/ResolveFoundationThemeTokensAction.php');

    expect($settings)->toContain('public string $page_background_color')
        ->and($settings)->toContain('public string $border_color')
        ->and($settings)->toContain('public string $band_background_color')
        ->and($settings)->toContain('public string $image_border_color')
        ->and($settings)->toContain('public string $dark_page_background_color')
        ->and($settings)->toContain('public string $dark_primary_action_color')
        ->and($settings)->toContain('public string $dark_image_border_color')
        ->and($settings)->toContain('public string $image_radius')
        ->and($settings)->toContain('SECTION_SPACING_OPTIONS')
        ->and($settings)->toContain('WIDGET_GAP_OPTIONS')
        ->and($schema)->toContain("ColorPicker::make('page_background_color')")
        ->and($schema)->toContain("ColorPicker::make('border_color')")
        ->and($schema)->toContain("ColorPicker::make('band_background_color')")
        ->and($schema)->toContain("ColorPicker::make('image_border_color')")
        ->and($schema)->toContain("ColorPicker::make('dark_page_background_color')")
        ->and($schema)->toContain("ColorPicker::make('dark_primary_action_color')")
        ->and($schema)->toContain("ColorPicker::make('dark_image_border_color')")
        ->and($schema)->toContain("Select::make('image_radius')")
        ->and($schema)->toContain("Select::make('section_spacing')")
        ->and($schema)->toContain("Select::make('widget_gap')")
        ->and($tokenAction)->toContain('sectionSpacingCssValue()')
        ->and($tokenAction)->toContain("darkPageBackground: \$this->settingColor(\$settings, 'dark_page_background_color', '#0f172a')")
        ->and($tokenAction)->toContain("darkPrimaryAction: \$this->settingColor(\$settings, 'dark_primary_action_color', '#93c5fd')")
        ->and($tokenAction)->toContain('widgetGapCssValue()')
        ->and($tokens)->toContain('.dark:root')
        ->and($tokens)->toContain('--foundation-body-fg: #f8fafc')
        ->and($tokens)->toContain('--foundation-page-bg: {{ $tokens->darkPageBackground }}')
        ->and($tokens)->toContain('--foundation-band-alt-bg')
        ->and($tokens)->toContain('--foundation-widget-gap')
        ->and($styles)->toContain('background: var(--foundation-page-bg)')
        ->and($styles)->toContain('color: var(--foundation-body-fg)')
        ->and($styles)->toContain('var(--foundation-section-spacing)')
        ->and($styles)->toContain('var(--foundation-card-bg)')
        ->and($styles)->toContain('var(--foundation-primary-action)')
        ->and($styles)->toContain('var(--foundation-image-border)')
        ->and($styles)->toContain('var(--foundation-image-radius)');
});

it('ships additive settings defaults for the dark foundation token layer', function (): void {
    $migration = file_get_contents(dirname(__DIR__, 2) . '/database/settings/2026_06_07_000001_add_foundation_theme_dark_design_tokens.php');
    $translations = file_get_contents(dirname(__DIR__, 2) . '/resources/lang/en/form.php');
    $tokenData = file_get_contents(dirname(__DIR__, 2) . '/src/Data/FoundationThemeTokensData.php');

    expect($migration)
        ->toContain('foundation_theme.dark_page_background_color')
        ->toContain('foundation_theme.dark_surface_background_color')
        ->toContain('foundation_theme.dark_card_background_color')
        ->toContain('foundation_theme.dark_primary_action_color')
        ->toContain('foundation_theme.dark_image_border_color')
        ->toContain('$this->migrator->exists($key)')
        ->and($translations)->toContain('dark_design_tokens')
        ->and($translations)->toContain('Dark page background colour')
        ->and($tokenData)->toContain('public string $darkPageBackground')
        ->and($tokenData)->toContain('public string $darkPrimaryAction')
        ->and($tokenData)->toContain('public string $darkImageBorder');
});
