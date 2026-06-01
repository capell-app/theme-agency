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
];
