<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InertiaBookingsVue\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class InertiaBookingsVueHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    public function passes(): bool
    {
        return is_file(__DIR__ . '/../../resources/js/Pages/Capell/Page.vue')
            && is_file(__DIR__ . '/../../resources/js/Pages/Capell/Bookings/Request.vue');
    }
}
