<?php

declare(strict_types=1);

namespace Capell\Contacts\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class ContactsHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
