<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Tests\Fixtures\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Illuminate\Support\Collection;
use RuntimeException;

final class ThrowingFixtureHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, mixed>
     */
    public static function runDiagnostics(): Collection
    {
        throw new RuntimeException('check exploded');
    }
}
