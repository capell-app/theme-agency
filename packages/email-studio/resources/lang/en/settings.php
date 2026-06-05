<?php

declare(strict_types=1);

return [
    'bytes' => 'bytes',
    'content_strategy_database' => 'Database',
    'content_strategy_filesystem' => 'Filesystem',
    'mail_tracker' => 'Mail tracking',
    'mail_tracker_content_max_size' => 'Maximum stored content size',
    'mail_tracker_filesystem' => 'Tracker filesystem disk',
    'mail_tracker_filesystem_folder' => 'Tracker filesystem folder',
    'mail_tracker_inject_pixel' => 'Track opens',
    'mail_tracker_inject_pixel_helper' => 'Injects the MailTracker tracking pixel into outgoing HTML email.',
    'mail_tracker_log_content' => 'Store rendered email content',
    'mail_tracker_log_content_helper' => 'Stores the original rendered HTML so admins can inspect sent messages.',
    'mail_tracker_log_content_strategy' => 'Content storage strategy',
    'mail_tracker_purge_retention_days' => 'Tracked email retention',
    'mail_tracker_purge_retention_days_helper' => 'Set to 0 to disable the scheduled purge. The default is 60 days.',
    'mail_tracker_queue' => 'Tracker queue',
    'mail_tracker_search_date_start_days' => 'Default search window',
    'mail_tracker_track_links' => 'Track clicks',
    'mail_tracker_track_links_helper' => 'Rewrites links through MailTracker signed redirect URLs.',
    'title' => 'Email Studio',
];
