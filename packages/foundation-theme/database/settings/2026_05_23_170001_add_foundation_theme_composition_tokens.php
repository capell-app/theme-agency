<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $defaults = [
            'foundation_theme.band_background_color' => '#faf9f7',
            'foundation_theme.band_alternate_background_color' => '#f4f3f1',
            'foundation_theme.band_accent_background_color' => '#f4f3f1',
            'foundation_theme.band_border_color' => '#e1e5eb',
            'foundation_theme.widget_gap' => 'balanced',
        ];

        foreach ($defaults as $key => $value) {
            if (! $this->migrator->exists($key)) {
                $this->migrator->add($key, $value);
            }
        }
    }
};
