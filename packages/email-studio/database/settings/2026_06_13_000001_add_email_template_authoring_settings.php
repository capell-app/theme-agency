<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $defaults = [
            'email_studio.template_config_variables' => ['app.name', 'app.url'],
            'email_studio.auth_replace_verification' => true,
            'email_studio.auth_replace_password_reset' => true,
            'email_studio.auth_send_welcome' => false,
            'email_studio.auth_send_verified' => false,
            'email_studio.auth_send_login' => false,
            'email_studio.auth_send_lockout' => false,
            'email_studio.auth_send_password_reset_success' => false,
        ];

        foreach ($defaults as $key => $value) {
            if (! $this->migrator->exists($key)) {
                $this->migrator->add($key, $value);
            }
        }
    }
};
