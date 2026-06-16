<?php

declare(strict_types=1);

use Capell\Newsletter\Enums\ConfirmationMode;
use Capell\Newsletter\Enums\ResubscribePolicy;

return [
    'tables' => [
        'subscribers' => 'newsletter_subscribers',
        'consent_events' => 'newsletter_consent_events',
        'public_tokens' => 'newsletter_public_tokens',
        'form_mappings' => 'newsletter_form_mappings',
        'provider_connections' => 'newsletter_provider_connections',
        'provider_audiences' => 'newsletter_provider_audiences',
        'provider_interest_mappings' => 'newsletter_provider_interest_mappings',
        'provider_subscribers' => 'newsletter_provider_subscribers',
        'sync_attempts' => 'newsletter_sync_attempts',
        'segments' => 'newsletter_segments',
        'segment_subscriber' => 'newsletter_segment_subscriber',
        'import_batches' => 'newsletter_import_batches',
        'sends' => 'newsletter_sends',
    ],
    'double_opt_in' => [
        'enabled_by_default' => true,
        'default_confirmation_mode' => ConfirmationMode::CapellOwned->value,
        'token_expiry_hours' => 72,
    ],
    'public_tokens' => [
        'token_expiry_hours' => 72,
    ],
    'resubscribe_policy' => ResubscribePolicy::RequireDoubleOptIn->value,
    'newsletter_tag_type' => 'newsletter',
    'providers' => [
        'allow_fake_provider' => false,
    ],
    'sync' => [
        'queue' => null,
        'retry_minutes' => [5, 30, 120],
    ],
    'sends' => [
        'delivery_strategy' => 'external_handoff',
    ],
    'imports' => [
        'max_file_kb' => 2048,
        'max_rows' => 10000,
    ],
    'webhooks' => [
        'allow_fake_provider' => false,
        'signature_headers' => [
            'kit' => 'X-Kit-Webhook-Signature',
            'mailchimp' => 'X-Mailchimp-Signature',
            'campaign_monitor' => 'X-CM-Signature',
        ],
    ],
    'http' => [
        'timeout' => 15,
        'retry_times' => 3,
        'retry_delay_ms' => 500,
        'retry_after_max_ms' => 60000,
    ],
];
