<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Hero\Health\HeroHealthCheck;
use Illuminate\Support\Facades\View;

it('reports a compatible capell api version', function (): void {
    expect(HeroHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning doctor check results', function (): void {
    $results = HeroHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(2)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when the hero widget component and view namespace are registered', function (): void {
    $check = new HeroHealthCheck;

    expect(HeroHealthCheck::passed())->toBeTrue()
        ->and($check->isWidgetComponentRegistered())->toBeTrue()
        ->and($check->widgetComponentCheck()->passed)->toBeTrue()
        ->and($check->viewNamespaceResolves())->toBeTrue()
        ->and($check->viewNamespaceCheck()->passed)->toBeTrue();
});

it('fails the view namespace check when the hero views cannot be resolved', function (): void {
    View::replaceNamespace('capell-hero', sys_get_temp_dir() . '/capell-hero-missing-' . uniqid());

    $check = new HeroHealthCheck;

    expect($check->viewNamespaceResolves())->toBeFalse()
        ->and($check->viewNamespaceCheck()->passed)->toBeFalse()
        ->and($check->viewNamespaceCheck()->remediation)->not->toBeNull()
        ->and(HeroHealthCheck::passed())->toBeFalse();
});
