<?php

declare(strict_types=1);

namespace Capell\Bookings\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class BookingsModelsContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
