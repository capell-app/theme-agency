<?php

declare(strict_types=1);

return [
    'enabled' => env('CAPELL_DEPLOYMENTS_ENABLED', true),
    'http_timeout' => env('CAPELL_DEPLOYMENTS_HTTP_TIMEOUT', 10),
    'oauth' => [
        'github' => [
            'client_id' => env('CAPELL_GITHUB_CLIENT_ID'),
            'client_secret' => env('CAPELL_GITHUB_CLIENT_SECRET'),
        ],
        'gitlab' => [
            'client_id' => env('CAPELL_GITLAB_CLIENT_ID'),
            'client_secret' => env('CAPELL_GITLAB_CLIENT_SECRET'),
        ],
        'bitbucket' => [
            'client_id' => env('CAPELL_BITBUCKET_CLIENT_ID'),
            'client_secret' => env('CAPELL_BITBUCKET_CLIENT_SECRET'),
        ],
    ],
];
