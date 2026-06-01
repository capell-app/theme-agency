<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $defaults = [
            'payments.stripe_secret_key' => null,
            'payments.stripe_publishable_key' => null,
            'payments.stripe_webhook_secret' => null,
            'payments.stripe_api_base_url' => 'https://api.stripe.com',
            'payments.stripe_api_version' => '2026-02-25.clover',
            'payments.stripe_timeout' => 20,
            'payments.stripe_connect_timeout' => 5,
            'payments.webhook_freshness_hours' => 48,
        ];

        foreach ($defaults as $key => $value) {
            if (! $this->migrator->exists($key)) {
                $this->migrator->add($key, $value);
            }
        }
    }
};
