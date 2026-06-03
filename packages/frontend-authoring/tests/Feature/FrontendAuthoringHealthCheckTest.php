<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\FrontendAuthoring\Health\FrontendAuthoringHealthCheck;
use Illuminate\Support\Facades\Config;

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
    Config::set('app.key');

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

it('confirms the beacon route is registered', function (): void {
    $check = new FrontendAuthoringHealthCheck;

    expect($check->isBeaconRouteRegistered())->toBeTrue()
        ->and($check->beaconRouteCheck()->passed)->toBeTrue();
});
