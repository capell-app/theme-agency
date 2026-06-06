<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions\Health;

use Capell\Diagnostics\Data\Health\ExtensionHealthReportData;
use Capell\Diagnostics\Data\Health\HealthCheckResultData;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

final class ExportExtensionHealthReportCsvAction
{
    use AsAction;

    public function handle(ExtensionHealthReportData $report): string
    {
        $stream = fopen('php://temp', 'r+');

        throw_unless(is_resource($stream), RuntimeException::class, 'Unable to open temporary CSV stream.');

        fputcsv($stream, [
            'type',
            'package',
            'key',
            'label',
            'class',
            'severity',
            'implementation',
            'passed',
            'message',
            'declared',
            'implemented',
            'stub',
            'broken',
            'executed',
            'passed_count',
            'failed_count',
        ]);

        fputcsv($stream, [
            'summary',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            (string) $report->declaredCount,
            (string) $report->implementedCount,
            (string) $report->stubCount,
            (string) $report->brokenCount,
            (string) $report->executedCount,
            (string) $report->passedCount,
            (string) $report->failedCount,
        ]);

        $report->checks
            ->toCollection()
            ->each(function (HealthCheckResultData $check) use ($stream): void {
                fputcsv($stream, [
                    'check',
                    $check->packageName,
                    $check->key,
                    $check->label,
                    $check->className,
                    $check->severity,
                    $check->implementationStatus->value,
                    $this->csvBoolean($check->passed),
                    $check->message ?? '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ]);
            });

        rewind($stream);

        $contents = stream_get_contents($stream);

        fclose($stream);

        return is_string($contents) ? rtrim($contents) : '';
    }

    private function csvBoolean(?bool $value): string
    {
        return match ($value) {
            true => 'true',
            false => 'false',
            null => '',
        };
    }
}
