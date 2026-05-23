<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\Settings;

use Capell\Frontend\Contracts\SettingsMigrationProviderInterface;

class FoundationThemeSettingsMigrationProvider implements SettingsMigrationProviderInterface
{
    public function getSettingMigrations(): array
    {
        return [
            '2026_05_10_190850_01_create_foundation_theme_settings',
            '2026_05_23_160819_add_foundation_theme_design_tokens',
            '2026_05_23_161002_refresh_foundation_theme_design_token_defaults',
            '2026_05_23_170001_add_foundation_theme_composition_tokens',
            '2026_05_23_171201_quiet_foundation_theme_composition_palette',
            '2026_05_23_180101_add_foundation_theme_image_tokens',
        ];
    }

    public function migrations(): array
    {
        return $this->getSettingMigrations();
    }
}
