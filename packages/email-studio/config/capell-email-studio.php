<?php

declare(strict_types=1);

use Illuminate\Support\Env;

return [
    'tables' => [
        'profiles' => 'email_profiles',
        'templates' => 'email_templates',
        'template_themes' => 'email_template_themes',
        'template_variants' => 'email_template_variants',
        'messages' => 'email_messages',
        'recipients' => 'email_recipients',
        'events' => 'email_events',
        'replies' => 'email_replies',
        'suppressions' => 'email_suppressions',
        'template_registrations' => 'email_template_registrations',
        'tracking_tokens' => 'email_tracking_tokens',
    ],
    'default_provider' => 'smtp',
    'queue' => Env::get('CAPELL_EMAIL_STUDIO_QUEUE', 'default'),
    'sending_lock_ttl_seconds' => Env::get('CAPELL_EMAIL_STUDIO_SENDING_LOCK_TTL_SECONDS', 900),
    'track_opens' => true,
    'track_clicks' => true,
    'body_retention_days' => 90,
    'webhook_tolerance_seconds' => 300,
    'public_route_prefix' => Env::get('CAPELL_EMAIL_STUDIO_PUBLIC_PREFIX', 'mail'),
    'tracking_token_ttl_days' => 180,
    'webhook_rate_limit' => 'email-studio-webhooks',
    'tracking_rate_limit' => 'email-studio-tracking',
    'template_config_variables' => [
        'app.name',
        'app.url',
    ],
    'screenshot_adapter' => null,
    'auth' => [
        'replace_verification' => true,
        'replace_password_reset' => true,
        'send_welcome' => false,
        'send_verified' => false,
        'send_login' => false,
        'send_lockout' => false,
        'send_password_reset_success' => false,
    ],
    'mail_tracker' => [
        'inject_pixel' => true,
        'track_links' => true,
        'log_content' => true,
        'log_content_strategy' => 'database',
        'filesystem' => 'local',
        'filesystem_folder' => 'mail-tracker',
        'queue' => 'default',
        'content_max_size' => 65_535,
        'search_date_start_days' => 30,
        'purge_retention_days' => 60,
    ],
];
