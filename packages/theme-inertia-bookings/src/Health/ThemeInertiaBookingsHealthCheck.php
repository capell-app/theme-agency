<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InertiaBookings\Health;

use Capell\Bookings\Contracts\PublicBookingRequestRenderer;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\InertiaBookings\Providers\InertiaBookingsThemeServiceProvider;
use Capell\ThemeStudio\InertiaBookings\Rendering\InertiaPublicBookingRequestRenderer;

final class ThemeInertiaBookingsHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    public function passes(): bool
    {
        return $this->themeRegistered() && $this->bookingRendererBound();
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
