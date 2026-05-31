<?php

declare(strict_types=1);

use Illuminate\Support\Env;

return [
    'default_provider' => Env::get('CAPELL_PAYMENTS_PROVIDER', 'stripe'),

    'stripe' => [
        'secret_key' => Env::get('STRIPE_SECRET'),
        'publishable_key' => Env::get('STRIPE_KEY'),
        'webhook_secret' => Env::get('STRIPE_WEBHOOK_SECRET'),
        'api_base_url' => Env::get('STRIPE_API_BASE_URL', 'https://api.stripe.com'),
        'api_version' => Env::get('STRIPE_API_VERSION', '2026-02-25.clover'),
        'timeout' => 20,
        'connect_timeout' => 5,
    ],

    'tables' => [
        'customers' => 'payment_customers',
        'checkout_sessions' => 'payment_checkout_sessions',
        'payment_intents' => 'payment_intents',
        'subscriptions' => 'payment_subscriptions',
    ],
];
