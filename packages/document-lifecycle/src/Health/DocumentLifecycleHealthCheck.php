<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\DocumentLifecycle\Actions\BuildDocumentLifecycleHealthReportAction;
use Capell\DocumentLifecycle\Data\DocumentLifecycleHealthReportData;
use Illuminate\Support\Collection;

final class DocumentLifecycleHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    public static function report(): DocumentLifecycleHealthReportData
    {
        return BuildDocumentLifecycleHealthReportAction::run();
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $report = self::report();

        return collect([
            new DoctorCheckResultData(
                label: 'Document Lifecycle health report',
                passed: $report->status === 'passed',
                message: $report->issues === []
                    ? 'Document Lifecycle tables, morph aliases, protected tables, and revision listener are ready.'
                    : implode(' ', $report->issues),
                remediation: $report->issues === [] ? null : 'Run the Document Lifecycle migrations and verify package provider registration.',
            ),
        ]);
    }
}
