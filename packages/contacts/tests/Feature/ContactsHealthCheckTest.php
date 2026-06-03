<?php

declare(strict_types=1);

use Capell\Contacts\Health\ContactsHealthCheck;
use Capell\Contacts\Tests\ContactsTestCase;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

require_once __DIR__ . '/../autoload.php';

uses(ContactsTestCase::class);

it('reports a compatible capell api version', function (): void {
    expect(ContactsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning check results', function (): void {
    $results = ContactsHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(3)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when tables, morph aliases, and hash secret are present', function (): void {
    $results = ContactsHealthCheck::runDiagnostics();

    expect(ContactsHealthCheck::passed())->toBeTrue()
        ->and($results->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue();
});

it('fails the storage table check when a contacts table is missing', function (): void {
    Schema::drop('contacts');

    $check = new ContactsHealthCheck;

    expect($check->missingTables())->toContain('contacts')
        ->and($check->storageTablesCheck()->passed)->toBeFalse()
        ->and(ContactsHealthCheck::passed())->toBeFalse();
});

it('fails the hash secret check when no secret is configured', function (): void {
    Config::set('capell-contacts.hash_secret', null);
    Config::set('app.key', null);

    $check = new ContactsHealthCheck;

    expect($check->hasIdentityHashSecret())->toBeFalse()
        ->and($check->identityHashSecretCheck()->passed)->toBeFalse();
});

it('confirms the crm models are registered in the morph map', function (): void {
    $check = new ContactsHealthCheck;

    expect($check->unregisteredMorphAliases())->toBe([])
        ->and($check->modelMorphAliasCheck()->passed)->toBeTrue();
});
