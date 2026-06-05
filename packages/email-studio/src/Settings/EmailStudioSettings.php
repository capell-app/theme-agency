<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Settings;

use Capell\Core\Contracts\SettingsContract;
use Capell\EmailStudio\Filament\Settings\EmailStudioSettingsSchema;
use Spatie\LaravelSettings\Settings;

final class EmailStudioSettings extends Settings implements SettingsContract
{
    public bool $mail_tracker_inject_pixel = true;

    public bool $mail_tracker_track_links = true;

    public bool $mail_tracker_log_content = true;

    public string $mail_tracker_log_content_strategy = 'database';

    public string $mail_tracker_filesystem = 'local';

    public string $mail_tracker_filesystem_folder = 'mail-tracker';

    public string $mail_tracker_queue = 'default';

    public int $mail_tracker_content_max_size = 65_535;

    public int $mail_tracker_search_date_start_days = 30;

    public int $mail_tracker_purge_retention_days = 60;

    public static function group(): string
    {
        return 'email_studio';
    }

    public static function schema(): string
    {
        return EmailStudioSettingsSchema::class;
    }
}
