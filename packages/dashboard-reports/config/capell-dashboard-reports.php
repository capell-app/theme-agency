<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Stale page threshold
    |--------------------------------------------------------------------------
    |
    | Number of days after which published pages count as stale in the Content
    | Health dashboard widget and its Page resource drill-down filter.
    |
    */
    'stale_page_threshold_days' => 90,

    /*
    |--------------------------------------------------------------------------
    | Digest recipients
    |--------------------------------------------------------------------------
    |
    | Email addresses that should receive the scheduled dashboard report digest
    | when capell:dashboard-reports:send-digest runs without --recipient.
    | Each address must belong to a Capell user so report counts stay scoped to
    | that user's site access.
    |
    */
    'digest_recipients' => [],
];
