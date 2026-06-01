<?php

declare(strict_types=1);

return [
    'title' => 'Payments',
    'stripe_secret_key' => 'Stripe secret key',
    'stripe_secret_key_helper' => 'Used for Stripe Checkout API requests when STRIPE_SECRET is not set.',
    'stripe_publishable_key' => 'Stripe publishable key',
    'stripe_webhook_secret' => 'Stripe webhook secret',
    'stripe_webhook_secret_helper' => 'Used to verify incoming Stripe webhook signatures when STRIPE_WEBHOOK_SECRET is not set.',
    'stripe_api_base_url' => 'Stripe API base URL',
    'stripe_api_version' => 'Stripe API version',
    'stripe_timeout' => 'Stripe timeout',
    'stripe_connect_timeout' => 'Stripe connection timeout',
    'webhook_freshness_hours' => 'Webhook freshness window',
];
