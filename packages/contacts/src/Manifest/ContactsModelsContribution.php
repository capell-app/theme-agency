<?php

declare(strict_types=1);

namespace Capell\Contacts\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class ContactsModelsContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
