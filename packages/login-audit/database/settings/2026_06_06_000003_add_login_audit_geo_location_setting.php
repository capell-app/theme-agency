<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('login_audit.enable_geo_location')) {
            $this->migrator->add('login_audit.enable_geo_location', false);
        }
    }
};
