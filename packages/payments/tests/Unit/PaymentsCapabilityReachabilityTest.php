<?php

declare(strict_types=1);

use Capell\Payments\Actions\CreateBillingPortalSessionAction;
use Capell\Payments\Actions\CreateCheckoutSessionAction;
use Capell\Payments\Actions\CreateFormPaymentCheckoutSessionAction;
use Capell\Payments\Actions\CreateFormPaymentCheckoutUrlAction;
use Capell\Payments\Actions\CreatePaidDownloadUrlAction;
use Capell\Payments\Actions\FulfillCompletedCheckoutSessionAction;
use Capell\Payments\Actions\HandleStripeWebhookAction;
use Capell\Payments\Actions\IssuePaymentRefundAction;
use Capell\Payments\Actions\ProcessStripeWebhookEventAction;
use Capell\Payments\Actions\RecordPaymentDisputeAction;
use Capell\Payments\Actions\RecordPaymentRefundAction;
use Capell\Payments\Actions\ResolveSubscriptionEntitlementAction;
use Capell\Payments\Contracts\PaymentFulfillmentHandler;
use Capell\Payments\Contracts\PaymentGateway;
use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\PaymentPurpose;
use Capell\Payments\Http\Controllers\CreateFormPaymentCheckoutController;
use Capell\Payments\Http\Controllers\CreatePortalBillingSessionController;
use Capell\Payments\Http\Controllers\DownloadPaidDownloadController;
use Capell\Payments\Http\Controllers\StripeWebhookController;
use Capell\Payments\Support\CustomerPortal\PaymentsPortalDashboardItemProvider;
use Capell\Payments\Support\CustomerPortal\PaymentsPortalSelfServiceItemProvider;
use Capell\Payments\Support\Fulfillment\PaidDownloadFulfillmentHandler;
use Capell\Payments\Support\Gateways\ConfiguredPaymentGateway;
use Capell\Payments\Tests\CustomerPortalPaymentsTestCase;
use Illuminate\Support\Facades\Route;

uses(CustomerPortalPaymentsTestCase::class);

it('backs advertised payment capabilities with production entrypoints', function (): void {
    $manifest = paymentsCapabilityManifest();

    expect($manifest['capabilities'])->toBe([
        'stripe-checkout',
        'one-off-payments',
        'subscriptions',
        'donations',
        'paid-downloads',
        'paid-gated-access',
        'form-payment-fields',
        'stripe-webhooks',
        'refund-records',
        'dispute-records',
        'payments-admin',
        'payment-fulfillment-handlers',
        'stripe-billing-portal',
        'subscription-entitlements',
        'customer-portal-billing-dashboard',
        'customer-portal-payments-feed',
    ]);

    expect($manifest['actions'])->toMatchArray([
        'checkout' => CreateCheckoutSessionAction::class,
        'billingPortal' => CreateBillingPortalSessionAction::class,
        'formPaymentCheckoutUrl' => CreateFormPaymentCheckoutUrlAction::class,
        'formPaymentCheckout' => CreateFormPaymentCheckoutSessionAction::class,
        'paidDownloadUrl' => CreatePaidDownloadUrlAction::class,
        'subscriptionEntitlement' => ResolveSubscriptionEntitlementAction::class,
    ]);

    expect(app(PaymentGateway::class))->toBeInstanceOf(ConfiguredPaymentGateway::class)
        ->and(PaymentPurpose::OneOff->value)->toBe('one_off')
        ->and(PaymentPurpose::Subscription->value)->toBe('subscription')
        ->and(PaymentPurpose::Donation->value)->toBe('donation')
        ->and(PaymentPurpose::PaidDownload->value)->toBe('paid_download')
        ->and(PaymentPurpose::GatedAccess->value)->toBe('gated_access')
        ->and(PaymentPurpose::FormPayment->value)->toBe('form_payment')
        ->and(CheckoutMode::Payment->value)->toBe('payment')
        ->and(CheckoutMode::Subscription->value)->toBe('subscription');

    foreach (paymentsCapabilityActionClasses() as $actionClass) {
        expect(class_exists($actionClass))->toBeTrue()
            ->and(is_callable([$actionClass, 'run']))->toBeTrue();
    }
});

it('exposes advertised payment routes through production controllers', function (): void {
    expect(paymentsCapabilityRouteAction('capell-payments.stripe-webhook'))->toContain(StripeWebhookController::class)
        ->and(paymentsCapabilityRouteAction('capell-payments.paid-downloads.show'))->toContain(DownloadPaidDownloadController::class)
        ->and(paymentsCapabilityRouteAction('capell-payments.form-builder.checkout'))->toContain(CreateFormPaymentCheckoutController::class)
        ->and(paymentsCapabilityRouteAction('capell-payments.portal.billing'))->toContain(CreatePortalBillingSessionController::class);
});

it('throttles public stripe webhook ingestion', function (): void {
    expect(Route::getRoutes()->getByName('capell-payments.stripe-webhook')?->gatherMiddleware())
        ->toContain('throttle:capell-payments-stripe-webhook');
});

it('registers production fulfillment and customer portal integrations when installed', function (): void {
    $handlers = collect(app()->tagged(PaymentFulfillmentHandler::TAG));

    expect($handlers->contains(fn (mixed $handler): bool => $handler instanceof PaidDownloadFulfillmentHandler))->toBeTrue()
        ->and(PaymentFulfillmentHandler::TAG)->toBe('capell.payments.fulfillment_handler')
        ->and(class_exists(PaymentsPortalDashboardItemProvider::class))->toBeTrue()
        ->and(class_exists(PaymentsPortalSelfServiceItemProvider::class))->toBeTrue();
});

/**
 * @return array<string, mixed>
 */
function paymentsCapabilityManifest(): array
{
    return capell_json_file_array(__DIR__ . '/../../capell.json');
}

/**
 * @return list<class-string>
 */
function paymentsCapabilityActionClasses(): array
{
    return [
        CreateCheckoutSessionAction::class,
        CreateBillingPortalSessionAction::class,
        CreateFormPaymentCheckoutUrlAction::class,
        CreateFormPaymentCheckoutSessionAction::class,
        CreatePaidDownloadUrlAction::class,
        ResolveSubscriptionEntitlementAction::class,
        HandleStripeWebhookAction::class,
        ProcessStripeWebhookEventAction::class,
        FulfillCompletedCheckoutSessionAction::class,
        IssuePaymentRefundAction::class,
        RecordPaymentRefundAction::class,
        RecordPaymentDisputeAction::class,
    ];
}

function paymentsCapabilityRouteAction(string $routeName): string
{
    $route = Route::getRoutes()->getByName($routeName);

    throw_if($route === null, RuntimeException::class, sprintf('Expected route [%s] to be registered.', $routeName));

    return $route->getActionName();
}
