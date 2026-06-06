<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $settings = [
            'login_audit.enable_suspicious_detection' => true,
            'login_audit.failed_login_threshold' => 5,
            'login_audit.failed_login_window_minutes' => 60,
            'login_audit.check_unusual_login_times' => false,
        ];

        foreach ($settings as $key => $value) {
            if (! $this->migrator->exists($key)) {
                $this->migrator->add($key, $value);
            }
        }
    }
};
