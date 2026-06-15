<?php

declare(strict_types=1);

use Capell\AccessGate\Health\AccessGateHealthCheck;
use Capell\AccessGate\Providers\AccessGateServiceProvider;
use Capell\AccessGate\Support\AccessGateDiagnosticsService;
use Capell\AccessGate\Tests\Fixtures\Autoload\PublicRequestProviderField;
use Capell\AccessGate\Tests\Fixtures\Autoload\PublicRequestProviderMethod;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Facades\CapellCore;
use Capell\CustomerPortal\Support\PortalSelfServiceItemRegistry;
use Capell\Frontend\Support\Render\FrontendHookRegistrar;
use Capell\Frontend\Support\Render\RenderHookRegistry;
use Illuminate\Routing\Router;

it('reports compatible capell api version', function (): void {
    expect(AccessGateHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs all diagnostics checks and returns doctor check results', function (): void {
    $checks = AccessGateHealthCheck::runDiagnostics();

    expect($checks)->toHaveCount(10)
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

it('reports registration configuration diagnostics for methods and fields', function (): void {
    config()->set('access-gate.registration.identity_methods', [
        PublicRequestProviderMethod::class,
    ]);
    config()->set('access-gate.registration.fields', [
        PublicRequestProviderField::class,
    ]);

    $provider = new AccessGateServiceProvider(app());
    accessGateHealthInvokeProviderMethod($provider, 'registerConfiguredAccessRequestMethods');
    accessGateHealthInvokeProviderMethod($provider, 'registerConfiguredRegistrationFields');

    $service = resolve(AccessGateDiagnosticsService::class);
    $result = $service->checkRegistrationConfiguration();

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toContain('provider')
        ->and($result->message)->toContain('provider_username');
});

it('reports invalid registration method and field configuration', function (): void {
    config()->set('access-gate.registration.identity_methods', [
        stdClass::class,
    ]);
    config()->set('access-gate.registration.fields', [
        'missing-field',
    ]);

    $result = resolve(AccessGateDiagnosticsService::class)->checkRegistrationConfiguration();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toContain(stdClass::class)
        ->and($result->message)->toContain('missing-field');
});

it('reports public route throttle registration', function (): void {
    $service = resolve(AccessGateDiagnosticsService::class);
    $router = resolve(Router::class);
    $result = $service->checkRouteThrottles($router);

    expect($result->passed)->toBeTrue()
        ->and($result->label)->toBe('Access Gate route throttles');
});

it('reports installed payment, customer portal, and cache-safe announcement bridge diagnostics', function (): void {
    CapellCore::forcePackageInstalled(AccessGateServiceProvider::$packageName);

    $registry = new RenderHookRegistry;
    app()->instance(RenderHookRegistry::class, $registry);
    app()->instance(FrontendHookRegistrar::class, new FrontendHookRegistrar($registry));
    app()->singleton(PortalSelfServiceItemRegistry::class);

    $provider = new AccessGateServiceProvider(app());
    accessGateHealthInvokeProviderMethod($provider, 'registerPaymentFulfillmentHandler');
    accessGateHealthInvokeProviderMethod($provider, 'registerCustomerPortalIntegrations');
    accessGateHealthInvokeProviderMethod($provider, 'registerFrontendRenderHooks');

    $service = resolve(AccessGateDiagnosticsService::class);

    expect($service->checkPaymentFulfillmentHandler()->passed)->toBeTrue()
        ->and($service->checkCustomerPortalProvider()->passed)->toBeTrue()
        ->and($service->checkAnnouncementHookCacheSafety()->passed)->toBeTrue();
});

it('reports missing cache-safe announcement hook metadata when installed without a contribution', function (): void {
    CapellCore::forcePackageInstalled(AccessGateServiceProvider::$packageName);
    app()->instance(RenderHookRegistry::class, new RenderHookRegistry);

    $result = resolve(AccessGateDiagnosticsService::class)->checkAnnouncementHookCacheSafety();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toContain('announcement hook');
});

function accessGateHealthInvokeProviderMethod(AccessGateServiceProvider $provider, string $method): mixed
{
    $reflectionMethod = new ReflectionMethod($provider, $method);

    return $reflectionMethod->invoke($provider);
}
