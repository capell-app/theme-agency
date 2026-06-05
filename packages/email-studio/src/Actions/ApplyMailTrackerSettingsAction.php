<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Models\SentEmail;
use Capell\EmailStudio\Models\SentEmailUrlClicked;
use Capell\EmailStudio\Settings\EmailStudioSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use jdavidbakr\MailTracker\MailTracker;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class ApplyMailTrackerSettingsAction
{
    use AsAction;

    public function handle(): void
    {
        $settings = $this->settingsValues();

        Config::set('mail-tracker.inject-pixel', $settings['inject_pixel']);
        Config::set('mail-tracker.track-links', $settings['track_links']);
        Config::set('mail-tracker.log-content', $settings['log_content']);
        Config::set('mail-tracker.log-content-strategy', $this->contentStrategy($settings['log_content_strategy']));
        Config::set('mail-tracker.tracker-filesystem', $this->stringSetting($settings['filesystem'], 'local'));
        Config::set('mail-tracker.tracker-filesystem-folder', $this->stringSetting($settings['filesystem_folder'], 'mail-tracker'));
        Config::set('mail-tracker.tracker-queue', $this->stringSetting($settings['queue'], 'default'));
        Config::set('mail-tracker.content-max-size', max(1, $settings['content_max_size']));
        Config::set('mail-tracker.search-date-start', max(1, $settings['search_date_start_days']));
        Config::set('mail-tracker.expire-days', max(0, $settings['purge_retention_days']));
        Config::set('mail-tracker.route', [
            'prefix' => 'email',
            'middleware' => ['api'],
        ]);
        Config::set('mail-tracker.redirect-missing-links-to', '/');
        Config::set('mail-tracker.admin-route.enabled', false);

        if (class_exists(MailTracker::class)) {
            MailTracker::useSentEmailModel(SentEmail::class);
            MailTracker::useSentEmailUrlClickedModel(SentEmailUrlClicked::class);
        }
    }

    /**
     * @return array{
     *     inject_pixel: bool,
     *     track_links: bool,
     *     log_content: bool,
     *     log_content_strategy: string,
     *     filesystem: string,
     *     filesystem_folder: string,
     *     queue: string,
     *     content_max_size: int,
     *     search_date_start_days: int,
     *     purge_retention_days: int,
     * }
     */
    private function settingsValues(): array
    {
        $defaults = $this->defaultSettingsValues();

        if (Model::getConnectionResolver() === null) {
            return $defaults;
        }

        try {
            /** @var EmailStudioSettings $settings */
            $settings = resolve(EmailStudioSettings::class);

            return [
                'inject_pixel' => $settings->mail_tracker_inject_pixel,
                'track_links' => $settings->mail_tracker_track_links,
                'log_content' => $settings->mail_tracker_log_content,
                'log_content_strategy' => $settings->mail_tracker_log_content_strategy,
                'filesystem' => $settings->mail_tracker_filesystem,
                'filesystem_folder' => $settings->mail_tracker_filesystem_folder,
                'queue' => $settings->mail_tracker_queue,
                'content_max_size' => $settings->mail_tracker_content_max_size,
                'search_date_start_days' => $settings->mail_tracker_search_date_start_days,
                'purge_retention_days' => $settings->mail_tracker_purge_retention_days,
            ];
        } catch (Throwable) {
            return $defaults;
        }
    }

    /**
     * @return array{
     *     inject_pixel: bool,
     *     track_links: bool,
     *     log_content: bool,
     *     log_content_strategy: string,
     *     filesystem: string,
     *     filesystem_folder: string,
     *     queue: string,
     *     content_max_size: int,
     *     search_date_start_days: int,
     *     purge_retention_days: int,
     * }
     */
    private function defaultSettingsValues(): array
    {
        $defaults = config('capell-email-studio.mail_tracker', []);

        if (! is_array($defaults)) {
            $defaults = [];
        }

        return [
            'inject_pixel' => $this->boolValue($defaults['inject_pixel'] ?? null, true),
            'track_links' => $this->boolValue($defaults['track_links'] ?? null, true),
            'log_content' => $this->boolValue($defaults['log_content'] ?? null, true),
            'log_content_strategy' => $this->stringValue($defaults['log_content_strategy'] ?? null, 'database'),
            'filesystem' => $this->stringValue($defaults['filesystem'] ?? null, 'local'),
            'filesystem_folder' => $this->stringValue($defaults['filesystem_folder'] ?? null, 'mail-tracker'),
            'queue' => $this->stringValue($defaults['queue'] ?? null, 'default'),
            'content_max_size' => $this->intValue($defaults['content_max_size'] ?? null, 65_535),
            'search_date_start_days' => $this->intValue($defaults['search_date_start_days'] ?? null, 30),
            'purge_retention_days' => $this->intValue($defaults['purge_retention_days'] ?? null, 60),
        ];
    }

    private function boolValue(mixed $value, bool $fallback): bool
    {
        return is_bool($value) ? $value : $fallback;
    }

    private function stringValue(mixed $value, string $fallback): string
    {
        return is_string($value) ? $value : $fallback;
    }

    private function intValue(mixed $value, int $fallback): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return $fallback;
    }

    private function contentStrategy(string $strategy): string
    {
        return in_array($strategy, ['database', 'filesystem'], true) ? $strategy : 'database';
    }

    private function stringSetting(string $value, string $fallback): string
    {
        $value = trim($value);

        return $value === '' ? $fallback : $value;
    }
}
