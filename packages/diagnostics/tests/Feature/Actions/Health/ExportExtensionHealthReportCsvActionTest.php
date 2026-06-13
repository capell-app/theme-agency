<?php

declare(strict_types=1);

use Capell\Diagnostics\Actions\Health\ExportExtensionHealthReportCsvAction;
use Capell\Diagnostics\Data\Health\ExtensionHealthReportData;
use Capell\Diagnostics\Data\Health\HealthCheckResultData;
use Capell\Diagnostics\Enums\HealthCheckImplementationStatus;
use Capell\Diagnostics\Health\DiagnosticsHealthCheck;
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
                className: DiagnosticsHealthCheck::class,
                severity: 'warning',
                surface: 'admin',
                coverage: ['manifest-validity', 'provider-registration'],
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
                surface: 'frontend',
                coverage: ['manifest-validity', 'public-route-security'],
                implementationStatus: HealthCheckImplementationStatus::Stub,
                passed: null,
                message: 'Check only satisfies the contract and asserts nothing.',
            ),
        ]),
        overallStatus: 'degraded',
        healthScore: 95,
    );

    $rows = array_map(str_getcsv(...), explode(PHP_EOL, (string) ExportExtensionHealthReportCsvAction::run($report)));

    expect($rows[0])->toBe([
        'type',
        'status',
        'score',
        'worst_severity',
        'package',
        'key',
        'label',
        'class',
        'severity',
        'surface',
        'coverage',
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
        ->and($rows[1])->toBe(['summary', 'degraded', '95', '', '', '', '', '', '', '', '', '', '', '', '2', '1', '1', '0', '1', '1', '0'])
        ->and($rows[2][0])->toBe('check')
        ->and($rows[2][4])->toBe('capell-app/diagnostics')
        ->and($rows[2][9])->toBe('admin')
        ->and($rows[2][10])->toBe('manifest-validity|provider-registration')
        ->and($rows[2][12])->toBe('true')
        ->and($rows[3][4])->toBe('capell-app/example')
        ->and($rows[3][11])->toBe(HealthCheckImplementationStatus::Stub->value)
        ->and($rows[3][12])->toBe('');
});
