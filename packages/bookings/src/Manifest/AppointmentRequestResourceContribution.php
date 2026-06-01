<?php

declare(strict_types=1);

namespace Capell\Bookings\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;

final class AppointmentRequestResourceContribution implements ExtensionContribution, RegistersExtensionAdminResource
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
