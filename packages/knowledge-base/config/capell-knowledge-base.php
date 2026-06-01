<?php

declare(strict_types=1);

use Illuminate\Support\Env;

return [
    'public_path_prefix' => 'docs',
    'public_routes_enabled' => true,
    'default_search_weight' => 50,
    'feedback' => [
        'hash_salt' => Env::get('APP_KEY', 'capell-knowledge-base'),
    ],
];
