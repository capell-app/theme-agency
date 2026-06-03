<?php

declare(strict_types=1);

namespace Capell\Newsletter\Support;

class CapellNewsletterManager
{
    /**
     * @return array<int, string>
     */
    public static function getMigrations(): array
    {
        return [
            '2026_05_10_190861_02_create_newsletter_subscribers_table',
            '2026_05_10_190861_01_create_newsletter_provider_connections_table',
            '2026_05_10_190861_04_create_newsletter_consent_events_table',
            '2026_05_10_190861_07_create_newsletter_public_tokens_table',
            '2026_05_10_190861_10_create_newsletter_form_mappings_table',
            '2026_05_10_190861_03_create_newsletter_provider_audiences_table',
            '2026_05_10_190861_05_create_newsletter_provider_interest_mappings_table',
            '2026_05_10_190861_06_create_newsletter_provider_subscribers_table',
            '2026_05_10_190861_09_create_newsletter_sync_attempts_table',
            '2026_05_10_190861_08_create_newsletter_segments_table',
            '2026_05_10_190861_11_create_newsletter_import_batches_table',
            '2026_05_10_190861_12_create_newsletter_processed_webhook_events_table',
            '2026_05_31_120000_13_create_newsletter_sends_table',
        ];
    }
}
