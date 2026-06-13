<?php

declare(strict_types=1);

return [
    'public_path_prefix' => 'bookings',
    'public_slot_interval_minutes' => 15,
    'reminder_lead_minutes' => 1440,
    'reminder_dispatch_limit' => 100,
    'hold_expiry_minutes' => 30,
    'default_group_capacity' => 8,
    'fuel_rate_pence_per_mile' => 500,
    'review_offsets_days' => [1, 2, 10],
    'message_log_retention_days' => 730,
    'travel_observation_retention_days' => 365,
];
