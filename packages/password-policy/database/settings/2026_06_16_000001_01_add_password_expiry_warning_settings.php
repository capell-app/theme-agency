<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('password_policy.password_expiry_warning_notifications_enabled')) {
            $this->migrator->add('password_policy.password_expiry_warning_notifications_enabled', false);
        }

        if (! $this->migrator->exists('password_policy.password_expiry_warning_days')) {
            $this->migrator->add('password_policy.password_expiry_warning_days', 7);
        }
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('password_policy.password_expiry_warning_notifications_enabled');
        $this->migrator->deleteIfExists('password_policy.password_expiry_warning_days');
    }
};
