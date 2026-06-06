<?php

declare(strict_types=1);

use Capell\Diagnostics\Actions\Health\ExportExtensionHealthReportCsvAction;
use Capell\Diagnostics\Data\Health\ExtensionHealthReportData;
use Capell\Diagnostics\Data\Health\HealthCheckResultData;
use Capell\Diagnostics\Enums\HealthCheckImplementationStatus;
use Spatie\LaravelData\DataCollection;

it('exports a health report as support-friendly csv', function (): void {
    $report = new ExtensionHealthReportData(
        declaredCount: 2,
        implementedCount: 1,
        stubCount: 1,
        brokenCount: 0,
        executedCount: 1,
        passedCount: 1,
        failedCount: 0,
        checks: new DataCollection(HealthCheckResultData::class, [
            new HealthCheckResultData(
                packageName: 'capell-app/diagnostics',
                key: 'diagnostics.package-catalog',
                label: 'Package catalog',
                className: 'Capell\\Diagnostics\\Health\\DiagnosticsHealthCheck',
                severity: 'warning',
                implementationStatus: HealthCheckImplementationStatus::Implemented,
                passed: true,
                message: 'All 1 assertion(s) passed.',
            ),
            new HealthCheckResultData(
                packageName: 'capell-app/example',
                key: 'example.stub',
                label: 'Example stub',
                className: 'Capell\\Example\\Health\\ExampleHealthCheck',
                severity: 'critical',
                implementationStatus: HealthCheckImplementationStatus::Stub,
                passed: null,
                message: 'Check only satisfies the contract and asserts nothing.',
            ),
        ]),
    );

    $rows = array_map(str_getcsv(...), explode(PHP_EOL, ExportExtensionHealthReportCsvAction::run($report)));

    expect($rows[0])->toBe([
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
    ])
        ->and($rows[1])->toBe(['summary', '', '', '', '', '', '', '', '', '2', '1', '1', '0', '1', '1', '0'])
        ->and($rows[2][0])->toBe('check')
        ->and($rows[2][1])->toBe('capell-app/diagnostics')
        ->and($rows[2][7])->toBe('true')
        ->and($rows[3][1])->toBe('capell-app/example')
        ->and($rows[3][6])->toBe(HealthCheckImplementationStatus::Stub->value)
        ->and($rows[3][7])->toBe('');
});
