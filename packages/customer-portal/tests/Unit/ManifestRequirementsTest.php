<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;
use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;
use Capell\CustomerPortal\Actions\AddSupportRequestReplyAction;
use Capell\CustomerPortal\Actions\ResolvePortalPreferenceOptionsAction;
use Capell\CustomerPortal\Actions\ResolvePortalProfileAction;
use Capell\CustomerPortal\Filament\Resources\PortalSupportRequests\PortalSupportRequestResource;
use Capell\CustomerPortal\Manifest\CustomerPortalFrontendRoutesContribution;
use Capell\CustomerPortal\Manifest\CustomerPortalModelsContribution;
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
        ->and($manifest['database']['requiredTables'])->toContain('portal_support_request_replies')
        ->and($manifest['providers']['admin'])->toBe([AdminServiceProvider::class])
        ->and($manifest['contributes'][0]['class'])->toBe(PortalSupportRequestResourceContribution::class)
        ->and($manifest['contributes'][0]['resourceClass'])->toBe(PortalSupportRequestResource::class)
        ->and($manifest['contributes'])->toContain([
            'type' => 'model',
            'class' => CustomerPortalModelsContribution::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'route',
            'class' => CustomerPortalFrontendRoutesContribution::class,
        ])
        ->and(class_implements(PortalSupportRequestResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(CustomerPortalFrontendRoutesContribution::class))->toContain(RegistersExtensionRoute::class)
        ->and($manifest['performance']['frontendRenderBudgetMs'])->toBe(200)
        ->and($manifest['performance']['frontendQueryBudget'])->toBe(20)
        ->and($manifest['performance']['adminQueryBudget'])->toBe(20)
        ->and($manifest['performance']['cacheSafety']['cacheable'])->toBeFalse()
        ->and($manifest['performance']['cacheSafety']['sensitiveOutput'])->toBeTrue()
        ->and($manifest['providers']['runtime'])->toBe([
            CustomerPortalServiceProvider::class,
        ])
        ->and($manifest['actions']['resolvePortalProfile'])->toBe(ResolvePortalProfileAction::class)
        ->and($manifest['actions']['resolvePortalPreferenceOptions'])->toBe(ResolvePortalPreferenceOptionsAction::class)
        ->and($manifest['actions']['addSupportRequestReply'])->toBe(AddSupportRequestReplyAction::class)
        ->and($manifest['capabilities'])->toContain(
            'customer-portal-authenticated-routes',
            'customer-portal-dashboard-ui',
            'portal-profile',
            'portal-preferences',
            'portal-dashboard-items',
            'portal-self-service-items',
            'portal-gated-resource-feed',
            'portal-payments-feed',
            'portal-document-feed',
            'portal-event-registration-feed',
            'portal-newsletter-preference-feed',
            'portal-support-requests',
            'portal-support-request-threading',
            'portal-support-request-notifications',
        )
        ->and($manifest['contributionTraceability']['deferredContributions'])->toBe([]);
});
