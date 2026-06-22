<?php

declare(strict_types=1);

namespace Capell\AiCreator\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class AiCreatorModelsContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
