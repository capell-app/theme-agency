<?php

declare(strict_types=1);

require_once __DIR__ . '/../autoload.php';

it('declares the contacts package manifest contract', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest)
        ->toHaveKey('manifest-version', 3)
        ->toHaveKey('name', 'capell-app/contacts')
        ->toHaveKey('namespace', 'Capell\\Contacts')
        ->and($manifest['dependencies']['requires'])->toContain('capell-app/admin', 'capell-app/core')
        ->and($manifest['providers']['admin'])->toContain('Capell\\Contacts\\Providers\\AdminServiceProvider')
        ->and($manifest['database']['migrations'])->toBeTrue()
        ->and($manifest['database']['requiredTables'])->toBe([
            'contacts',
            'contact_organisations',
            'contact_organisation_memberships',
            'contact_leads',
            'contact_activities',
        ])
        ->and($manifest['permissions'])->toContain(
            'View:Contact',
            'View:Organisation',
            'View:Lead',
            'View:ContactActivity',
        )
        ->and($manifest['actions']['syncContactSourceRecord'])->toBe('Capell\\Contacts\\Actions\\SyncContactSourceRecordAction')
        ->and($manifest['capabilities'])->toContain(
            'contacts-source-identity',
            'contacts-source-sync',
        )
        ->and($manifest['performance']['cacheSafety']['sensitiveOutput'])->toBeTrue();
});
