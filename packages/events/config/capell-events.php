<?php

declare(strict_types=1);

return [
    'recurrence' => [
        'sync_past_days' => (int) env('CAPELL_EVENTS_RECURRENCE_SYNC_PAST_DAYS', 31),
        'sync_horizon_days' => (int) env('CAPELL_EVENTS_RECURRENCE_SYNC_HORIZON_DAYS', 365),
    ],
    'notifications' => [
        'reminder_offsets_minutes' => [1440],
    ],
];
