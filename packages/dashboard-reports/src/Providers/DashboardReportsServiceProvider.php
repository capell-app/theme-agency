<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Providers;

use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Spatie\LaravelPackageTools\Package;

final class DashboardReportsServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-dashboard-reports';

    public static string $packageName = 'capell-app/dashboard-reports';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile(self::$name)
            ->hasTranslations()
            ->hasViews(self::$name);
    }

    public function registeringPackage(): void
    {
        $this->app->register(AdminServiceProvider::class);
    }

    public function packageRegistered(): void {}
}
