<?php

declare(strict_types=1);

use Capell\Payments\Actions\CreateCheckoutSessionAction;
use Capell\Payments\Contracts\PaymentGateway;
use Capell\Payments\Data\CheckoutLineItemData;
use Capell\Payments\Data\CheckoutSessionData;
use Capell\Payments\Data\CreateCheckoutSessionData;
use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentPurpose;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentCustomer;
use Capell\Payments\Tests\Fakes\FakePaymentGateway;
use Capell\Payments\Tests\TestCase;

uses(TestCase::class);

it('creates and records a checkout session through the configured gateway', function (): void {
    $sessionData = new CheckoutSessionData(
        provider: PaymentProvider::Stripe,
        providerSessionId: 'cs_test_123',
        status: CheckoutSessionStatus::Open,
        mode: CheckoutMode::Payment,
        purpose: PaymentPurpose::PaidDownload,
        url: 'https://checkout.stripe.com/c/pay/cs_test_123',
        currency: 'gbp',
        amountSubtotal: 2500,
        amountTotal: 2500,
        providerCustomerId: 'cus_123',
        customerEmail: 'reader@example.com',
        customerName: 'Reader Example',
        providerPaymentIntentId: 'pi_123',
        metadata: ['download_id' => 'guide'],
        providerPayload: ['id' => 'cs_test_123'],
    );

    $gateway = new FakePaymentGateway($sessionData);

    app()->instance(PaymentGateway::class, $gateway);

    $checkoutSession = CreateCheckoutSessionAction::run(new CreateCheckoutSessionData(
        successUrl: 'https://example.test/success',
        cancelUrl: 'https://example.test/cancel',
        lineItems: [
            new CheckoutLineItemData(
                name: 'Guide download',
                amount: 2500,
                currency: 'gbp',
            ),
        ],
        purpose: PaymentPurpose::PaidDownload,
        payableType: 'download',
        payableId: 'guide',
        referenceId: 'order_123',
    ));

    expect($checkoutSession)->toBeInstanceOf(CheckoutSession::class)
        ->and($checkoutSession->provider)->toBe(PaymentProvider::Stripe)
        ->and($checkoutSession->purpose)->toBe(PaymentPurpose::PaidDownload)
        ->and($checkoutSession->status)->toBe(CheckoutSessionStatus::Open)
        ->and($checkoutSession->amount_total)->toBe(2500)
        ->and($checkoutSession->payable_type)->toBe('download')
        ->and($checkoutSession->payable_id)->toBe('guide')
        ->and($checkoutSession->reference_id)->toBe('order_123')
        ->and($gateway->lastRequest?->idempotencyKey)->toStartWith('capell-checkout-session-');

    expect(PaymentCustomer::query()->count())->toBe(1)
        ->and(PaymentCustomer::query()->first()?->provider_customer_id)->toBe('cus_123');
});
