<?php

declare(strict_types=1);

namespace Capell\Bookings\Providers;

use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Bookings\Console\SendDueAppointmentRemindersCommand;
use Capell\Bookings\Contracts\PublicBookingRequestRenderer;
use Capell\Bookings\Enums\ResourceEnum;
use Capell\Bookings\Rendering\BladePublicBookingRequestRenderer;
use Capell\Bookings\Support\BookingsModelRegistrar;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Override;
use Spatie\LaravelPackageTools\Package;

class BookingsServiceProvider extends AbstractPackageServiceProvider
{
    /** @var list<string> */
    private const array PROTECTED_TABLES = [
        'booking_services',
        'booking_staff_members',
        'booking_locations',
        'booking_availability_windows',
        'booking_availability_exceptions',
        'appointment_requests',
        'appointment_audit_logs',
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
            ->hasCommand(SendDueAppointmentRemindersCommand::class)
            ->hasMigrations([
                '2026_05_31_130000_01_create_booking_services_table',
                '2026_05_31_130000_02_create_booking_staff_members_table',
                '2026_05_31_130000_03_create_booking_locations_table',
                '2026_05_31_130000_04_create_booking_availability_windows_table',
                '2026_05_31_130000_05_create_appointment_requests_table',
                '2026_05_31_130000_06_create_booking_availability_exceptions_table',
                '2026_05_31_130000_07_create_appointment_audit_logs_table',
            ]);
    }

    public function registeringPackage(): void
    {
        $this->app->bindIf(PublicBookingRequestRenderer::class, BladePublicBookingRequestRenderer::class);

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
            $email = strtolower((string) $request->input('customer_email', ''));

            return Limit::perMinute(6)
                ->by(hash('sha256', $email . '|' . (string) $request->ip()));
        });
    }

    public function packageBooted(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(static::$packageName);
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
        });

        return $this;
    }
}
