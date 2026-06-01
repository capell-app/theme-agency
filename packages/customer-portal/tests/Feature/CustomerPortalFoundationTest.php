<?php

declare(strict_types=1);

use Capell\CustomerPortal\Actions\FindOrCreatePortalAccountAction;
use Capell\CustomerPortal\Actions\ResolvePortalDashboardItemsAction;
use Capell\CustomerPortal\Actions\ResolvePortalSelfServiceItemsAction;
use Capell\CustomerPortal\Actions\SubmitSupportRequestAction;
use Capell\CustomerPortal\Actions\UpdatePortalPreferencesAction;
use Capell\CustomerPortal\Actions\UpdateSupportRequestStatusAction;
use Capell\CustomerPortal\Contracts\PortalDashboardItemProvider;
use Capell\CustomerPortal\Contracts\PortalSelfServiceItemProvider;
use Capell\CustomerPortal\Data\PortalAccountIdentityData;
use Capell\CustomerPortal\Data\PortalDashboardItemData;
use Capell\CustomerPortal\Data\PortalPreferencesData;
use Capell\CustomerPortal\Data\PortalSelfServiceItemData;
use Capell\CustomerPortal\Data\SupportRequestData;
use Capell\CustomerPortal\Enums\PortalDashboardItemPriority;
use Capell\CustomerPortal\Enums\PortalSelfServiceItemType;
use Capell\CustomerPortal\Enums\SupportRequestPriority;
use Capell\CustomerPortal\Enums\SupportRequestStatus;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Capell\CustomerPortal\Support\PortalDashboardItemRegistry;
use Capell\CustomerPortal\Support\PortalSelfServiceItemRegistry;
use Capell\CustomerPortal\Tests\CustomerPortalTestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

require_once __DIR__ . '/../autoload.php';

uses(CustomerPortalTestCase::class);

it('loads the portal foundation tables', function (): void {
    expect(Schema::hasTable('portal_accounts'))->toBeTrue()
        ->and(Schema::hasTable('portal_support_requests'))->toBeTrue();
});

it('creates and reuses portal accounts by normalized email', function (): void {
    $siteId = $this->createCustomerPortalSite();

    $firstPortalAccount = FindOrCreatePortalAccountAction::run(new PortalAccountIdentityData(
        siteId: $siteId,
        email: ' Customer@Example.test ',
        displayName: 'Customer Example',
        profile: ['company' => 'Example Ltd'],
        preferences: ['newsletter' => false],
    ));

    $secondPortalAccount = FindOrCreatePortalAccountAction::run(new PortalAccountIdentityData(
        siteId: $siteId,
        email: 'customer@example.test',
        ownerType: 'user',
        ownerId: 123,
        profile: ['role' => 'buyer'],
        preferences: ['events' => true],
    ));

    $freshPortalAccount = $secondPortalAccount->fresh();

    throw_unless($freshPortalAccount instanceof PortalAccount, RuntimeException::class, 'Expected portal account to refresh from the database.');

    expect($secondPortalAccount->is($firstPortalAccount))->toBeTrue()
        ->and(PortalAccount::query()->count())->toBe(1)
        ->and($freshPortalAccount->email)->toBe('customer@example.test')
        ->and($freshPortalAccount->email_hash)->toBe(PortalAccount::emailHash('customer@example.test'))
        ->and($freshPortalAccount->profile)->toBe([
            'company' => 'Example Ltd',
            'role' => 'buyer',
        ])
        ->and($freshPortalAccount->preferences)->toBe([
            'newsletter' => false,
            'events' => true,
        ]);
});

it('updates portal preferences through the action boundary', function (): void {
    $portalAccount = FindOrCreatePortalAccountAction::run(new PortalAccountIdentityData(
        siteId: $this->createCustomerPortalSite(),
        email: 'preferences@example.test',
        preferences: ['newsletter' => false, 'events' => false],
    ));

    $updatedPortalAccount = UpdatePortalPreferencesAction::run(
        portalAccount: $portalAccount,
        preferencesData: new PortalPreferencesData(values: ['events' => true]),
    );

    expect($updatedPortalAccount->preferences)->toBe([
        'newsletter' => false,
        'events' => true,
    ]);
});

it('records support requests for portal accounts', function (): void {
    $portalAccount = FindOrCreatePortalAccountAction::run(new PortalAccountIdentityData(
        siteId: $this->createCustomerPortalSite(),
        email: 'support@example.test',
    ));

    $supportRequest = SubmitSupportRequestAction::run(
        portalAccount: $portalAccount,
        supportRequestData: new SupportRequestData(
            subject: 'Billing question',
            message: 'Please send the latest invoice.',
            priority: SupportRequestPriority::High,
            source: 'portal',
            context: ['path' => '/portal/support'],
        ),
    );

    expect($supportRequest)->toBeInstanceOf(PortalSupportRequest::class)
        ->and($supportRequest->account->is($portalAccount))->toBeTrue()
        ->and($supportRequest->status)->toBe(SupportRequestStatus::Open)
        ->and($supportRequest->priority)->toBe(SupportRequestPriority::High)
        ->and($supportRequest->requester_email)->toBe('support@example.test')
        ->and($supportRequest->context)->toBe(['path' => '/portal/support'])
        ->and($portalAccount->supportRequests()->open()->count())->toBe(1);
});

it('triages support request status through the action boundary', function (): void {
    $portalAccount = FindOrCreatePortalAccountAction::run(new PortalAccountIdentityData(
        siteId: $this->createCustomerPortalSite(),
        email: 'triage@example.test',
    ));

    $supportRequest = SubmitSupportRequestAction::run(
        portalAccount: $portalAccount,
        supportRequestData: new SupportRequestData(
            subject: 'Need help',
            message: 'Please help with access.',
        ),
    );

    $resolved = UpdateSupportRequestStatusAction::run($supportRequest, SupportRequestStatus::Resolved);

    expect($resolved->status)->toBe(SupportRequestStatus::Resolved)
        ->and($resolved->resolved_at)->not->toBeNull()
        ->and($resolved->closed_at)->toBeNull()
        ->and($portalAccount->supportRequests()->open()->count())->toBe(0);
});

it('resolves dashboard items from registered providers in priority order', function (): void {
    $portalAccount = FindOrCreatePortalAccountAction::run(new PortalAccountIdentityData(
        siteId: $this->createCustomerPortalSite(),
        email: 'dashboard@example.test',
    ));

    resolve(PortalDashboardItemRegistry::class)->register('test-provider', new class implements PortalDashboardItemProvider
    {
        public function dashboardItemsFor(PortalAccount $portalAccount): iterable
        {
            return [
                new PortalDashboardItemData(
                    key: 'documents',
                    label: 'Documents',
                    count: 2,
                    priority: PortalDashboardItemPriority::Normal,
                ),
                new PortalDashboardItemData(
                    key: 'payments',
                    label: 'Payments',
                    count: 1,
                    priority: PortalDashboardItemPriority::High,
                    meta: ['account_id' => $portalAccount->getKey()],
                ),
            ];
        }
    });

    $dashboardItems = ResolvePortalDashboardItemsAction::run($portalAccount);

    expect($dashboardItems)->toHaveCount(2)
        ->and($dashboardItems[0]->key)->toBe('payments')
        ->and($dashboardItems[1]->key)->toBe('documents');
});

it('resolves typed self-service items from registered providers in recency order', function (): void {
    $portalAccount = FindOrCreatePortalAccountAction::run(new PortalAccountIdentityData(
        siteId: $this->createCustomerPortalSite(),
        email: 'self-service@example.test',
    ));

    resolve(PortalSelfServiceItemRegistry::class)->register('test-provider', new class implements PortalSelfServiceItemProvider
    {
        public function selfServiceItemsFor(PortalAccount $portalAccount): iterable
        {
            return [
                new PortalSelfServiceItemData(
                    key: 'document-1',
                    type: PortalSelfServiceItemType::Document,
                    label: 'Policy document',
                    description: 'Ready for review.',
                    url: '/portal/documents/policy',
                    status: 'Ready',
                    occurredAt: now()->subDay(),
                    meta: ['account_id' => $portalAccount->getKey()],
                ),
                new PortalSelfServiceItemData(
                    key: 'payment-1',
                    type: PortalSelfServiceItemType::Payment,
                    label: 'Latest invoice',
                    description: 'Paid invoice.',
                    url: '/portal/payments/invoice',
                    status: 'Paid',
                    occurredAt: now(),
                ),
            ];
        }
    });

    $items = ResolvePortalSelfServiceItemsAction::run($portalAccount);

    expect($items)->toHaveCount(2)
        ->and($items[0]->key)->toBe('payment-1')
        ->and($items[0]->type)->toBe(PortalSelfServiceItemType::Payment)
        ->and($items[1]->key)->toBe('document-1');
});

it('requires a durable portal account identity', function (): void {
    FindOrCreatePortalAccountAction::run(new PortalAccountIdentityData(
        siteId: $this->createCustomerPortalSite(),
    ));
})->throws(ValidationException::class);
