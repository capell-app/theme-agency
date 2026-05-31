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
        ->and($manifest['database']['migrations'])->toBeTrue()
        ->and($manifest['database']['requiredTables'])->toBe([
            'contacts',
            'contact_organisations',
            'contact_organisation_memberships',
            'contact_leads',
            'contact_activities',
        ])
        ->and($manifest['performance']['cacheSafety']['sensitiveOutput'])->toBeTrue();
});
