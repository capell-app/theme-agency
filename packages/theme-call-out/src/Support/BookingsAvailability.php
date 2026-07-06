<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CallOut\Support;

use Capell\Core\Facades\CapellCore;

/**
 * Provider-resolved availability check for the optional `capell-app/bookings`
 * package, mirroring `Capell\FoundationTheme\Support\NavigationAvailability`'s
 * shape exactly.
 *
 * Consumed only from real PHP (class-based Blade components, Actions), never
 * from a public `@php` Blade block.
 *
 * When bookings is not installed, Call Out's quote-path stepper and
 * emergency-availability banner fall back to a plain "Call now" phone CTA
 * instead of a real booking-slot picker.
 */
final class BookingsAvailability
{
    private const string PACKAGE_NAME = 'capell-app/bookings';

    public static function check(): bool
    {
        return CapellCore::isPackageInstalled(self::PACKAGE_NAME);
    }
}
