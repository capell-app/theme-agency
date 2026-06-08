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

    throw_unless(is_array($manifest), RuntimeException::class, 'Expected customer portal manifest to decode as an array.');

    $dependencies = customerPortalManifestMap($manifest['dependencies'] ?? null);
    $database = customerPortalManifestMap($manifest['database'] ?? null);
    $providers = customerPortalManifestMap($manifest['providers'] ?? null);
    $performance = customerPortalManifestMap($manifest['performance'] ?? null);
    $cacheSafety = customerPortalManifestMap($performance['cacheSafety'] ?? null);
    $actions = customerPortalManifestMap($manifest['actions'] ?? null);
    $contributes = customerPortalManifestList($manifest['contributes'] ?? null);
    $contributionTraceability = customerPortalManifestMap($manifest['contributionTraceability'] ?? null);

    expect($manifest['slug'])->toBe('customer-portal')
        ->and($dependencies['requires'] ?? [])->toBe(['capell-app/admin', 'capell-app/core'])
        ->and($database['requiredTables'] ?? [])->toContain('portal_accounts')
        ->and($database['requiredTables'] ?? [])->toContain('portal_support_requests')
        ->and($database['requiredTables'] ?? [])->toContain('portal_support_request_replies')
        ->and($providers['admin'] ?? [])->toBe([AdminServiceProvider::class])
        ->and($contributes[0]['class'] ?? null)->toBe(PortalSupportRequestResourceContribution::class)
        ->and($contributes[0]['resourceClass'] ?? null)->toBe(PortalSupportRequestResource::class)
        ->and($contributes)->toContain([
            'type' => 'model',
            'class' => CustomerPortalModelsContribution::class,
        ])
        ->and($contributes)->toContain([
            'type' => 'route',
            'class' => CustomerPortalFrontendRoutesContribution::class,
        ])
        ->and(class_implements(PortalSupportRequestResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(CustomerPortalFrontendRoutesContribution::class))->toContain(RegistersExtensionRoute::class)
        ->and($performance['frontendRenderBudgetMs'] ?? null)->toBe(200)
        ->and($performance['frontendQueryBudget'] ?? null)->toBe(20)
        ->and($performance['adminQueryBudget'] ?? null)->toBe(20)
        ->and($cacheSafety['cacheable'] ?? null)->toBeFalse()
        ->and($cacheSafety['sensitiveOutput'] ?? null)->toBeTrue()
        ->and($providers['runtime'] ?? [])->toBe([
            CustomerPortalServiceProvider::class,
        ])
        ->and($actions['resolvePortalProfile'] ?? null)->toBe(ResolvePortalProfileAction::class)
        ->and($actions['resolvePortalPreferenceOptions'] ?? null)->toBe(ResolvePortalPreferenceOptionsAction::class)
        ->and($actions['addSupportRequestReply'] ?? null)->toBe(AddSupportRequestReplyAction::class)
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
        ->and($contributionTraceability['deferredContributions'] ?? [])->toBe([]);
});

/**
 * @return array<string, mixed>
 */
function customerPortalManifestMap(mixed $value): array
{
    if (! is_array($value)) {
        return [];
    }

    $map = [];

    foreach ($value as $key => $item) {
        if (is_string($key)) {
            $map[$key] = $item;
        }
    }

    return $map;
}

/**
 * @return list<array<string, mixed>>
 */
function customerPortalManifestList(mixed $value): array
{
    if (! is_array($value)) {
        return [];
    }

    $list = [];

    foreach ($value as $item) {
        if (is_array($item)) {
            $list[] = customerPortalManifestMap($item);
        }
    }

    return $list;
}
