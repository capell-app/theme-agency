<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $defaults = [
            'foundation_theme.page_background_color' => '#faf9f7',
            'foundation_theme.surface_background_color' => '#ffffff',
            'foundation_theme.muted_background_color' => '#f4f3f1',
            'foundation_theme.header_background_color' => '#fbfaf7',
            'foundation_theme.border_color' => '#e1e5eb',
            'foundation_theme.border_strong_color' => '#c7ced8',
            'foundation_theme.card_background_color' => '#ffffff',
            'foundation_theme.primary_action_color' => '#315f8f',
            'foundation_theme.section_spacing' => 'relaxed',
        ];

        foreach ($defaults as $key => $value) {
            if (! $this->migrator->exists($key)) {
                $this->migrator->add($key, $value);
            }
        }
    }
};
