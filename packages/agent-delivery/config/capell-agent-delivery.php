<?php

declare(strict_types=1);

return [
    'middleware' => ['api'],

    'public_pages' => [
        'auth_middleware' => null,
        'rate_limit_middleware' => null,
        'rate_limit_per_minute' => 60,
        'max_candidate_sites' => 50,
        'middleware' => [],
    ],
];
