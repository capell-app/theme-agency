<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InertiaBookings\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class ThemeManagementPageContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    public static function themeKey(): string
    {
        return 'inertia-bookings';
    }
}
