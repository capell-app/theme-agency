<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\LoginAudit\Health\LoginAuditHealthCheck;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Schema;

it('reports a compatible capell api version', function (): void {
    expect(LoginAuditHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning check results', function (): void {
    $results = LoginAuditHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(3)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when the table, listeners, and activity alias are present', function (): void {
    $results = LoginAuditHealthCheck::runDiagnostics();

    expect(LoginAuditHealthCheck::passed())->toBeTrue()
        ->and($results->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue();
});

it('fails the storage table check when the login audit table is missing', function (): void {
    $tableName = config('login-audit.table_name', 'login_audit');

    Schema::drop($tableName);

    $check = new LoginAuditHealthCheck;

    expect($check->hasStorageTable())->toBeFalse()
        ->and($check->storageTableCheck()->passed)->toBeFalse()
        ->and(LoginAuditHealthCheck::passed())->toBeFalse();
});

it('fails the event listeners check when a listener is unconfigured', function (): void {
    config()->set('login-audit.listeners.login');

    $check = new LoginAuditHealthCheck;

    expect($check->missingListenerKeys())->toContain('login')
        ->and($check->eventListenersCheck()->passed)->toBeFalse()
        ->and(LoginAuditHealthCheck::passed())->toBeFalse();
});

it('fails the middleware alias check when the frontend activity alias is missing', function (): void {
    resolve(Router::class)->getMiddleware();

    $reflection = new ReflectionObject(resolve(Router::class));
    $property = $reflection->getProperty('middleware');

    $middleware = $property->getValue(resolve(Router::class));
    unset($middleware['frontend.activity']);
    $property->setValue(resolve(Router::class), $middleware);

    $check = new LoginAuditHealthCheck;

    expect($check->hasActivityMiddlewareAlias())->toBeFalse()
        ->and($check->activityMiddlewareAliasCheck()->passed)->toBeFalse()
        ->and(LoginAuditHealthCheck::passed())->toBeFalse();
});
