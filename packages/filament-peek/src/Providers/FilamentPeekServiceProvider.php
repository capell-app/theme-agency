<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Providers;

use Capell\Admin\Contracts\Extenders\AdminPanelExtender;
use Capell\Admin\Contracts\Extenders\PagePreviewActionExtender;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\FilamentPeek\Filament\Extenders\FilamentPeekPanelExtender;
use Capell\FilamentPeek\Filament\Extenders\PagePeekPreviewActionExtender;
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
        if (! $this->isDiscoveringPackages() && config('capell-filament-peek.enabled', true)) {
            $this->app->tag([FilamentPeekPanelExtender::class], AdminPanelExtender::TAG);
        }

        $this->app->booted(function (): void {
            if ($this->isDiscoveringPackages()) {
                return;
            }

            if (! $this->shouldRegisterRuntime()) {
                return;
            }

            $this->app->tag([PagePeekPreviewActionExtender::class], PagePreviewActionExtender::TAG);
        });
    }

    public function packageRegistered(): void
    {
        $this->configureUpstreamPreviewModal();
    }

    private function shouldRegisterRuntime(): bool
    {
        if (! config('capell-filament-peek.enabled', true)) {
            return false;
        }

        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function configureUpstreamPreviewModal(): void
    {
        $devicePresets = config('capell-filament-peek.preview.device_presets', false);

        config()->set('filament-peek.devicePresets', is_array($devicePresets) ? $devicePresets : false);
        config()->set('filament-peek.initialDevicePreset', $this->configString('capell-filament-peek.preview.initial_device_preset', 'fullscreen'));
        config()->set('filament-peek.allowIframeOverflow', $this->configBool('capell-filament-peek.preview.allow_iframe_overflow', false));
        config()->set('filament-peek.allowIframePointerEvents', $this->configBool('capell-filament-peek.preview.allow_iframe_pointer_events', false));
    }

    private function configString(string $key, string $default): string
    {
        $value = config($key, $default);

        return is_string($value) && $value !== '' ? $value : $default;
    }

    private function configBool(string $key, bool $default): bool
    {
        $value = config($key, $default);

        return is_bool($value) ? $value : $default;
    }
}
