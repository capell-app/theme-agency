<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $updates = [
            'foundation_theme.page_background_color' => ['#f6f2ea', '#faf9f7'],
            'foundation_theme.muted_background_color' => ['#e8eef7', '#f4f3f1'],
            'foundation_theme.border_color' => ['#d2dbe8', '#e1e5eb'],
            'foundation_theme.border_strong_color' => ['#9fb0c8', '#c7ced8'],
            'foundation_theme.band_background_color' => ['#fbfaf7', '#faf9f7'],
            'foundation_theme.band_alternate_background_color' => ['#eef3f8', '#f4f3f1'],
            'foundation_theme.band_accent_background_color' => ['#f3f8f5', '#f4f3f1'],
            'foundation_theme.band_border_color' => ['#c7d4e4', '#e1e5eb'],
        ];

        foreach ($updates as $key => [$previousDefault, $newDefault]) {
            if (! $this->migrator->exists($key)) {
                $this->migrator->add($key, $newDefault);

                continue;
            }

            $this->migrator->update(
                $key,
                static fn (mixed $value): mixed => $value === $previousDefault ? $newDefault : $value,
            );
        }
    }
};
