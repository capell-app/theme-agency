<?php

declare(strict_types=1);

namespace Capell\Payments\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Payments\Actions\BuildPaymentsHealthReportAction;
use Capell\Payments\Data\PaymentsHealthReportData;
use Illuminate\Support\Collection;

final class PaymentsHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    public static function report(): PaymentsHealthReportData
    {
        return BuildPaymentsHealthReportAction::run();
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $report = self::report();

        return collect([
            new DoctorCheckResultData(
                label: 'Payments health report',
                passed: $report->status !== 'failed',
                message: $report->issues === []
                    ? 'Payments configuration, webhook events, fulfilment, and disputes are healthy.'
                    : implode(' ', $report->issues),
                remediation: $report->issues === [] ? null : 'Review Payments settings, migrations, webhook delivery, fulfilment failures, and unresolved disputes.',
            ),
        ]);
    }
}
