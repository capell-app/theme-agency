<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('foundation_theme.heading_scale')) {
            $this->migrator->add('foundation_theme.heading_scale', 'balanced');
        }
    }
};
