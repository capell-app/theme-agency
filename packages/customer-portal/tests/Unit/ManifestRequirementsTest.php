<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;
use Capell\CustomerPortal\Filament\Resources\PortalSupportRequests\PortalSupportRequestResource;
use Capell\CustomerPortal\Manifest\PortalSupportRequestResourceContribution;
use Capell\CustomerPortal\Providers\AdminServiceProvider;
use Capell\CustomerPortal\Providers\CustomerPortalServiceProvider;

require_once __DIR__ . '/../autoload.php';

it('declares a cache-safe package-local foundation manifest', function (): void {
    $manifestPath = dirname(__DIR__) . '/../capell.json';
    $manifest = json_decode((string) file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);

    expect($manifest['slug'])->toBe('customer-portal')
        ->and($manifest['dependencies']['requires'])->toBe(['capell-app/admin', 'capell-app/core'])
        ->and($manifest['database']['requiredTables'])->toContain('portal_accounts')
        ->and($manifest['database']['requiredTables'])->toContain('portal_support_requests')
        ->and($manifest['providers']['admin'])->toBe([AdminServiceProvider::class])
        ->and($manifest['contributes'][0]['class'])->toBe(PortalSupportRequestResourceContribution::class)
        ->and($manifest['contributes'][0]['resourceClass'])->toBe(PortalSupportRequestResource::class)
        ->and(class_implements(PortalSupportRequestResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and($manifest['performance']['cacheSafety']['cacheable'])->toBeFalse()
        ->and($manifest['performance']['cacheSafety']['sensitiveOutput'])->toBeTrue()
        ->and($manifest['providers']['runtime'])->toBe([
            CustomerPortalServiceProvider::class,
        ]);
});
