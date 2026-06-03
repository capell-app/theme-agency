<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Tests\Fixtures\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;

/**
 * A health check that satisfies the contract but asserts nothing — the no-op
 * pattern this package is designed to surface.
 */
final class StubFixtureHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }
}
