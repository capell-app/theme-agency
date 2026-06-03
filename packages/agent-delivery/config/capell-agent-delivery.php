<?php

declare(strict_types=1);

return [
    'middleware' => ['api'],

    'public_pages' => [
        'auth_middleware' => null,
        'cache_max_age_seconds' => 300,
        'chunk_overlap_words' => 30,
        'chunk_target_words' => 160,
        'rate_limit_middleware' => 'throttle:capell-agent-delivery',
        'rate_limit_per_minute' => 60,
        'max_candidate_sites' => 50,
        'middleware' => [],
    ],
];
