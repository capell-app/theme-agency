<?php

declare(strict_types=1);

use Capell\Payments\Actions\IssuePaymentRefundAction;
use Capell\Payments\Enums\PaymentIntentStatus;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentRefundStatus;
use Capell\Payments\Models\PaymentIntent;
use Capell\Payments\Models\PaymentRefund;
use Capell\Payments\Tests\TestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(TestCase::class);

it('issues and records a Stripe payment intent refund', function (): void {
    config()->set('capell-payments.stripe.secret_key', 'sk_test_123');

    $paymentIntent = PaymentIntent::query()->create([
        'provider' => PaymentProvider::Stripe->value,
        'provider_payment_intent_id' => 'pi_123',
        'status' => PaymentIntentStatus::Succeeded->value,
        'amount' => 2500,
        'currency' => 'gbp',
    ]);

    Http::fake([
        'https://api.stripe.com/v1/refunds' => Http::response([
            'id' => 're_123',
            'object' => 'refund',
            'amount' => 1000,
            'currency' => 'gbp',
            'payment_intent' => 'pi_123',
            'charge' => 'ch_123',
            'reason' => 'requested_by_customer',
            'status' => 'succeeded',
            'metadata' => [
                'capell_payment_intent_id' => (string) $paymentIntent->getKey(),
            ],
        ]),
    ]);

    $refund = IssuePaymentRefundAction::run($paymentIntent, 1000, 'requested_by_customer');

    Http::assertSent(function (Request $request): bool {
        parse_str($request->body(), $body);

        return $request->hasHeader('Stripe-Version', '2026-02-25.clover')
            && $request->hasHeader('Idempotency-Key')
            && ($body['payment_intent'] ?? null) === 'pi_123'
            && ($body['amount'] ?? null) === '1000'
            && ($body['reason'] ?? null) === 'requested_by_customer';
    });

    expect($refund)->toBeInstanceOf(PaymentRefund::class)
        ->and($refund->provider_refund_id)->toBe('re_123')
        ->and($refund->status)->toBe(PaymentRefundStatus::Succeeded)
        ->and($refund->amount)->toBe(1000)
        ->and($refund->provider_payment_intent_id)->toBe('pi_123');
});
