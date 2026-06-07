<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $settings = [
            'login_audit.alert_new_devices' => true,
            'login_audit.alert_failed_logins' => false,
            'login_audit.alert_suspicious_logins' => true,
        ];

        foreach ($settings as $key => $value) {
            if (! $this->migrator->exists($key)) {
                $this->migrator->add($key, $value);
            }
        }
    }
};
