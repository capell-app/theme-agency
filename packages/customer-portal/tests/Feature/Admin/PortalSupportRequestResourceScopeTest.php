<?php

declare(strict_types=1);

use Capell\CustomerPortal\Enums\PortalAccountStatus;
use Capell\CustomerPortal\Enums\SupportRequestPriority;
use Capell\CustomerPortal\Enums\SupportRequestStatus;
use Capell\CustomerPortal\Filament\Resources\PortalSupportRequests\PortalSupportRequestResource;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Capell\CustomerPortal\Tests\CustomerPortalTestCase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;

uses(CustomerPortalTestCase::class);

/**
 * @param  Collection<int, int>  $assignedSiteIds
 */
function portalSupportRequestScopedAdmin(Collection $assignedSiteIds, bool $isGlobalAdmin = false): Authenticatable
{
    $admin = new class extends Authenticatable
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        /** @var Collection<int, int> */
        public Collection $assignedSiteIds;

        public bool $isGlobalAdminFlag = false;

        /** @return Collection<int, int> */
        public function getAssignedSiteIds(): Collection
        {
            return $this->assignedSiteIds;
        }

        public function isGlobalAdmin(): bool
        {
            return $this->isGlobalAdminFlag;
        }
    };

    $admin->forceFill([
        'id' => 9001,
        'name' => 'Scoped Portal Admin',
        'email' => 'scoped-admin@example.test',
    ]);
    $admin->assignedSiteIds = $assignedSiteIds;
    $admin->isGlobalAdminFlag = $isGlobalAdmin;

    return $admin;
}

function createPortalSupportRequestForSite(int $siteId, string $subject): PortalSupportRequest
{
    $portalAccount = PortalAccount::query()->create([
        'site_id' => $siteId,
        'email' => sprintf('owner-%d@example.test', $siteId),
        'display_name' => sprintf('Owner %d', $siteId),
        'status' => PortalAccountStatus::Active->value,
    ]);

    return PortalSupportRequest::query()->create([
        'site_id' => $siteId,
        'portal_account_id' => $portalAccount->getKey(),
        'status' => SupportRequestStatus::Open->value,
        'priority' => SupportRequestPriority::Normal->value,
        'subject' => $subject,
        'message' => sprintf('Message for %s', $subject),
        'requester_email' => sprintf('owner-%d@example.test', $siteId),
        'source' => 'customer-portal',
    ]);
}

it('scopes the admin support-request query to the actor assigned sites', function (): void {
    $assignedSiteId = $this->createCustomerPortalSite();
    $otherSiteId = $this->createCustomerPortalSite();

    $assignedRequest = createPortalSupportRequestForSite($assignedSiteId, 'Assigned site request');
    createPortalSupportRequestForSite($otherSiteId, 'Other site request');

    auth()->setUser(portalSupportRequestScopedAdmin(collect([$assignedSiteId])));

    expect(PortalSupportRequestResource::getEloquentQuery()->pluck('id')->all())
        ->toEqualCanonicalizing([$assignedRequest->getKey()]);
});

it('never exposes another site decrypted support request to a non-global admin', function (): void {
    $assignedSiteId = $this->createCustomerPortalSite();
    $otherSiteId = $this->createCustomerPortalSite();

    createPortalSupportRequestForSite($assignedSiteId, 'Assigned site request');
    createPortalSupportRequestForSite($otherSiteId, 'Confidential other-site subject');

    auth()->setUser(portalSupportRequestScopedAdmin(collect([$assignedSiteId])));

    $visibleSubjects = PortalSupportRequestResource::getEloquentQuery()->get()
        ->map(fn (PortalSupportRequest $supportRequest): string => $supportRequest->subject)
        ->all();

    expect($visibleSubjects)->toEqualCanonicalizing(['Assigned site request'])
        ->and($visibleSubjects)->not->toContain('Confidential other-site subject');
});

it('returns no support requests for an actor with no assigned sites', function (): void {
    $siteId = $this->createCustomerPortalSite();
    createPortalSupportRequestForSite($siteId, 'Assigned site request');

    auth()->setUser(portalSupportRequestScopedAdmin(collect([])));

    expect(PortalSupportRequestResource::getEloquentQuery()->count())->toBe(0);
});

it('exposes every site support request to a global admin', function (): void {
    $firstSiteId = $this->createCustomerPortalSite();
    $secondSiteId = $this->createCustomerPortalSite();
    createPortalSupportRequestForSite($firstSiteId, 'First site request');
    createPortalSupportRequestForSite($secondSiteId, 'Second site request');

    auth()->setUser(portalSupportRequestScopedAdmin(collect([$firstSiteId]), isGlobalAdmin: true));

    expect(PortalSupportRequestResource::getEloquentQuery()->count())->toBe(2);
});
