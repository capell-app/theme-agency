<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InertiaBookings\Health;

use Capell\Bookings\Contracts\PublicBookingRequestRenderer;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Inertia\Facades\CapellInertia;
use Capell\Inertia\Support\CapellInertiaManager;
use Capell\ThemeStudio\InertiaBookings\Providers\InertiaBookingsThemeServiceProvider;
use Capell\ThemeStudio\InertiaBookings\Rendering\InertiaPublicBookingRequestRenderer;

final class ThemeInertiaBookingsHealthCheck implements ChecksExtensionHealth
{
    /**
     * @var array<string, string>
     */
    private const array ADAPTER_PACKAGES = [
        'react' => 'capell-app/inertia-react-adapter',
        'vue' => 'capell-app/inertia-vue-adapter',
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    public function passes(): bool
    {
        return $this->inertiaBridgeAvailable()
            && $this->configuredAdapterInstalled()
            && $this->themeRegistered()
            && $this->bookingRendererBound();
    }

    public function inertiaBridgeAvailable(): bool
    {
        return class_exists(CapellInertia::class) && class_exists(CapellInertiaManager::class);
    }

    public function configuredAdapterInstalled(): bool
    {
        $configuredAdapter = config('capell-inertia.adapter', 'vue');
        $adapter = is_string($configuredAdapter) ? $configuredAdapter : 'vue';
        $packageName = self::ADAPTER_PACKAGES[$adapter] ?? null;

        return is_string($packageName) && CapellCore::isPackageInstalled($packageName);
    }

    public function themeRegistered(): bool
    {
        if (! app()->bound(ThemeRegistry::class)) {
            return false;
        }

        $registry = resolve(ThemeRegistry::class);

        return $registry->has(InertiaBookingsThemeServiceProvider::THEME_KEY)
            && $registry->definition(InertiaBookingsThemeServiceProvider::THEME_KEY)->runtime === FrontendRuntime::Inertia;
    }

    public function bookingRendererBound(): bool
    {
        return app()->make(PublicBookingRequestRenderer::class) instanceof InertiaPublicBookingRequestRenderer;
    }
}
