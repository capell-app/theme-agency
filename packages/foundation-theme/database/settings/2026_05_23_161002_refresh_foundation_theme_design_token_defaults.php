<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $updates = [
            'foundation_theme.page_background_color' => ['#faf9f7', '#f6f2ea'],
            'foundation_theme.muted_background_color' => ['#f4f3f1', '#e8eef7'],
            'foundation_theme.header_background_color' => ['#ffffff', '#fbfaf7'],
            'foundation_theme.border_color' => ['#e5e7eb', '#d2dbe8'],
            'foundation_theme.border_strong_color' => ['#cbd5e1', '#9fb0c8'],
            'foundation_theme.primary_action_color' => ['#3b5998', '#315f8f'],
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
