<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $defaults = [
            'seo_suite.seo_audit_enabled' => true,
            'seo_suite.seo_check_meta_description' => true,
            'seo_suite.seo_check_meta_title' => true,
            'seo_suite.seo_check_duplicate_title' => true,
            'seo_suite.ai_discovery_audit_enabled' => true,
            'seo_suite.ai_discovery_default_enabled' => true,
            'seo_suite.ai_discovery_crawler_policy' => 'search_visible_training_restricted',
            'seo_suite.pagespeed_audit_enabled' => true,
            'seo_suite.pagespeed_weekly_digest_enabled' => true,
            'seo_suite.pagespeed_scheduled_limit' => 50,
            'seo_suite.pagespeed_stale_after_days' => 14,
        ];

        foreach ($defaults as $key => $value) {
            if (! $this->migrator->exists($key)) {
                $this->migrator->add($key, $value);
            }
        }
    }
};
