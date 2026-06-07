<?php

declare(strict_types=1);

return [
    'credentials_path' => 'Service account credentials path',
    'credentials_path_helper' => 'Store the JSON credentials outside settings and reference the absolute path here.',
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
