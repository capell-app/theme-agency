<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Tests\Fixtures\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Illuminate\Support\Collection;

final class FailingFixtureHealthCheck implements ChecksExtensionHealth
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
                label: 'Database table present',
                passed: true,
                message: 'Found.',
            ),
            new DoctorCheckResultData(
                label: 'Required route registered',
                passed: false,
                message: 'Missing route.',
                remediation: 'Register the route.',
            ),
        ]);
    }
}
