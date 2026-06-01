<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class KnowledgeBaseModelsContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
