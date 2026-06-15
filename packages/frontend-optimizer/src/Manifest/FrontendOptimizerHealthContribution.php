<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Manifest;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

final class FrontendOptimizerHealthContribution implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
