<?php

declare(strict_types=1);

use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Enums\PaymentDisputeStatus;
use Capell\Payments\Enums\PaymentIntentStatus;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentPurpose;
use Capell\Payments\Enums\PaymentRefundStatus;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentDispute;
use Capell\Payments\Models\PaymentIntent;
use Capell\Payments\Models\PaymentRefund;
use Capell\Payments\Tests\TestCase;
use Illuminate\Support\Facades\DB;

uses(TestCase::class);

it('casts the refund amount minor units to an integer even when persisted as a string', function (): void {
    $refund = new PaymentRefund;
    DB::table($refund->getTable())->insert([
        'provider' => PaymentProvider::Stripe->value,
        'provider_refund_id' => 're_cast_123',
        'status' => PaymentRefundStatus::Succeeded->value,
        'amount' => '750',
        'currency' => 'gbp',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $persistedRefund = PaymentRefund::query()->where('provider_refund_id', 're_cast_123')->firstOrFail();

    expect($persistedRefund->amount)->toBe(750);
});

it('casts the payment intent amount minor units to an integer even when persisted as a string', function (): void {
    $paymentIntent = new PaymentIntent;
    DB::table($paymentIntent->getTable())->insert([
        'provider' => PaymentProvider::Stripe->value,
        'provider_payment_intent_id' => 'pi_cast_123',
        'status' => PaymentIntentStatus::Succeeded->value,
        'amount' => '3000',
        'currency' => 'gbp',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $persistedPaymentIntent = PaymentIntent::query()->where('provider_payment_intent_id', 'pi_cast_123')->firstOrFail();

    expect($persistedPaymentIntent->amount)->toBe(3000);
});

it('casts the dispute amount minor units to an integer even when persisted as a string', function (): void {
    $dispute = new PaymentDispute;
    DB::table($dispute->getTable())->insert([
        'provider' => PaymentProvider::Stripe->value,
        'provider_dispute_id' => 'dp_cast_123',
        'status' => PaymentDisputeStatus::WarningNeedsResponse->value,
        'amount' => '1250',
        'currency' => 'gbp',
        'is_charge_refundable' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $persistedDispute = PaymentDispute::query()->where('provider_dispute_id', 'dp_cast_123')->firstOrFail();

    expect($persistedDispute->amount)->toBe(1250);
});

it('casts the checkout session subtotal and total minor units to integers and preserves null', function (): void {
    $checkoutSession = new CheckoutSession;
    DB::table($checkoutSession->getTable())->insert([
        'provider' => PaymentProvider::Stripe->value,
        'provider_session_id' => 'cs_cast_123',
        'status' => CheckoutSessionStatus::Complete->value,
        'mode' => CheckoutMode::Payment->value,
        'purpose' => PaymentPurpose::PaidDownload->value,
        'currency' => 'gbp',
        'amount_subtotal' => '2000',
        'amount_total' => '2400',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table($checkoutSession->getTable())->insert([
        'provider' => PaymentProvider::Stripe->value,
        'provider_session_id' => 'cs_cast_null',
        'status' => CheckoutSessionStatus::Open->value,
        'mode' => CheckoutMode::Setup->value,
        'purpose' => PaymentPurpose::PaidDownload->value,
        'currency' => 'gbp',
        'amount_subtotal' => null,
        'amount_total' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $persistedSession = CheckoutSession::query()->where('provider_session_id', 'cs_cast_123')->firstOrFail();
    $nullableSession = CheckoutSession::query()->where('provider_session_id', 'cs_cast_null')->firstOrFail();

    expect($persistedSession->amount_subtotal)->toBe(2000)
        ->and($persistedSession->amount_total)->toBe(2400)
        ->and($nullableSession->amount_subtotal)->toBeNull()
        ->and($nullableSession->amount_total)->toBeNull();
});
