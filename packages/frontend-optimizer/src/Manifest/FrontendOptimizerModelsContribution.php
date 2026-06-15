<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Manifest;

use Capell\Core\Contracts\Extensions\ExtensionContribution;

final class FrontendOptimizerModelsContribution implements ExtensionContribution
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
