<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\GA4Reports\Health\Ga4ReportsHealthCheck;
use Capell\GA4Reports\Models\GA4ReportsSyncRun;
use Capell\GA4Reports\Settings\GA4ReportsSettings;
use Capell\GA4Reports\Tests\GA4ReportsTestCase;
use Illuminate\Support\Facades\Schema;

uses(GA4ReportsTestCase::class);

function instanceGA4ReportsSettings(bool $enabled, string $propertyId, string $credentialsPath): void
{
    $settings = new GA4ReportsSettings;
    $settings->enabled = $enabled;
    $settings->property_id = $propertyId;
    $settings->credentials_path = $credentialsPath;
    $settings->sync_days = 30;
    $settings->route_slug = 'ga4-reports';

    app()->instance(GA4ReportsSettings::class, $settings);
}

it('reports a compatible capell api version', function (): void {
    expect(Ga4ReportsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning check results', function (): void {
    $results = Ga4ReportsHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(2)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when storage tables exist and the integration is disabled', function (): void {
    instanceGA4ReportsSettings(enabled: false, propertyId: '', credentialsPath: '');

    expect(Ga4ReportsHealthCheck::passed())->toBeTrue();
});

it('passes when enabled with a property id and readable credentials file', function (): void {
    $credentialsPath = tempnam(sys_get_temp_dir(), 'ga4-reports-credentials');
    expect($credentialsPath)->toBeString();

    instanceGA4ReportsSettings(enabled: true, propertyId: '123456789', credentialsPath: $credentialsPath);

    $check = new Ga4ReportsHealthCheck;

    expect($check->configurationCheck()->passed)->toBeTrue()
        ->and(Ga4ReportsHealthCheck::passed())->toBeTrue();

    unlink($credentialsPath);
});

it('fails the storage table check when a reporting table is missing', function (): void {
    Schema::drop((new GA4ReportsSyncRun)->getTable());

    $check = new Ga4ReportsHealthCheck;

    expect($check->missingTables())->toContain((new GA4ReportsSyncRun)->getTable())
        ->and($check->storageTablesCheck()->passed)->toBeFalse()
        ->and(Ga4ReportsHealthCheck::passed())->toBeFalse();
});

it('fails the configuration check when enabled without required settings', function (): void {
    instanceGA4ReportsSettings(enabled: true, propertyId: '', credentialsPath: '');

    $check = new Ga4ReportsHealthCheck;
    $result = $check->configurationCheck();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toContain('property ID')
        ->and($result->message)->toContain('credentials path');
});

it('fails the configuration check when the credentials file is not readable', function (): void {
    instanceGA4ReportsSettings(enabled: true, propertyId: '123456789', credentialsPath: '/tmp/ga4-reports-missing-credentials.json');

    $check = new Ga4ReportsHealthCheck;
    $result = $check->configurationCheck();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toContain('not readable');
});

it('never leaks the property id or credentials path in check output', function (): void {
    $credentialsPath = tempnam(sys_get_temp_dir(), 'ga4-reports-secret-path');
    expect($credentialsPath)->toBeString();

    instanceGA4ReportsSettings(enabled: true, propertyId: '987654321', credentialsPath: $credentialsPath);

    $messages = Ga4ReportsHealthCheck::runDiagnostics()
        ->flatMap(static fn (DoctorCheckResultData $result): array => [$result->message, (string) $result->remediation])
        ->implode("\n");

    expect($messages)->not->toContain('987654321')
        ->and($messages)->not->toContain($credentialsPath);

    unlink($credentialsPath);
});
