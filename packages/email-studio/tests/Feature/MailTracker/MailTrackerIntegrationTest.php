<?php

declare(strict_types=1);

use Capell\Admin\Support\Extensions\ExtensionManagementSurfaceRegistry;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;
use Capell\EmailStudio\Actions\ApplyMailTrackerSettingsAction;
use Capell\EmailStudio\Actions\PurgeTrackedEmailsAction;
use Capell\EmailStudio\Console\Commands\PurgeTrackedEmailsCommand;
use Capell\EmailStudio\Filament\Resources\SentEmails\SentEmailResource;
use Capell\EmailStudio\Filament\Settings\EmailStudioSettingsSchema;
use Capell\EmailStudio\Models\SentEmail;
use Capell\EmailStudio\Models\SentEmailUrlClicked;
use Capell\EmailStudio\Providers\EmailStudioServiceProvider;
use Capell\EmailStudio\Settings\EmailStudioSettings;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use jdavidbakr\MailTracker\MailTracker;

afterEach(function (): void {
    Carbon::setTestNow();
});

it('registers email studio settings and extension management surface', function (): void {
    $settingsRegistry = resolve(SettingsSchemaRegistry::class);

    expect($settingsRegistry->getSettingsClass(EmailStudioSettings::group()))
        ->toBe(EmailStudioSettings::class)
        ->and($settingsRegistry->getSchemas(EmailStudioSettings::group()))
        ->toContain(EmailStudioSettingsSchema::class)
        ->and($settingsRegistry->getMetadata(EmailStudioSettings::group())?->packageName)
        ->toBe(EmailStudioServiceProvider::$packageName);

    $surfaces = resolve(ExtensionManagementSurfaceRegistry::class)
        ->surfacesForPackage(EmailStudioServiceProvider::$packageName);

    expect($surfaces[0]->settingsGroup ?? null)->toBe(EmailStudioSettings::group());
});

it('applies settings to mail tracker configuration and custom models', function (): void {
    $settings = resolve(EmailStudioSettings::class);
    $settings->mail_tracker_inject_pixel = false;
    $settings->mail_tracker_track_links = false;
    $settings->mail_tracker_log_content = true;
    $settings->mail_tracker_log_content_strategy = 'filesystem';
    $settings->mail_tracker_filesystem = 'local';
    $settings->mail_tracker_filesystem_folder = 'tracked-mail';
    $settings->mail_tracker_queue = 'tracking';
    $settings->mail_tracker_content_max_size = 12_345;
    $settings->mail_tracker_search_date_start_days = 14;
    $settings->mail_tracker_purge_retention_days = 42;
    $settings->save();

    ApplyMailTrackerSettingsAction::run();

    expect(config('mail-tracker.inject-pixel'))->toBeFalse()
        ->and(config('mail-tracker.track-links'))->toBeFalse()
        ->and(config('mail-tracker.log-content'))->toBeTrue()
        ->and(config('mail-tracker.log-content-strategy'))->toBe('filesystem')
        ->and(config('mail-tracker.tracker-filesystem'))->toBe('local')
        ->and(config('mail-tracker.tracker-filesystem-folder'))->toBe('tracked-mail')
        ->and(config('mail-tracker.tracker-queue'))->toBe('tracking')
        ->and(config('mail-tracker.content-max-size'))->toBe(12_345)
        ->and(config('mail-tracker.search-date-start'))->toBe(14)
        ->and(config('mail-tracker.expire-days'))->toBe(42)
        ->and(config('mail-tracker.admin-route.enabled'))->toBeFalse()
        ->and(MailTracker::$sentEmailModel)->toBe(SentEmail::class)
        ->and(MailTracker::$sentEmailUrlClickedModel)->toBe(SentEmailUrlClicked::class)
        ->and((new SentEmail)->getTable())->toBe('sent_emails')
        ->and((new SentEmailUrlClicked)->getTable())->toBe('sent_emails_url_clicked');
});

it('purges expired tracked emails and click rows while preserving fresh records', function (): void {
    Carbon::setTestNow('2026-06-05 10:00:00');
    Storage::fake('local');

    $expiredEmail = SentEmail::query()->create([
        'hash' => 'expired-tracked-email-hash-00001',
        'subject' => 'Expired',
        'content' => null,
        'meta' => collect(['content_file_path' => 'mail-tracker/expired.html']),
        'created_at' => Carbon::now()->subDays(61),
        'updated_at' => Carbon::now()->subDays(61),
    ]);
    Storage::disk('local')->put('mail-tracker/expired.html', '<p>Expired</p>');

    $freshEmail = SentEmail::query()->create([
        'hash' => 'fresh-tracked-email-hash-0000001',
        'subject' => 'Fresh',
        'content' => '<p>Fresh</p>',
        'created_at' => Carbon::now()->subDays(10),
        'updated_at' => Carbon::now()->subDays(10),
    ]);

    SentEmailUrlClicked::query()->create([
        'sent_email_id' => $expiredEmail->getKey(),
        'url' => 'https://example.com/expired',
        'hash' => 'expired-click-hash-00000000001',
        'clicks' => 2,
    ]);
    SentEmailUrlClicked::query()->create([
        'sent_email_id' => $freshEmail->getKey(),
        'url' => 'https://example.com/fresh',
        'hash' => 'fresh-click-hash-0000000000001',
        'clicks' => 1,
    ]);

    $result = PurgeTrackedEmailsAction::run(retentionDays: 60);

    expect($result->retentionDays)->toBe(60)
        ->and($result->matchedEmails)->toBe(1)
        ->and($result->deletedClicks)->toBe(1)
        ->and($result->deletedEmails)->toBe(1)
        ->and(SentEmail::query()->whereKey($expiredEmail->getKey())->exists())->toBeFalse()
        ->and(SentEmail::query()->whereKey($freshEmail->getKey())->exists())->toBeTrue()
        ->and(SentEmailUrlClicked::query()->where('sent_email_id', $freshEmail->getKey())->exists())->toBeTrue();

    Storage::disk('local')->assertMissing('mail-tracker/expired.html');
});

it('supports dry run tracked email purge without deleting rows', function (): void {
    Carbon::setTestNow('2026-06-05 10:00:00');

    $expiredEmail = SentEmail::query()->create([
        'hash' => 'dry-run-tracked-email-hash-001',
        'subject' => 'Dry run',
        'created_at' => Carbon::now()->subDays(61),
        'updated_at' => Carbon::now()->subDays(61),
    ]);

    $result = PurgeTrackedEmailsAction::run(retentionDays: 60, dryRun: true);

    expect($result->dryRun)->toBeTrue()
        ->and($result->matchedEmails)->toBe(1)
        ->and($result->deletedEmails)->toBe(0)
        ->and(SentEmail::query()->whereKey($expiredEmail->getKey())->exists())->toBeTrue();
});

it('runs tracked email purge through the console command in json mode', function (): void {
    Carbon::setTestNow('2026-06-05 10:00:00');

    SentEmail::query()->create([
        'hash' => 'console-tracked-email-hash-001',
        'subject' => 'Console',
        'created_at' => Carbon::now()->subDays(61),
        'updated_at' => Carbon::now()->subDays(61),
    ]);

    $exitCode = Artisan::call('capell-email-studio:purge-tracked-emails', [
        '--days' => '60',
        '--dry-run' => true,
        '--json' => true,
    ]);

    $output = json_decode((string) Artisan::output(), associative: true, flags: JSON_THROW_ON_ERROR);

    expect($exitCode)->toBe(PurgeTrackedEmailsCommand::SUCCESS)
        ->and($output)->toBe([
            'retention_days' => 60,
            'dry_run' => true,
            'matched_emails' => 1,
            'deleted_clicks' => 0,
            'deleted_emails' => 0,
        ]);
});

it('rejects invalid tracked email purge days', function (): void {
    $this->artisan('capell-email-studio:purge-tracked-emails', ['--days' => 'never'])
        ->expectsOutput(__('capell-email-studio::package.commands.positive_integer', ['option' => '--days']))
        ->assertFailed();
});

it('registers the tracked email purge command and daily schedule', function (): void {
    $schedule = new Schedule;
    app()->instance(Schedule::class, $schedule);

    $provider = new EmailStudioServiceProvider(app());
    $provider->packageBooted();

    $event = collect($schedule->events())
        ->first(fn (mixed $scheduledEvent): bool => str_contains((string) $scheduledEvent->command, 'capell-email-studio:purge-tracked-emails'));

    expect(class_exists(PurgeTrackedEmailsCommand::class))->toBeTrue()
        ->and($event)->not->toBeNull()
        ->and($event?->expression)->toBe('0 0 * * *')
        ->and($event?->withoutOverlapping)->toBeTrue()
        ->and($event?->onOneServer)->toBeTrue();
});

it('keeps the sent email resource read only', function (): void {
    expect(SentEmailResource::canCreate())->toBeFalse()
        ->and(SentEmailResource::canEdit(new SentEmail))->toBeFalse()
        ->and(SentEmailResource::canDelete(new SentEmail))->toBeFalse();
});
