<?php

declare(strict_types=1);

use Capell\CustomerPortal\Contracts\PortalDashboardItemProvider;
use Capell\CustomerPortal\Contracts\PortalSelfServiceItemProvider;
use Capell\CustomerPortal\Data\PortalDashboardItemData;
use Capell\CustomerPortal\Data\PortalSelfServiceItemData;
use Capell\CustomerPortal\Enums\PortalDashboardItemPriority;
use Capell\CustomerPortal\Enums\PortalSelfServiceItemType;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Capell\CustomerPortal\Support\PortalDashboardItemRegistry;
use Capell\CustomerPortal\Support\PortalSelfServiceItemRegistry;
use Capell\CustomerPortal\Tests\CustomerPortalTestCase;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Route;

require_once dirname(__DIR__) . '/../autoload.php';

uses(CustomerPortalTestCase::class);

function customerPortalUser(): User
{
    $user = new User;
    $user->forceFill([
        'id' => 1001,
        'name' => 'Morgan Customer',
        'email' => 'Morgan@Example.test',
    ]);

    return $user;
}

it('registers authenticated customer portal frontend routes', function (): void {
    expect(Route::has('capell-customer-portal.dashboard'))->toBeTrue()
        ->and(Route::has('capell-customer-portal.preferences.update'))->toBeTrue()
        ->and(Route::has('capell-customer-portal.support.store'))->toBeTrue();
});

it('renders an authenticated dashboard without exposing package internals', function (): void {
    $this->createCustomerPortalSite();

    resolve(PortalDashboardItemRegistry::class)->register('test-provider', new class implements PortalDashboardItemProvider
    {
        public function dashboardItemsFor(PortalAccount $portalAccount): iterable
        {
            return [
                new PortalDashboardItemData(
                    key: 'documents',
                    label: 'Documents',
                    description: 'Secure documents ready for review.',
                    url: '/portal/documents',
                    count: 2,
                    priority: PortalDashboardItemPriority::High,
                ),
            ];
        }
    });
    resolve(PortalSelfServiceItemRegistry::class)->register('test-provider', new class implements PortalSelfServiceItemProvider
    {
        public function selfServiceItemsFor(PortalAccount $portalAccount): iterable
        {
            return [
                new PortalSelfServiceItemData(
                    key: 'invoice-1',
                    type: PortalSelfServiceItemType::Payment,
                    label: 'Latest invoice',
                    description: 'Paid on card ending 4242.',
                    url: '/portal/payments/invoice-1',
                    status: 'Paid',
                ),
            ];
        }
    });

    $response = $this->actingAs(customerPortalUser())
        ->get(route('capell-customer-portal.dashboard'));

    $response
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertSee('Customer portal')
        ->assertSee('Morgan Customer')
        ->assertSee('Documents')
        ->assertSee('Secure documents ready for review.')
        ->assertSee('Self-service')
        ->assertSee('Latest invoice')
        ->assertSee('Paid on card ending 4242.')
        ->assertDontSee('capell-app/customer-portal', false)
        ->assertDontSee('portal_account_id', false)
        ->assertDontSee('signed', false)
        ->assertDontSee('Filament', false);

    expect(PortalAccount::query()->count())->toBe(1)
        ->and(PortalAccount::query()->first()?->email)->toBe('morgan@example.test');
});

it('updates authenticated portal preferences from the frontend', function (): void {
    $this->createCustomerPortalSite();

    $response = $this->actingAs(customerPortalUser())
        ->post(route('capell-customer-portal.preferences.update'), [
            'preferences' => [
                'email_updates' => '1',
                'event_reminders' => '1',
            ],
        ]);

    $response->assertRedirect(route('capell-customer-portal.dashboard'));

    $portalAccount = PortalAccount::query()->firstOrFail();

    expect($portalAccount->preferences)->toBe([
        'email_updates' => true,
        'event_reminders' => true,
    ]);
});

it('submits authenticated support requests from the frontend', function (): void {
    $this->createCustomerPortalSite();

    $response = $this->actingAs(customerPortalUser())
        ->post(route('capell-customer-portal.support.store'), [
            'subject' => 'Invoice question',
            'priority' => 'high',
            'message' => 'Please resend the latest invoice.',
        ]);

    $response->assertRedirect(route('capell-customer-portal.dashboard'));

    $supportRequest = PortalSupportRequest::query()->firstOrFail();

    expect($supportRequest->subject)->toBe('Invoice question')
        ->and($supportRequest->message)->toBe('Please resend the latest invoice.')
        ->and($supportRequest->priority->value)->toBe('high')
        ->and($supportRequest->source)->toBe('customer-portal')
        ->and($supportRequest->requester_email)->toBe('morgan@example.test');
});
