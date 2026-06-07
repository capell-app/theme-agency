<?php

declare(strict_types=1);
use Illuminate\Support\Env;

return [
    'enabled' => Env::get('CAPELL_FRONTEND_AUTHORING', true),

    'workflow' => [
        'require_approval' => Env::get('CAPELL_FRONTEND_AUTHORING_REQUIRE_APPROVAL', false),
        'workspace_name' => Env::get('CAPELL_FRONTEND_AUTHORING_WORKSPACE_NAME', 'Inline editor changes'),
    ],

    'selectors' => [
        'page_title' => '#main h1:first-of-type',
        'page_content' => '#main .content-component:first-of-type',
    ],

    'html_policy' => 'trusted_admin_raw',
];
