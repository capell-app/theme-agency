<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InertiaBookings\Health;

use Capell\Bookings\Contracts\PublicBookingRequestRenderer;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Inertia\Facades\CapellInertia;
use Capell\Inertia\Support\CapellInertiaManager;
use Capell\ThemeStudio\InertiaBookings\Providers\InertiaBookingsThemeServiceProvider;
use Capell\ThemeStudio\InertiaBookings\Rendering\InertiaPublicBookingRequestRenderer;
use Illuminate\Support\Collection;

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

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $healthCheck = new self;

        return collect([
            new DoctorCheckResultData(
                label: 'Inertia bridge',
                passed: $healthCheck->inertiaBridgeAvailable(),
                message: $healthCheck->inertiaBridgeAvailable()
                    ? 'The Capell Inertia bridge is available.'
                    : 'The Capell Inertia bridge classes are missing.',
                remediation: $healthCheck->inertiaBridgeAvailable() ? null : 'Install capell-app/inertia before enabling this theme.',
            ),
            new DoctorCheckResultData(
                label: 'Configured Inertia adapter',
                passed: $healthCheck->configuredAdapterInstalled(),
                message: $healthCheck->configuredAdapterInstalled()
                    ? sprintf('The configured [%s] Inertia adapter package is installed.', $healthCheck->configuredAdapter())
                    : sprintf('The configured [%s] Inertia adapter package is missing.', $healthCheck->configuredAdapter()),
                remediation: $healthCheck->configuredAdapterInstalled() ? null : 'Install the matching Inertia adapter package or change capell-inertia.adapter.',
            ),
            new DoctorCheckResultData(
                label: 'Booking adapter component pack',
                passed: $healthCheck->configuredAdapterBookingComponentAvailable(),
                message: $healthCheck->configuredAdapterBookingComponentAvailable()
                    ? 'The configured adapter booking component pack is healthy.'
                    : 'The configured adapter booking component pack is missing or unhealthy.',
                remediation: $healthCheck->configuredAdapterBookingComponentAvailable() ? null : 'Install and verify the matching theme-inertia-bookings adapter package.',
            ),
            new DoctorCheckResultData(
                label: 'Inertia bookings theme registration',
                passed: $healthCheck->themeRegistered(),
                message: $healthCheck->themeRegistered()
                    ? 'The Inertia bookings theme is registered with the Inertia runtime.'
                    : 'The Inertia bookings theme is not registered with the Inertia runtime.',
                remediation: $healthCheck->themeRegistered() ? null : 'Verify the theme service provider is loaded.',
            ),
            new DoctorCheckResultData(
                label: 'Public booking renderer',
                passed: $healthCheck->bookingRendererBound(),
                message: $healthCheck->bookingRendererBound()
                    ? 'The public booking request renderer is bound to the Inertia renderer.'
                    : 'The public booking request renderer is not bound to the Inertia renderer.',
                remediation: $healthCheck->bookingRendererBound() ? null : 'Verify the theme service provider registers the booking renderer binding.',
            ),
        ]);
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

        return $healthCheck->passes();
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

    public function configuredAdapter(): string
    {
        $configuredAdapter = config('capell-inertia.adapter', 'vue');

        return is_string($configuredAdapter) ? $configuredAdapter : 'vue';
    }
}
