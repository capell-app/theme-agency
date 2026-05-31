<?php

declare(strict_types=1);

namespace Capell\Bookings\Providers;

use Capell\Bookings\Support\BookingsModelRegistrar;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Override;
use Spatie\LaravelPackageTools\Package;

class BookingsServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-bookings';

    public static string $packageName = 'capell-app/bookings';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasTranslations()
            ->hasMigrations([
                '2026_05_31_130000_01_create_booking_services_table',
                '2026_05_31_130000_02_create_booking_staff_members_table',
                '2026_05_31_130000_03_create_booking_locations_table',
                '2026_05_31_130000_04_create_booking_availability_windows_table',
                '2026_05_31_130000_05_create_appointment_requests_table',
            ]);
    }

    public function registeringPackage(): void
    {
        $this->app->booted(function (): void {
            if (! $this->isPackageInstalled()) {
                return;
            }

            BookingsModelRegistrar::register();
        });
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::getPackage(static::$packageName)->isInstalled();
    }
}
