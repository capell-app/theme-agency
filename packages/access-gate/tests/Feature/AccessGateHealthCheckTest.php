<?php

declare(strict_types=1);

use Capell\AccessGate\Health\AccessGateHealthCheck;
use Capell\AccessGate\Support\AccessGateDiagnosticsService;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Illuminate\Routing\Router;

it('reports compatible capell api version', function (): void {
    expect(AccessGateHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs all diagnostics checks and returns doctor check results', function (): void {
    $checks = AccessGateHealthCheck::runDiagnostics();

    expect($checks)->toHaveCount(5)
        ->and($checks->every(fn (DoctorCheckResultData $check): bool => $check->label !== ''))->toBeTrue()
        ->and($checks->every(fn (DoctorCheckResultData $check): bool => $check->message !== ''))->toBeTrue();
});

it('reports passed when all checks succeed', function (): void {
    expect(AccessGateHealthCheck::passed())->toBeTrue();
});

it('delegates to the shared diagnostics service', function (): void {
    $service = resolve(AccessGateDiagnosticsService::class);
    $router = resolve(Router::class);

    $serviceChecks = $service->runAllChecks($router);
    $healthChecks = AccessGateHealthCheck::runDiagnostics();

    expect($healthChecks)->toHaveCount($serviceChecks->count())
        ->and($healthChecks->pluck('label')->all())->toBe($serviceChecks->pluck('label')->all());
});

it('reports database check as passed when tables exist', function (): void {
    $service = resolve(AccessGateDiagnosticsService::class);
    $result = $service->checkDatabase();

    expect($result->passed)->toBeTrue()
        ->and($result->label)->toBe('Access Gate database');
});

it('reports middleware check result', function (): void {
    $service = resolve(AccessGateDiagnosticsService::class);
    $router = resolve(Router::class);
    $result = $service->checkMiddleware($router);

    expect($result->label)->toBe('Access Gate middleware')
        ->and($result->message)->toBeString();
});

it('reports cookies check as passed with default config', function (): void {
    $service = resolve(AccessGateDiagnosticsService::class);
    $result = $service->checkCookies();

    expect($result->passed)->toBeTrue()
        ->and($result->label)->toBe('Access Gate cookies');
});
