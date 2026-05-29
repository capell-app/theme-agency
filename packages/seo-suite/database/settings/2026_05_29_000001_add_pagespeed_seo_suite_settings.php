<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $defaults = [
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
