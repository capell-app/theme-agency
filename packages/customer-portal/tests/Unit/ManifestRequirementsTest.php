<?php

declare(strict_types=1);

use Capell\CustomerPortal\Providers\CustomerPortalServiceProvider;

require_once __DIR__ . '/../autoload.php';

it('declares a cache-safe package-local foundation manifest', function (): void {
    $manifestPath = dirname(__DIR__) . '/../capell.json';
    $manifest = json_decode((string) file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);

    expect($manifest['slug'])->toBe('customer-portal')
        ->and($manifest['dependencies']['requires'])->toBe(['capell-app/core'])
        ->and($manifest['database']['requiredTables'])->toContain('portal_accounts')
        ->and($manifest['database']['requiredTables'])->toContain('portal_support_requests')
        ->and($manifest['performance']['cacheSafety']['cacheable'])->toBeFalse()
        ->and($manifest['performance']['cacheSafety']['sensitiveOutput'])->toBeTrue()
        ->and($manifest['providers']['runtime'])->toBe([
            CustomerPortalServiceProvider::class,
        ]);
});
