<?php

declare(strict_types=1);

return [
    'providers' => [
        'stripe' => 'Stripe',
    ],
    'checkout_modes' => [
        'payment' => 'One-off payment',
        'subscription' => 'Subscription',
        'setup' => 'Setup',
    ],
    'purposes' => [
        'one_off' => 'One-off payment',
        'subscription' => 'Subscription',
        'donation' => 'Donation',
        'paid_download' => 'Paid download',
        'gated_access' => 'Paid gated access',
        'form_payment' => 'Form payment',
    ],
    'checkout_statuses' => [
        'open' => 'Open',
        'complete' => 'Complete',
        'expired' => 'Expired',
        'unknown' => 'Unknown',
    ],
    'payment_intent_statuses' => [
        'requires_payment_method' => 'Requires payment method',
        'requires_confirmation' => 'Requires confirmation',
        'requires_action' => 'Requires action',
        'processing' => 'Processing',
        'requires_capture' => 'Requires capture',
        'canceled' => 'Canceled',
        'succeeded' => 'Succeeded',
        'unknown' => 'Unknown',
    ],
    'subscription_statuses' => [
        'incomplete' => 'Incomplete',
        'incomplete_expired' => 'Incomplete expired',
        'trialing' => 'Trialing',
        'active' => 'Active',
        'past_due' => 'Past due',
        'canceled' => 'Canceled',
        'unpaid' => 'Unpaid',
        'paused' => 'Paused',
        'unknown' => 'Unknown',
    ],
];
