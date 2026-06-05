<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\GA4Reports\Contracts\GA4ReportsDataClientInterface;
use Capell\GA4Reports\Health\Ga4ReportsHealthCheck;
use Capell\GA4Reports\Models\GA4ReportsSyncRun;
use Capell\GA4Reports\Settings\GA4ReportsSettings;
use Capell\GA4Reports\Tests\Fakes\FakeGA4ReportsDataClient;
use Capell\GA4Reports\Tests\GA4ReportsTestCase;
use Illuminate\Support\Facades\Date;
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

function createGA4ReportsHealthCredentialsFile(string $prefix = 'ga4-reports-credentials'): string
{
    $credentialsPath = tempnam(sys_get_temp_dir(), $prefix);
    expect($credentialsPath)->toBeString();

    file_put_contents($credentialsPath, json_encode([
        'client_email' => 'ga4-reports@example.test',
        'private_key' => 'fake-service-account-private-key',
    ], JSON_THROW_ON_ERROR));

    return $credentialsPath;
}

it('reports a compatible capell api version', function (): void {
    expect(Ga4ReportsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning check results', function (): void {
    $results = Ga4ReportsHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(5)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when storage tables exist and the integration is disabled', function (): void {
    instanceGA4ReportsSettings(enabled: false, propertyId: '', credentialsPath: '');

    expect(Ga4ReportsHealthCheck::passed())->toBeTrue();
});

it('passes when enabled with a property id and readable credentials file', function (): void {
    $credentialsPath = createGA4ReportsHealthCredentialsFile();

    instanceGA4ReportsSettings(enabled: true, propertyId: '123456789', credentialsPath: $credentialsPath);
    app()->instance(GA4ReportsDataClientInterface::class, new FakeGA4ReportsDataClient(configured: true));
    GA4ReportsSyncRun::query()->create([
        'property_id' => '123456789',
        'status' => 'succeeded',
        'window_start' => Date::now()->subDay()->toDateString(),
        'window_end' => Date::now()->subDay()->toDateString(),
        'started_at' => Date::now()->subMinutes(5),
        'finished_at' => Date::now()->subMinute(),
    ]);

    $check = new Ga4ReportsHealthCheck;

    expect($check->configurationCheck()->passed)->toBeTrue()
        ->and($check->lastSyncRecencyCheck()->passed)->toBeTrue()
        ->and($check->dataClientReachabilityCheck()->passed)->toBeTrue()
        ->and(Ga4ReportsHealthCheck::passed())->toBeTrue();

    unlink($credentialsPath);
});

it('passes the model availability check when models are registered and storage columns exist', function (): void {
    $check = new Ga4ReportsHealthCheck;

    expect($check->missingModelClasses())->toBe([])
        ->and($check->missingStorageColumns())->toBe([])
        ->and($check->modelAvailabilityCheck()->passed)->toBeTrue();
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

it('fails the sync recency check when enabled without a successful sync', function (): void {
    $credentialsPath = createGA4ReportsHealthCredentialsFile();

    instanceGA4ReportsSettings(enabled: true, propertyId: '123456789', credentialsPath: $credentialsPath);

    $check = new Ga4ReportsHealthCheck;
    $result = $check->lastSyncRecencyCheck();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toContain('No successful');

    unlink($credentialsPath);
});

it('fails the sync recency check when the latest successful sync is stale', function (): void {
    $credentialsPath = createGA4ReportsHealthCredentialsFile();

    instanceGA4ReportsSettings(enabled: true, propertyId: '123456789', credentialsPath: $credentialsPath);
    config()->set('capell-ga4-reports.health.max_successful_sync_age_hours', 24);

    GA4ReportsSyncRun::query()->create([
        'property_id' => '123456789',
        'status' => 'succeeded',
        'window_start' => Date::now()->subDays(3)->toDateString(),
        'window_end' => Date::now()->subDays(3)->toDateString(),
        'started_at' => Date::now()->subDays(2),
        'finished_at' => Date::now()->subHours(25),
    ]);

    $check = new Ga4ReportsHealthCheck;
    $result = $check->lastSyncRecencyCheck();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toContain('older than');

    unlink($credentialsPath);
});

it('fails the data client check when the resolved client is not configured', function (): void {
    $credentialsPath = createGA4ReportsHealthCredentialsFile();

    instanceGA4ReportsSettings(enabled: true, propertyId: '123456789', credentialsPath: $credentialsPath);
    app()->instance(GA4ReportsDataClientInterface::class, new FakeGA4ReportsDataClient(configured: false));

    $check = new Ga4ReportsHealthCheck;
    $result = $check->dataClientReachabilityCheck();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toContain('not configured');

    unlink($credentialsPath);
});

it('fails the data client check when the api probe throws', function (): void {
    $credentialsPath = createGA4ReportsHealthCredentialsFile();

    instanceGA4ReportsSettings(enabled: true, propertyId: '123456789', credentialsPath: $credentialsPath);
    app()->instance(GA4ReportsDataClientInterface::class, new FakeGA4ReportsDataClient(
        configured: true,
        shouldFail: true,
    ));

    $check = new Ga4ReportsHealthCheck;
    $result = $check->dataClientReachabilityCheck();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toContain('could not complete');

    unlink($credentialsPath);
});

it('fails the configuration check when the credentials file is not service account json', function (): void {
    $credentialsPath = tempnam(sys_get_temp_dir(), 'ga4-reports-invalid-credentials');
    expect($credentialsPath)->toBeString();
    file_put_contents($credentialsPath, '{}');

    instanceGA4ReportsSettings(enabled: true, propertyId: '123456789', credentialsPath: $credentialsPath);

    $check = new Ga4ReportsHealthCheck;
    $result = $check->configurationCheck();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toContain('not valid service-account JSON');

    unlink($credentialsPath);
});

it('never leaks the property id or credentials path in check output', function (): void {
    $credentialsPath = createGA4ReportsHealthCredentialsFile('ga4-reports-secret-path');

    instanceGA4ReportsSettings(enabled: true, propertyId: '987654321', credentialsPath: $credentialsPath);
    app()->instance(GA4ReportsDataClientInterface::class, new FakeGA4ReportsDataClient(
        configured: true,
        shouldFail: true,
    ));

    $messages = Ga4ReportsHealthCheck::runDiagnostics()
        ->flatMap(static fn (DoctorCheckResultData $result): array => [$result->message, (string) $result->remediation])
        ->implode("\n");

    expect($messages)->not->toContain('987654321')
        ->and($messages)->not->toContain($credentialsPath);

    unlink($credentialsPath);
});
