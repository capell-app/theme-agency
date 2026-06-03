<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Tests\Fixtures\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Illuminate\Support\Collection;

final class PassingFixtureHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        return collect([
            new DoctorCheckResultData(
                label: 'Everything fine',
                passed: true,
                message: 'All good.',
            ),
        ]);
    }
}
