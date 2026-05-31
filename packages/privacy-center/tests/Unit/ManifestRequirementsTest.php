<?php

declare(strict_types=1);

use Capell\PrivacyCenter\Tests\PrivacyCenterTestCase;

require_once dirname(__DIR__) . '/autoload.php';

uses(PrivacyCenterTestCase::class);

it('declares privacy center manifest ownership and cache safety', function (): void {
    $manifest = json_decode((string) file_get_contents(dirname(__DIR__, 2) . '/capell.json'), true);

    expect($manifest)->toBeArray()
        ->and($manifest['name'])->toBe('capell-app/privacy-center')
        ->and($manifest['namespace'])->toBe('Capell\\PrivacyCenter')
        ->and($manifest['database']['requiredTables'])->toContain('privacy_consent_records')
        ->and($manifest['performance']['cacheSafety']['sensitiveOutput'])->toBeTrue()
        ->and($manifest['capabilities'])->toContain('privacy-center-subject-requests');
});
