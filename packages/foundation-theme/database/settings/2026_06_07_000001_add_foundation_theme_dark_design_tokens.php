<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $defaults = [
            'foundation_theme.dark_page_background_color' => '#0f172a',
            'foundation_theme.dark_surface_background_color' => '#111827',
            'foundation_theme.dark_muted_background_color' => '#1f2937',
            'foundation_theme.dark_header_background_color' => '#111827',
            'foundation_theme.dark_border_color' => '#334155',
            'foundation_theme.dark_border_strong_color' => '#475569',
            'foundation_theme.dark_card_background_color' => '#111827',
            'foundation_theme.dark_primary_action_color' => '#93c5fd',
            'foundation_theme.dark_band_background_color' => '#0f172a',
            'foundation_theme.dark_band_alternate_background_color' => '#111827',
            'foundation_theme.dark_band_accent_background_color' => '#1e293b',
            'foundation_theme.dark_band_border_color' => '#334155',
            'foundation_theme.dark_image_border_color' => '#334155',
        ];

        foreach ($defaults as $key => $value) {
            if (! $this->migrator->exists($key)) {
                $this->migrator->add($key, $value);
            }
        }
    }
};
