<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\PrivacyCenter\Health\PrivacyCenterHealthCheck;
use Capell\PrivacyCenter\Tests\PrivacyCenterTestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

require_once dirname(__DIR__) . '/autoload.php';

uses(PrivacyCenterTestCase::class);

it('reports a compatible capell api version', function (): void {
    expect(PrivacyCenterHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning check results', function (): void {
    $results = PrivacyCenterHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(4)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when tables, morph aliases, service provider, and hash secret are present', function (): void {
    $results = PrivacyCenterHealthCheck::runDiagnostics();

    expect(PrivacyCenterHealthCheck::passed())->toBeTrue()
        ->and($results->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue();
});

it('fails the storage table check when a privacy table is missing', function (): void {
    Schema::drop('privacy_consent_records');

    $check = new PrivacyCenterHealthCheck;

    expect($check->missingTables())->toContain('privacy_consent_records')
        ->and($check->storageTablesCheck()->passed)->toBeFalse()
        ->and(PrivacyCenterHealthCheck::passed())->toBeFalse();
});

it('fails the hash secret check when no secret is configured', function (): void {
    Config::set('capell-privacy-center.hash_secret');
    Config::set('app.key');

    $check = new PrivacyCenterHealthCheck;

    expect($check->hasIdentityHashSecret())->toBeFalse()
        ->and($check->identityHashSecretCheck()->passed)->toBeFalse()
        ->and(PrivacyCenterHealthCheck::passed())->toBeFalse();
});

it('confirms the privacy models are registered in the morph map', function (): void {
    $check = new PrivacyCenterHealthCheck;

    expect($check->unregisteredMorphAliases())->toBe([])
        ->and($check->modelMorphAliasCheck()->passed)->toBeTrue();
});

it('confirms the package service provider is loaded', function (): void {
    $check = new PrivacyCenterHealthCheck;

    expect($check->isServiceProviderLoaded())->toBeTrue()
        ->and($check->serviceProviderCheck()->passed)->toBeTrue();
});
