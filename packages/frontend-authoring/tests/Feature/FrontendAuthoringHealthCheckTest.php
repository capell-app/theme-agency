<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\FrontendAuthoring\Health\FrontendAuthoringHealthCheck;
use Capell\FrontendAuthoring\Support\EditableRegionSigner;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

it('reports a compatible capell api version', function (): void {
    expect(FrontendAuthoringHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning check results', function (): void {
    $results = FrontendAuthoringHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(4)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when the route, bindings, configuration, and signing secret are present', function (): void {
    $check = new FrontendAuthoringHealthCheck;

    expect(FrontendAuthoringHealthCheck::passed())->toBeTrue()
        ->and($check->isBeaconRouteRegistered())->toBeTrue()
        ->and($check->unresolvableBindings())->toBe([])
        ->and($check->isConfigurationReadable())->toBeTrue()
        ->and($check->hasSigningSecret())->toBeTrue();
});

it('fails the signing secret check when no application key is configured', function (): void {
    Config::set('app.key', null);

    $check = new FrontendAuthoringHealthCheck;

    expect($check->hasSigningSecret())->toBeFalse()
        ->and($check->signingSecretCheck()->passed)->toBeFalse()
        ->and(FrontendAuthoringHealthCheck::passed())->toBeFalse();
});

it('confirms the authoring service bindings are resolvable', function (): void {
    $check = new FrontendAuthoringHealthCheck;

    expect($check->unresolvableBindings())->toBe([])
        ->and($check->serviceBindingsCheck()->passed)->toBeTrue();
});

it('fails the service bindings check when a required surface cannot resolve', function (): void {
    app()->instance(EditableRegionSigner::class, new stdClass);

    $check = new FrontendAuthoringHealthCheck;

    expect($check->unresolvableBindings())->toBe([EditableRegionSigner::class])
        ->and($check->serviceBindingsCheck()->passed)->toBeFalse()
        ->and($check->serviceBindingsCheck()->message)->toContain(EditableRegionSigner::class)
        ->and(FrontendAuthoringHealthCheck::passed())->toBeFalse();
});

it('fails the configuration check when the enabled flag is not readable', function (): void {
    Config::set('capell-frontend-authoring', []);

    $check = new FrontendAuthoringHealthCheck;

    expect($check->isConfigurationReadable())->toBeFalse()
        ->and($check->configurationCheck()->passed)->toBeFalse()
        ->and(FrontendAuthoringHealthCheck::passed())->toBeFalse();
});

it('confirms the beacon route is registered', function (): void {
    $check = new FrontendAuthoringHealthCheck;

    expect($check->isBeaconRouteRegistered())->toBeTrue()
        ->and($check->beaconRouteCheck()->passed)->toBeTrue();
});

it('fails the beacon route check when the package route is unavailable', function (): void {
    $registeredRoutes = Route::getRoutes();

    if (! $registeredRoutes instanceof RouteCollection) {
        throw new RuntimeException('Expected the Laravel router to expose a concrete route collection.');
    }

    Route::setRoutes(new RouteCollection);

    try {
        $check = new FrontendAuthoringHealthCheck;

        expect($check->isBeaconRouteRegistered())->toBeFalse()
            ->and($check->beaconRouteCheck()->passed)->toBeFalse()
            ->and(FrontendAuthoringHealthCheck::passed())->toBeFalse();
    } finally {
        Route::setRoutes($registeredRoutes);
    }
});
