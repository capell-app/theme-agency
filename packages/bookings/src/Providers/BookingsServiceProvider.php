<?php

declare(strict_types=1);

namespace Capell\Bookings\Providers;

use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Data\Extensions\ExtensionManagementSurfaceData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Bookings\Console\ExpireBookingWorkflowStateCommand;
use Capell\Bookings\Console\InstallBookingsDemoCommand;
use Capell\Bookings\Console\PruneBookingRetentionDataCommand;
use Capell\Bookings\Console\ScheduleBookingReviewRequestsCommand;
use Capell\Bookings\Console\SendDueAppointmentRemindersCommand;
use Capell\Bookings\Contracts\BookingMessageChannel;
use Capell\Bookings\Contracts\BookingsAiAdvisor;
use Capell\Bookings\Contracts\PublicBookingRequestRenderer;
use Capell\Bookings\Contracts\SocialEventProvider;
use Capell\Bookings\Contracts\TravelTimeProvider;
use Capell\Bookings\Enums\ResourceEnum;
use Capell\Bookings\Rendering\BladePublicBookingRequestRenderer;
use Capell\Bookings\Settings\BookingsSettings;
use Capell\Bookings\Support\BookingsModelRegistrar;
use Capell\Bookings\Support\LocalBookingMessageChannel;
use Capell\Bookings\Support\LocalBookingsAiAdvisor;
use Capell\Bookings\Support\LocalTravelTimeProvider;
use Capell\Bookings\Support\NullSocialEventProvider;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Core\Support\Settings\SettingsGroupMetadata;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Override;
use Spatie\LaravelPackageTools\Package;

final class BookingsServiceProvider extends AbstractPackageServiceProvider
{
    /** @var list<string> */
    private const array PROTECTED_TABLES = [
        'booking_services',
        'booking_staff_members',
        'booking_locations',
        'booking_availability_windows',
        'booking_availability_exceptions',
        'lesson_series',
        'appointment_requests',
        'appointment_audit_logs',
        'booking_lesson_notes',
        'booking_messaging_consents',
        'booking_message_logs',
        'booking_travel_observations',
        'booking_travel_adjustments',
        'booking_work_zones',
        'booking_change_proposals',
        'booking_change_proposal_parties',
        'booking_group_sessions',
        'booking_review_requests',
        'booking_review_participants',
        'booking_owner_prompts',
        'booking_webhook_events',
        'booking_waitlist_entries',
        'booking_lesson_skill_assessments',
        'booking_lesson_bundles',
    ];

    public static string $name = 'capell-bookings';

    public static string $packageName = 'capell-app/bookings';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews(self::$name)
            ->hasCommand(InstallBookingsDemoCommand::class)
            ->hasCommand(ExpireBookingWorkflowStateCommand::class)
            ->hasCommand(PruneBookingRetentionDataCommand::class)
            ->hasCommand(ScheduleBookingReviewRequestsCommand::class)
            ->hasCommand(SendDueAppointmentRemindersCommand::class)
            ->hasMigrations([
                '2026_05_31_130000_01_create_booking_services_table',
                '2026_05_31_130000_02_create_booking_staff_members_table',
                '2026_05_31_130000_03_create_booking_locations_table',
                '2026_05_31_130000_04_create_booking_availability_windows_table',
                '2026_05_31_130000_05_create_appointment_requests_table',
                '2026_05_31_130000_06_create_booking_availability_exceptions_table',
                '2026_05_31_130000_07_create_appointment_audit_logs_table',
                '2026_06_13_000001_create_lesson_series_table',
                '2026_06_13_000002_add_adaptive_foundations_to_appointment_requests_table',
                '2026_06_13_000003_add_travel_and_payment_fields_to_bookings_tables',
                '2026_06_13_000004_create_booking_lesson_notes_table',
                '2026_06_13_000005_create_booking_messaging_tables',
                '2026_06_13_000006_create_booking_travel_tables',
                '2026_06_13_000007_create_booking_change_proposals_table',
                '2026_06_13_000008_create_booking_group_sessions_table',
                '2026_06_13_000009_add_group_session_fields_to_appointment_requests_table',
                '2026_06_13_000010_create_booking_review_requests_table',
                '2026_06_13_000011_create_booking_owner_prompts_table',
                '2026_06_13_000012_create_booking_webhook_events_table',
                '2026_06_13_000013_create_booking_waitlist_entries_table',
                '2026_06_13_000014_create_booking_lesson_skill_assessments_table',
                '2026_06_13_000015_create_booking_lesson_bundles_table',
                '2026_06_13_000016_create_booking_review_participants_table',
                '2026_06_13_000017_add_token_fields_to_booking_review_requests_table',
            ]);
    }

    public function registeringPackage(): void
    {
        $this->app->bindIf(PublicBookingRequestRenderer::class, BladePublicBookingRequestRenderer::class);
        $this->app->bindIf(BookingMessageChannel::class, LocalBookingMessageChannel::class);
        $this->app->bindIf(TravelTimeProvider::class, LocalTravelTimeProvider::class);
        $this->app->bindIf(SocialEventProvider::class, NullSocialEventProvider::class);
        $this->app->bindIf(BookingsAiAdvisor::class, LocalBookingsAiAdvisor::class);
        $this
            ->registerConfigSettings()
            ->registerSettings();

        $this->app->booted(function (): void {
            if (! $this->isPackageInstalled()) {
                return;
            }

            BookingsModelRegistrar::register();
            $this
                ->registerProtectedTables()
                ->registerAdminResources()
                ->registerReminderSchedule();
        });
    }

    public function bootingPackage(): void
    {
        RateLimiter::for('capell-bookings-request', static function (Request $request): Limit {
            $email = $request->input('customer_email');
            $normalizedEmail = is_string($email) ? strtolower($email) : '';

            return Limit::perMinute(6)
                ->by(hash('sha256', $normalizedEmail . '|' . ($request->ip() ?? 'unknown')));
        });

        RateLimiter::for('capell-bookings-webhook', static fn (Request $request): Limit => Limit::perMinute(60)->by($request->ip() ?? 'unknown'));
    }

    public function packageBooted(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../database/settings/2026_06_13_000001_create_bookings_settings.php' => database_path('settings/2026_06_13_000001_create_bookings_settings.php'),
            ], 'capell-bookings-settings');
        }
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerProtectedTables(): self
    {
        foreach (self::PROTECTED_TABLES as $tableName) {
            CapellCore::registerProtectedTable(static fn (): string => $tableName);
        }

        return $this;
    }

    private function registerAdminResources(): self
    {
        if (! class_exists(CapellAdmin::class) || ! class_exists(AdminSurfaceContributionData::class)) {
            return $this;
        }

        foreach (ResourceEnum::cases() as $resource) {
            CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::resource(
                class: $resource->value,
                group: $resource->name,
            ));
        }

        return $this;
    }

    private function registerReminderSchedule(): self
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command('capell:bookings:send-due-reminders')
                ->everyFiveMinutes()
                ->withoutOverlapping()
                ->onOneServer();

            $schedule->command('capell:bookings:expire-workflow-state')
                ->everyFiveMinutes()
                ->withoutOverlapping()
                ->onOneServer();

            $schedule->command('capell:bookings:schedule-review-requests')
                ->hourly()
                ->withoutOverlapping()
                ->onOneServer();

            $schedule->command('capell:bookings:prune-retention-data')
                ->daily()
                ->withoutOverlapping()
                ->onOneServer();
        });

        return $this;
    }

    private function registerConfigSettings(): self
    {
        $settings = Config::array('settings.settings', []);

        if (! in_array(BookingsSettings::class, $settings, true)) {
            $settings[] = BookingsSettings::class;
        }

        config(['settings.settings' => $settings]);

        return $this;
    }

    private function registerSettings(): self
    {
        $this->surface()->settingsClass(BookingsSettings::group(), BookingsSettings::class);

        if (class_exists(SettingsGroupMetadata::class)) {
            $this->surface()->settingsMetadata(new SettingsGroupMetadata(
                group: BookingsSettings::group(),
                label: 'capell-bookings::settings.title',
                icon: Heroicon::OutlinedCalendarDays,
                navigationGroup: 'capell-admin::navigation.group_system',
                navigationSort: 95,
                packageName: self::$packageName,
            ));
        }

        $this->surface()->settingsSchema(BookingsSettings::group(), BookingsSettings::schema());

        if (class_exists(ExtensionManagementSurfaceData::class)) {
            CapellAdmin::registerExtensionManagementSurface(ExtensionManagementSurfaceData::settings(
                packageName: self::$packageName,
                label: 'capell-bookings::settings.title',
                settingsGroup: BookingsSettings::group(),
                icon: Heroicon::OutlinedCalendarDays,
            ));
        }

        return $this;
    }
}
