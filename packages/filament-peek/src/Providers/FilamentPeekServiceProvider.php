<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Providers;

use Capell\Admin\Contracts\Extenders\AdminPanelExtender;
use Capell\Admin\Contracts\Extenders\ResourceHeaderActionExtender;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\FilamentPeek\Filament\Extenders\FilamentPeekPanelExtender;
use Capell\FilamentPeek\Filament\Extenders\PagePeekPreviewHeaderActionExtender;
use Spatie\LaravelPackageTools\Package;

final class FilamentPeekServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-filament-peek';

    public static string $packageName = 'capell-app/filament-peek';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews()
            ->hasRoute('web');
    }

    public function registeringPackage(): void
    {
        $this->app->booted(function (): void {
            if ($this->isDiscoveringPackages()) {
                return;
            }

            if (! $this->shouldRegisterRuntime()) {
                return;
            }

            $this->registerAdminExtenders();
        });
    }

    private function shouldRegisterRuntime(): bool
    {
        if (! config('capell-filament-peek.enabled', true)) {
            return false;
        }

        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerAdminExtenders(): void
    {
        $this->app->tag([FilamentPeekPanelExtender::class], AdminPanelExtender::TAG);
        $this->app->tag([PagePeekPreviewHeaderActionExtender::class], ResourceHeaderActionExtender::TAG);
    }
}
