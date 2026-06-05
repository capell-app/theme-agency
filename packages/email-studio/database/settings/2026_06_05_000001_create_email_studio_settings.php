<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $defaults = [
            'email_studio.mail_tracker_inject_pixel' => true,
            'email_studio.mail_tracker_track_links' => true,
            'email_studio.mail_tracker_log_content' => true,
            'email_studio.mail_tracker_log_content_strategy' => 'database',
            'email_studio.mail_tracker_filesystem' => 'local',
            'email_studio.mail_tracker_filesystem_folder' => 'mail-tracker',
            'email_studio.mail_tracker_queue' => 'default',
            'email_studio.mail_tracker_content_max_size' => 65_535,
            'email_studio.mail_tracker_search_date_start_days' => 30,
            'email_studio.mail_tracker_purge_retention_days' => 60,
        ];

        foreach ($defaults as $key => $value) {
            if (! $this->migrator->exists($key)) {
                $this->migrator->add($key, $value);
            }
        }
    }
};
