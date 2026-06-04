<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('login_audit.show_login_audits')) {
            $this->migrator->add('login_audit.show_login_audits', true);
        }

        if (! $this->migrator->exists('login_audit.retention_days')) {
            $this->migrator->add('login_audit.retention_days', 90);
        }

        if (! $this->migrator->exists('login_audit.track_user_ip_addresses')) {
            $this->migrator->add('login_audit.track_user_ip_addresses', true);
        }

        if (! $this->migrator->exists('login_audit.track_admin_activity')) {
            $this->migrator->add('login_audit.track_admin_activity', true);
        }

        if (! $this->migrator->exists('login_audit.activity_update_grace_seconds')) {
            $this->migrator->add('login_audit.activity_update_grace_seconds', 60);
        }

        if (! $this->migrator->exists('login_audit.last_purged_at')) {
            $this->migrator->add('login_audit.last_purged_at', null);
        }

        if (! $this->migrator->exists('login_audit.enable_user_resource_bridge')) {
            $this->migrator->add('login_audit.enable_user_resource_bridge', true);
        }
    }
};
