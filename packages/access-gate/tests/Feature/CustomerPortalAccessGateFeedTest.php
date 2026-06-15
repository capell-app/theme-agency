<?php

declare(strict_types=1);

namespace Capell\AccessGate\Tests\Feature;

use Capell\AccessGate\Enums\BrowserTokenStatus;
use Capell\AccessGate\Enums\GrantStatus;
use Capell\AccessGate\Enums\RegistrationStatus;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Models\BrowserToken;
use Capell\AccessGate\Models\Grant;
use Capell\AccessGate\Models\Registration;
use Capell\AccessGate\Tests\CustomerPortalAccessGateTestCase;
use Capell\CustomerPortal\Actions\ResolvePortalSelfServiceItemsAction;
use Capell\CustomerPortal\Data\PortalSelfServiceItemData;
use Capell\CustomerPortal\Enums\PortalSelfServiceItemType;
use Capell\CustomerPortal\Models\PortalAccount;
use PHPUnit\Framework\Attributes\Test;

final class CustomerPortalAccessGateFeedTest extends CustomerPortalAccessGateTestCase
{
    #[Test]
    public function it_registers_access_gate_resources_as_portal_self_service_items(): void
    {
        $siteId = $this->createPortalSite();
        $area = Area::factory()->create([
            'site_id' => $siteId,
            'name' => 'Partner Library',
        ]);
        $registration = Registration::factory()->for($area, 'area')->create([
            'email' => 'Access@Example.com',
            'email_normalized' => 'access@example.com',
            'status' => RegistrationStatus::Approved,
            'requested_url' => 'https://example.test/partners',
            'requested_at' => now()->subDay(),
            'approved_at' => now(),
        ]);
        $grant = Grant::factory()
            ->for($area, 'area')
            ->for($registration, 'registration')
            ->create([
                'email' => 'access@example.com',
                'status' => GrantStatus::Active,
                'starts_at' => now()->subHour(),
                'expires_at' => now()->addWeek(),
            ]);
        Registration::factory()->create([
            'email' => 'other@example.com',
            'email_normalized' => 'other@example.com',
        ]);
        $portalAccount = PortalAccount::query()->create([
            'site_id' => $siteId,
            'email' => 'ACCESS@example.com',
            'display_name' => 'Access User',
        ]);

        $items = ResolvePortalSelfServiceItemsAction::run($portalAccount);
        $item = collect($items)->first();

        $this->assertCount(1, $items);
        $this->assertInstanceOf(PortalSelfServiceItemData::class, $item);

        $this->assertSame('access-gate.grant.' . $grant->getKey(), $item->key);
        $this->assertSame(PortalSelfServiceItemType::GatedResource, $item->type);
        $this->assertSame('Partner Library', $item->label);
        $this->assertSame('https://example.test/partners', $item->url);
        $this->assertSame(__('capell-access-gate::public.portal.grant_status.active'), $item->status);
        $this->assertSame([
            'grant_id' => (int) $grant->getKey(),
            'area_id' => (int) $area->getKey(),
            'status' => GrantStatus::Active->value,
        ], $item->meta);
    }

    #[Test]
    public function it_registers_active_browser_tokens_as_portal_self_service_items(): void
    {
        $siteId = $this->createPortalSite();
        $area = Area::factory()->create([
            'site_id' => $siteId,
            'name' => 'Device Library',
        ]);
        $registration = Registration::factory()->for($area, 'area')->create([
            'email' => 'member@example.com',
            'email_normalized' => 'member@example.com',
            'requested_url' => 'https://example.test/device-library',
        ]);
        $grant = Grant::factory()
            ->for($area, 'area')
            ->for($registration, 'registration')
            ->create([
                'email' => 'member@example.com',
                'status' => GrantStatus::Active,
            ]);
        $browserToken = BrowserToken::factory()->for($area, 'area')->for($grant, 'grant')->create([
            'status' => BrowserTokenStatus::Active,
            'expires_at' => now()->addDays(7),
            'last_used_at' => now()->subMinutes(5),
        ]);
        BrowserToken::factory()->for($area, 'area')->for($grant, 'grant')->create([
            'status' => BrowserTokenStatus::Revoked,
            'revoked_at' => now(),
        ]);
        BrowserToken::factory()->for($area, 'area')->for($grant, 'grant')->create([
            'status' => BrowserTokenStatus::Active,
            'expires_at' => now()->subDay(),
        ]);
        $portalAccount = PortalAccount::query()->create([
            'site_id' => $siteId,
            'email' => 'MEMBER@example.com',
        ]);

        $items = ResolvePortalSelfServiceItemsAction::run($portalAccount);
        $tokenItem = collect($items)->firstWhere('key', 'access-gate.browser-token.' . $browserToken->getKey());

        $this->assertCount(2, $items);
        $this->assertInstanceOf(PortalSelfServiceItemData::class, $tokenItem);
        $this->assertSame(PortalSelfServiceItemType::GatedResource, $tokenItem->type);
        $this->assertSame('Device Library', $tokenItem->label);
        $this->assertSame('https://example.test/device-library', $tokenItem->url);
        $this->assertSame(__('capell-access-gate::public.portal.browser_token_status.active'), $tokenItem->status);
        $this->assertSame([
            'browser_token_id' => (int) $browserToken->getKey(),
            'grant_id' => (int) $grant->getKey(),
            'area_id' => (int) $area->getKey(),
            'status' => BrowserTokenStatus::Active->value,
        ], $tokenItem->meta);
    }

    #[Test]
    public function it_shows_pending_access_requests_when_no_active_grant_exists(): void
    {
        $siteId = $this->createPortalSite();
        $area = Area::factory()->create([
            'site_id' => null,
            'name' => 'Preview Area',
        ]);
        $registration = Registration::factory()->for($area, 'area')->create([
            'email' => 'pending@example.com',
            'email_normalized' => 'pending@example.com',
            'status' => RegistrationStatus::Pending,
            'requested_url' => 'https://example.test/preview',
        ]);
        $portalAccount = PortalAccount::query()->create([
            'site_id' => $siteId,
            'email' => 'pending@example.com',
        ]);

        $items = ResolvePortalSelfServiceItemsAction::run($portalAccount);
        $item = collect($items)->first();

        $this->assertCount(1, $items);
        $this->assertInstanceOf(PortalSelfServiceItemData::class, $item);
        $this->assertSame('access-gate.registration.' . $registration->getKey(), $item->key);
        $this->assertSame(PortalSelfServiceItemType::GatedResource, $item->type);
        $this->assertSame('Preview Area', $item->label);
        $this->assertSame(__('capell-access-gate::public.portal.registration_status.pending'), $item->status);
    }

    #[Test]
    public function it_declares_customer_portal_gated_resource_feed_metadata_in_the_access_gate_manifest(): void
    {
        $manifest = json_decode(
            (string) file_get_contents(__DIR__ . '/../../capell.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertContains('frontend', $manifest['surfaces']);
        $this->assertContains('capell-app/customer-portal', $manifest['dependencies']['supports'] ?? []);
        $this->assertContains('access-gate-customer-portal-gated-resource-feed', $manifest['capabilities']);
    }
}
