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

    /**
     * @var array<string, array{package: string, health: class-string}>
     */
    private const array ADAPTER_COMPONENT_PACKS = [
        'react' => [
            'package' => 'capell-app/theme-inertia-bookings-react',
            'health' => 'Capell\\ThemeStudio\\InertiaBookingsReact\\Health\\InertiaBookingsReactHealthCheck',
        ],
        'vue' => [
            'package' => 'capell-app/theme-inertia-bookings-vue',
            'health' => 'Capell\\ThemeStudio\\InertiaBookingsVue\\Health\\InertiaBookingsVueHealthCheck',
        ],
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    public function passes(): bool
    {
        return $this->inertiaBridgeAvailable()
            && $this->configuredAdapterInstalled()
            && $this->configuredAdapterBookingComponentAvailable()
            && $this->themeRegistered()
            && $this->bookingRendererBound();
    }

    public function inertiaBridgeAvailable(): bool
    {
        return class_exists(CapellInertia::class) && class_exists(CapellInertiaManager::class);
    }

    public function configuredAdapterInstalled(): bool
    {
        $packageName = self::ADAPTER_PACKAGES[$this->configuredAdapter()] ?? null;

        return is_string($packageName) && CapellCore::isPackageInstalled($packageName);
    }

    public function configuredAdapterBookingComponentAvailable(): bool
    {
        $componentPack = self::ADAPTER_COMPONENT_PACKS[$this->configuredAdapter()] ?? null;

        if (! is_array($componentPack)) {
            return false;
        }

        $packageName = $componentPack['package'];
        $healthClass = $componentPack['health'];

        if (! CapellCore::isPackageInstalled($packageName) || ! class_exists($healthClass)) {
            return false;
        }

        $healthCheck = new $healthClass;

        return method_exists($healthCheck, 'passes') && $healthCheck->passes();
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

    private function configuredAdapter(): string
    {
        $configuredAdapter = config('capell-inertia.adapter', 'vue');

        return is_string($configuredAdapter) ? $configuredAdapter : 'vue';
    }
}
