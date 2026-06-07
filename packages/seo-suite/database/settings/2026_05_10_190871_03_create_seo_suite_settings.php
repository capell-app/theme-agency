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
            'seo_suite.seo_authoring_strict_meta_enabled' => false,
            'seo_suite.seo_meta_description_min_length' => 65,
            'seo_suite.seo_meta_description_max_length' => 200,
            'seo_suite.seo_authoring_require_title_in_description' => false,
            'seo_suite.seo_quality_gate_preset' => 'standard',
            'seo_suite.seo_quality_gate_meta_description_mode' => 'blocker',
            'seo_suite.seo_quality_gate_meta_title_mode' => 'warning',
            'seo_suite.seo_quality_gate_social_image_mode' => 'warning',
            'seo_suite.seo_quality_gate_canonical_mode' => 'warning',
            'seo_suite.seo_quality_gate_robots_mode' => 'warning',
            'seo_suite.seo_quality_gate_schema_mode' => 'warning',
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
