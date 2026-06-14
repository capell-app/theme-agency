<?php

declare(strict_types=1);

return [
    'credentials_path' => 'Service account credentials path',
    'credentials_path_helper' => 'Store the JSON credentials outside settings and reference the absolute path here.',
    'credentials_path_invalid_json' => 'The service-account credentials file must contain valid JSON.',
    'credentials_path_missing' => 'Not set',
    'credentials_path_not_readable' => 'The service-account credentials file must exist and be readable by the application.',
    'credentials_path_not_service_account' => 'The service-account credentials file must contain client_email and private_key values.',
    'credentials_path_valid' => 'Readable service-account JSON',
    'enabled' => 'Enable GA4 Reports reporting',
    'fieldset' => 'GA4 Reports',
    'property_id' => 'GA4 property ID',
    'property_id_helper' => 'Use the numeric GA4 property ID without the properties/ prefix.',
    'route_slug' => 'Admin page slug',
    'sync_cron' => 'Sync schedule',
    'sync_cron_helper' => 'Cron expression for the scheduled GA4 sync. Default is 0 2 * * * for 02:00 daily.',
    'sync_days' => 'Sync window',
    'title' => 'GA4 Reports settings',
];
