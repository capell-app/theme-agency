<?php

declare(strict_types=1);

use Capell\CustomerPortal\Actions\ResolveAuthenticatedPortalAccountAction;
use Capell\CustomerPortal\Actions\ResolvePortalDashboardItemsAction;
use Capell\CustomerPortal\Actions\ResolvePortalSelfServiceItemsAction;
use Capell\CustomerPortal\Data\PortalSelfServiceItemData;
use Capell\CustomerPortal\Enums\PortalSelfServiceItemType;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\Payments\Actions\ResolvePortalPaymentCustomerAction;
use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentPurpose;
use Capell\Payments\Enums\SubscriptionStatus;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentCustomer;
use Capell\Payments\Models\PaymentDownloadEntitlement;
use Capell\Payments\Models\Subscription;
use Capell\Payments\Tests\CustomerPortalPaymentsTestCase;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

uses(CustomerPortalPaymentsTestCase::class);

function paymentsPortalUser(): User
{
    $user = new User;
    $user->forceFill([
        'id' => 1001,
        'name' => 'Morgan Customer',
        'email' => 'morgan@example.test',
    ]);

    return $user;
}

it('registers payment billing dashboard items for portal accounts', function (): void {
    $siteId = $this->createPortalPaymentsSite();
    $portalAccount = PortalAccount::query()->create([
        'site_id' => $siteId,
        'owner_type' => User::class,
        'owner_id' => 1001,
        'email' => 'morgan@example.test',
        'display_name' => 'Morgan Customer',
    ]);
    $paymentCustomer = PaymentCustomer::query()->create([
        'provider' => PaymentProvider::Stripe,
        'provider_customer_id' => 'cus_portal_123',
        'site_id' => $siteId,
        'billable_type' => User::class,
        'billable_id' => '1001',
        'email' => 'morgan@example.test',
    ]);

    Subscription::query()->create([
        'provider' => PaymentProvider::Stripe,
        'provider_subscription_id' => 'sub_portal_123',
        'payment_customer_id' => $paymentCustomer->getKey(),
        'provider_customer_id' => 'cus_portal_123',
        'status' => SubscriptionStatus::Active,
        'current_period_ends_at' => now()->addMonth(),
    ]);

    $items = ResolvePortalDashboardItemsAction::run($portalAccount);

    expect(Route::has('capell-payments.portal.billing'))->toBeTrue()
        ->and($items)->toHaveCount(1)
        ->and($items[0]->key)->toBe('payments.billing')
        ->and($items[0]->label)->toBe(__('capell-payments::generic.portal.billing_label'))
        ->and($items[0]->url)->toBe(route('capell-payments.portal.billing'))
        ->and($items[0]->count)->toBe(1)
        ->and($items[0]->meta)->toBe(['provider' => 'stripe']);
});

it('registers payment self service items for portal accounts', function (): void {
    $siteId = $this->createPortalPaymentsSite();
    $portalAccount = PortalAccount::query()->create([
        'site_id' => $siteId,
        'owner_type' => User::class,
        'owner_id' => 1001,
        'email' => 'morgan@example.test',
        'display_name' => 'Morgan Customer',
    ]);
    $paymentCustomer = PaymentCustomer::query()->create([
        'provider' => PaymentProvider::Stripe,
        'provider_customer_id' => 'cus_portal_feed_123',
        'site_id' => $siteId,
        'billable_type' => User::class,
        'billable_id' => '1001',
        'email' => 'morgan@example.test',
    ]);

    Subscription::query()->create([
        'provider' => PaymentProvider::Stripe,
        'provider_subscription_id' => 'sub_portal_feed_123',
        'payment_customer_id' => $paymentCustomer->getKey(),
        'provider_customer_id' => 'cus_portal_feed_123',
        'status' => SubscriptionStatus::Active,
        'current_period_ends_at' => now()->addMonth(),
    ]);
    $checkoutSession = CheckoutSession::query()->create([
        'provider' => PaymentProvider::Stripe,
        'provider_session_id' => 'cs_portal_feed_123',
        'payment_customer_id' => $paymentCustomer->getKey(),
        'site_id' => $siteId,
        'mode' => CheckoutMode::Payment,
        'purpose' => PaymentPurpose::PaidDownload,
        'status' => CheckoutSessionStatus::Complete,
        'currency' => 'gbp',
        'amount_total' => 1999,
        'provider_customer_id' => 'cus_portal_feed_123',
        'reference_id' => 'guide-001',
        'completed_at' => now()->subHour(),
    ]);
    PaymentDownloadEntitlement::query()->create([
        'checkout_session_id' => $checkoutSession->getKey(),
        'site_id' => $siteId,
        'download_key' => 'guide-001',
        'download_name' => 'Paid guide',
        'disk' => 'local',
        'path' => 'downloads/guide.pdf',
        'file_name' => 'guide.pdf',
        'fulfilled_at' => now(),
        'expires_at' => now()->addWeek(),
    ]);

    $items = ResolvePortalSelfServiceItemsAction::run($portalAccount);
    $itemsByKeyPrefix = collect($items)->keyBy(function (PortalSelfServiceItemData $item): string {
        $key = $item->key;

        if (str_starts_with($key, 'payments.download.')) {
            return 'download';
        }

        if (str_starts_with($key, 'payments.checkout-session.')) {
            return 'checkout';
        }

        if (str_starts_with($key, 'payments.subscription.')) {
            return 'subscription';
        }

        return $key;
    });

    expect($items)->toHaveCount(3)
        ->and($itemsByKeyPrefix->get('download')->type)->toBe(PortalSelfServiceItemType::Payment)
        ->and($itemsByKeyPrefix->get('download')->label)->toBe('Paid guide')
        ->and($itemsByKeyPrefix->get('download')->url)->toContain('/capell/payments/downloads/')
        ->and($itemsByKeyPrefix->get('checkout')->description)->toBe('Paid GBP 19.99.')
        ->and($itemsByKeyPrefix->get('subscription')->url)->toBe(route('capell-payments.portal.billing'));
});

it('resolves portal payment customers by owner metadata and decrypted email fallback', function (): void {
    $siteId = $this->createPortalPaymentsSite();
    $portalAccount = PortalAccount::query()->create([
        'site_id' => $siteId,
        'email' => 'morgan@example.test',
    ]);
    $metadataCustomer = PaymentCustomer::query()->create([
        'provider' => PaymentProvider::Stripe,
        'provider_customer_id' => 'cus_metadata_123',
        'site_id' => $siteId,
        'metadata' => ['portal_account_id' => $portalAccount->getKey()],
    ]);

    expect(ResolvePortalPaymentCustomerAction::run($portalAccount)?->is($metadataCustomer))->toBeTrue();

    $metadataCustomer->delete();

    $emailCustomer = PaymentCustomer::query()->create([
        'provider' => PaymentProvider::Stripe,
        'provider_customer_id' => 'cus_email_123',
        'site_id' => $siteId,
        'email' => 'MORGAN@EXAMPLE.TEST',
    ]);

    expect(ResolvePortalPaymentCustomerAction::run($portalAccount)?->is($emailCustomer))->toBeTrue();
});

it('creates Stripe billing portal sessions from the authenticated portal route', function (): void {
    $siteId = $this->createPortalPaymentsSite();
    $user = paymentsPortalUser();

    config()->set('capell-payments.stripe.secret_key', 'sk_test_123');
    config()->set('capell-customer-portal.site_id', $siteId);

    PaymentCustomer::query()->create([
        'provider' => PaymentProvider::Stripe,
        'provider_customer_id' => 'cus_route_123',
        'site_id' => $siteId,
        'billable_type' => $user->getMorphClass(),
        'billable_id' => '1001',
        'email' => 'morgan@example.test',
    ]);

    Http::fake([
        'https://api.stripe.com/v1/billing_portal/sessions' => Http::response([
            'id' => 'bps_route_123',
            'object' => 'billing_portal.session',
            'created' => 1_685_000_000,
            'customer' => 'cus_route_123',
            'return_url' => route('capell-customer-portal.dashboard'),
            'url' => 'https://billing.stripe.com/p/session/route_123',
        ]),
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('capell-payments.portal.billing'));

    $response->assertRedirect('https://billing.stripe.com/p/session/route_123');

    $portalAccount = ResolveAuthenticatedPortalAccountAction::run($user);

    Http::assertSent(function (Request $request): bool {
        parse_str($request->body(), $body);

        return $body['customer'] === 'cus_route_123'
            && $body['return_url'] === route('capell-customer-portal.dashboard');
    });

    expect($portalAccount->email)->toBe('morgan@example.test');
});
