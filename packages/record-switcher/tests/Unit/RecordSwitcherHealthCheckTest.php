<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\RecordSwitcher\Health\RecordSwitcherHealthCheck;

it('runs registration-aware diagnostics returning doctor check results', function (): void {
    $results = RecordSwitcherHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(4)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when the admin extender, Livewire namespace, and Filament assets are registered', function (): void {
    $check = new RecordSwitcherHealthCheck;

    expect(RecordSwitcherHealthCheck::passed())->toBeTrue()
        ->and($check->headingExtenderCheck()->passed)->toBeTrue()
        ->and($check->headingExtenderIsTagged())->toBeTrue()
        ->and($check->headingExtenderRegistrationCheck()->passed)->toBeTrue()
        ->and($check->livewireNamespaceIsRegistered())->toBeTrue()
        ->and($check->livewireNamespaceCheck()->passed)->toBeTrue()
        ->and($check->missingRegisteredAssetHandles())->toBe([])
        ->and($check->adminAssetsCheck()->passed)->toBeTrue();
});

it('reports missing asset source files', function (): void {
    $check = new RecordSwitcherHealthCheck;

    expect($check->missingAssetPaths(['resources/dist/missing-record-switcher.js' => __DIR__ . '/missing.js']))
        ->toBe(['resources/dist/missing-record-switcher.js']);
});
