<?php

declare(strict_types=1);

return [
    'tables' => [
        'accounts' => 'portal_accounts',
        'support_requests' => 'portal_support_requests',
    ],
    'hash_secret' => null,
    'route_prefix' => 'portal',
    'middleware' => ['web', 'auth'],
    'site_id' => null,
    'preferences' => [
        'email_updates' => [
            'label' => 'capell-customer-portal::generic.frontend.preference_email_updates',
        ],
        'product_updates' => [
            'label' => 'capell-customer-portal::generic.frontend.preference_product_updates',
        ],
        'event_reminders' => [
            'label' => 'capell-customer-portal::generic.frontend.preference_event_reminders',
        ],
    ],
];
