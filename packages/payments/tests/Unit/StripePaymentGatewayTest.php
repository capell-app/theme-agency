<?php

declare(strict_types=1);

use Capell\Payments\Data\CheckoutLineItemData;
use Capell\Payments\Data\CreateCheckoutSessionData;
use Capell\Payments\Enums\PaymentPurpose;
use Capell\Payments\Support\Gateways\StripePaymentGateway;
use Capell\Payments\Tests\TestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(TestCase::class);

it('creates a native Stripe checkout session with Capell metadata', function (): void {
    config()->set('capell-payments.stripe.secret_key', 'sk_test_123');

    Http::fake([
        'https://api.stripe.com/v1/checkout/sessions' => Http::response([
            'id' => 'cs_test_123',
            'status' => 'open',
            'mode' => 'payment',
            'url' => 'https://checkout.stripe.com/c/pay/cs_test_123',
            'currency' => 'gbp',
            'amount_subtotal' => 500,
            'amount_total' => 500,
            'customer' => 'cus_123',
            'payment_intent' => 'pi_123',
            'metadata' => ['capell_purpose' => 'donation'],
        ]),
    ]);

    $sessionData = (new StripePaymentGateway)->createCheckoutSession(new CreateCheckoutSessionData(
        successUrl: 'https://example.test/success',
        cancelUrl: 'https://example.test/cancel',
        lineItems: [
            new CheckoutLineItemData(
                name: 'Donation',
                amount: 500,
                currency: 'gbp',
            ),
        ],
        purpose: PaymentPurpose::Donation,
        customerEmail: 'donor@example.com',
        sourceType: 'form_submission',
        sourceId: 'submission_123',
        referenceId: 'donation_123',
        idempotencyKey: 'payment_donation_123',
    ));

    Http::assertSent(function (Request $request): bool {
        parse_str($request->body(), $body);

        $metadata = $body['metadata'] ?? [];
        $lineItems = $body['line_items'] ?? [];
        $firstLineItem = is_array($lineItems) ? ($lineItems[0] ?? []) : [];
        $priceData = is_array($firstLineItem) ? ($firstLineItem['price_data'] ?? []) : [];

        return $request->hasHeader('Stripe-Version', '2026-02-25.clover')
            && $request->hasHeader('Idempotency-Key', 'payment_donation_123')
            && $body['mode'] === 'payment'
            && $body['customer_email'] === 'donor@example.com'
            && $body['client_reference_id'] === 'donation_123'
            && is_array($metadata)
            && ($metadata['capell_purpose'] ?? null) === 'donation'
            && ($metadata['capell_source_type'] ?? null) === 'form_submission'
            && is_array($priceData)
            && ($priceData['unit_amount'] ?? null) === '500';
    });

    expect($sessionData->providerSessionId)->toBe('cs_test_123')
        ->and($sessionData->providerPaymentIntentId)->toBe('pi_123')
        ->and($sessionData->providerCustomerId)->toBe('cus_123')
        ->and($sessionData->amountTotal)->toBe(500);
});
