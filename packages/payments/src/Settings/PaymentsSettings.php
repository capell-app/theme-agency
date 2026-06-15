<?php

declare(strict_types=1);

namespace Capell\Payments\Settings;

use Capell\Core\Contracts\SettingsContract;
use Capell\Payments\Filament\Settings\PaymentsSettingsSchema;
use Spatie\LaravelSettings\Settings;

final class PaymentsSettings extends Settings implements SettingsContract
{
    public ?string $stripe_secret_key = null;

    public ?string $stripe_publishable_key = null;

    public ?string $stripe_webhook_secret = null;

    public string $stripe_api_base_url = 'https://api.stripe.com';

    public string $stripe_api_version = '2026-02-25.clover';

    public int $stripe_timeout = 20;

    public int $stripe_connect_timeout = 5;

    public ?string $paypal_client_id = null;

    public ?string $paypal_client_secret = null;

    public string $paypal_api_base_url = 'https://api-m.paypal.com';

    public int $paypal_timeout = 20;

    public int $paypal_connect_timeout = 5;

    public int $webhook_freshness_hours = 48;

    public static function group(): string
    {
        return 'payments';
    }

    public static function schema(): string
    {
        return PaymentsSettingsSchema::class;
    }
}
