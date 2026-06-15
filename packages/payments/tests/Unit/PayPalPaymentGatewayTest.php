<?php

declare(strict_types=1);

use Capell\Payments\Data\CheckoutLineItemData;
use Capell\Payments\Data\CreateCheckoutSessionData;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentPurpose;
use Capell\Payments\Support\Gateways\ConfiguredPaymentGateway;
use Capell\Payments\Support\Gateways\PayPalPaymentGateway;
use Capell\Payments\Tests\TestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(TestCase::class);

it('creates a PayPal checkout order with Capell metadata through the configured gateway', function (): void {
    config()->set('capell-payments.paypal.client_id', 'client_id_123');
    config()->set('capell-payments.paypal.client_secret', 'client_secret_123');
    config()->set('capell-payments.paypal.api_base_url', 'https://api-m.sandbox.paypal.com');

    Http::fake([
        'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
            'access_token' => 'access_token_123',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]),
        'https://api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
            'id' => 'PAYPAL_ORDER_123',
            'status' => 'CREATED',
            'links' => [
                [
                    'rel' => 'approve',
                    'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL_ORDER_123',
                ],
            ],
        ]),
    ]);

    $sessionData = (new ConfiguredPaymentGateway)->createCheckoutSession(new CreateCheckoutSessionData(
        successUrl: 'https://example.test/success',
        cancelUrl: 'https://example.test/cancel',
        lineItems: [
            new CheckoutLineItemData(
                name: 'Clinic slot',
                amount: 4500,
                currency: 'gbp',
            ),
            new CheckoutLineItemData(
                name: 'Day stable',
                amount: 2000,
                currency: 'gbp',
            ),
        ],
        purpose: PaymentPurpose::OneOff,
        provider: PaymentProvider::PayPal,
        customerEmail: 'rider@example.com',
        sourceType: 'equestrian_tour_day_slot',
        sourceId: 'slot_123',
        referenceId: 'booking_123',
        idempotencyKey: 'payment_booking_123',
        metadata: ['description' => 'Willow Farm clinic booking'],
    ));

    Http::assertSent(function (Request $request): bool {
        if ($request->url() !== 'https://api-m.sandbox.paypal.com/v2/checkout/orders') {
            return false;
        }

        $payload = $request->data();
        $purchaseUnit = $payload['purchase_units'][0] ?? [];
        $amount = is_array($purchaseUnit) ? ($purchaseUnit['amount'] ?? []) : [];
        $experienceContext = $payload['payment_source']['paypal']['experience_context'] ?? [];

        return $request->hasHeader('PayPal-Request-Id', 'payment_booking_123')
            && ($payload['intent'] ?? null) === 'CAPTURE'
            && is_array($purchaseUnit)
            && ($purchaseUnit['custom_id'] ?? null) === 'booking_123'
            && is_array($amount)
            && ($amount['value'] ?? null) === '65.00'
            && is_array($experienceContext)
            && ($experienceContext['return_url'] ?? null) === 'https://example.test/success';
    });

    expect($sessionData->provider)->toBe(PaymentProvider::PayPal)
        ->and($sessionData->providerSessionId)->toBe('PAYPAL_ORDER_123')
        ->and($sessionData->providerPaymentIntentId)->toBe('PAYPAL_ORDER_123')
        ->and($sessionData->url)->toBe('https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL_ORDER_123')
        ->and($sessionData->amountTotal)->toBe(6500)
        ->and($sessionData->metadata['capell_source_type'])->toBe('equestrian_tour_day_slot');
});

it('requires PayPal credentials before creating a checkout order', function (): void {
    Http::fake();

    expect(fn (): mixed => (new PayPalPaymentGateway)->createCheckoutSession(new CreateCheckoutSessionData(
        successUrl: 'https://example.test/success',
        cancelUrl: 'https://example.test/cancel',
        lineItems: [
            new CheckoutLineItemData(
                name: 'Clinic slot',
                amount: 4500,
                currency: 'gbp',
            ),
        ],
        provider: PaymentProvider::PayPal,
    )))->toThrow('PayPal checkout requires configured PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET values.');
});
