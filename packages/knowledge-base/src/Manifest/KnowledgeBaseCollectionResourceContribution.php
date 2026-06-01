<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;

final class KnowledgeBaseCollectionResourceContribution implements ExtensionContribution, RegistersExtensionAdminResource
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
