<?php

declare(strict_types=1);

use Capell\Payments\Actions\HandleStripeWebhookAction;
use Capell\Payments\Actions\ProcessStripeWebhookEventAction;
use Capell\Payments\Actions\VerifyStripeWebhookSignatureAction;
use Capell\Payments\Contracts\PaymentFulfillmentHandler;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Enums\PaymentDisputeStatus;
use Capell\Payments\Enums\PaymentIntentStatus;
use Capell\Payments\Enums\PaymentRefundStatus;
use Capell\Payments\Enums\PaymentWebhookEventStatus;
use Capell\Payments\Enums\SubscriptionStatus;
use Capell\Payments\Exceptions\PaymentGatewayConfigurationException;
use Capell\Payments\Exceptions\StripeWebhookSignatureException;
use Capell\Payments\Jobs\ProcessStripeWebhookEventJob;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentDispute;
use Capell\Payments\Models\PaymentDownloadEntitlement;
use Capell\Payments\Models\PaymentIntent;
use Capell\Payments\Models\PaymentRefund;
use Capell\Payments\Models\PaymentWebhookEvent;
use Capell\Payments\Models\Subscription;
use Capell\Payments\Support\Fulfillment\PaidDownloadFulfillmentHandler;
use Capell\Payments\Tests\Fakes\FakePaymentFulfillmentHandler;
use Capell\Payments\Tests\TestCase;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Queue;

uses(TestCase::class);

beforeEach(function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::create(2026, 5, 31, 12, 0, 0));

    config()->set('capell-payments.stripe.webhook_secret', 'whsec_test_secret');

    FakePaymentFulfillmentHandler::$fulfilledSessionIds = [];
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('verifies stripe webhook signatures against the raw payload', function (): void {
    $payload = stripeWebhookPayload([
        'id' => 'evt_signature_test',
        'type' => 'payment_intent.succeeded',
    ]);

    $eventPayload = VerifyStripeWebhookSignatureAction::run(
        $payload,
        stripeSignatureHeader($payload),
        'whsec_test_secret',
    );

    expect($eventPayload['id'])->toBe('evt_signature_test')
        ->and($eventPayload['type'])->toBe('payment_intent.succeeded');
});

it('rejects invalid stripe webhook signatures', function (): void {
    $payload = stripeWebhookPayload([
        'id' => 'evt_invalid_signature',
        'type' => 'payment_intent.succeeded',
    ]);

    VerifyStripeWebhookSignatureAction::run(
        $payload,
        stripeSignatureHeader($payload, 'wrong_secret'),
        'whsec_test_secret',
    );
})->throws(StripeWebhookSignatureException::class);

it('records checkout session webhooks idempotently before queued processing updates the session record', function (): void {
    app()->bind(FakePaymentFulfillmentHandler::class);
    app()->tag([FakePaymentFulfillmentHandler::class], PaymentFulfillmentHandler::TAG);
    Queue::fake();

    $payload = stripeWebhookPayload([
        'id' => 'evt_checkout_completed',
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => 'cs_test_completed',
                'object' => 'checkout.session',
                'mode' => 'payment',
                'status' => 'complete',
                'currency' => 'gbp',
                'amount_subtotal' => 2500,
                'amount_total' => 2500,
                'customer' => 'cus_test_123',
                'customer_details' => [
                    'email' => 'reader@example.com',
                    'name' => 'Reader Example',
                ],
                'payment_intent' => 'pi_test_123',
                'client_reference_id' => 'order_123',
                'metadata' => [
                    'capell_purpose' => 'paid_download',
                    'capell_site_id' => '42',
                    'capell_payable_type' => 'download',
                    'capell_payable_id' => 'guide',
                ],
                'completed_at' => CarbonImmutable::now()->getTimestamp(),
            ],
        ],
    ]);

    $firstEvent = HandleStripeWebhookAction::run($payload, stripeSignatureHeader($payload));
    $processedFirstEvent = ProcessStripeWebhookEventAction::run((int) $firstEvent->getKey());
    $secondEvent = HandleStripeWebhookAction::run($payload, stripeSignatureHeader($payload));
    $processedSecondEvent = ProcessStripeWebhookEventAction::run((int) $secondEvent->getKey());

    expect($firstEvent->is($secondEvent))->toBeTrue()
        ->and(PaymentWebhookEvent::query()->count())->toBe(1)
        ->and($firstEvent->status)->toBe(PaymentWebhookEventStatus::Received)
        ->and($processedFirstEvent->status)->toBe(PaymentWebhookEventStatus::Processed)
        ->and($processedSecondEvent->status)->toBe(PaymentWebhookEventStatus::Processed);

    Queue::assertPushed(ProcessStripeWebhookEventJob::class, 1);

    $checkoutSession = CheckoutSession::query()->firstOrFail();

    expect($checkoutSession->provider_session_id)->toBe('cs_test_completed')
        ->and($checkoutSession->status)->toBe(CheckoutSessionStatus::Complete)
        ->and($checkoutSession->amount_total)->toBe(2500)
        ->and($checkoutSession->payable_type)->toBe('download')
        ->and($checkoutSession->payable_id)->toBe('guide')
        ->and($checkoutSession->reference_id)->toBe('order_123')
        ->and(FakePaymentFulfillmentHandler::$fulfilledSessionIds)->toBe(['cs_test_completed']);
});

it('does not extend paid download entitlement expiry when checkout fulfilment replays', function (): void {
    app()->bind(PaidDownloadFulfillmentHandler::class);
    app()->tag([PaidDownloadFulfillmentHandler::class], PaymentFulfillmentHandler::TAG);

    $payload = stripeWebhookPayload([
        'id' => 'evt_paid_download_completed',
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => 'cs_test_paid_download_replay',
                'object' => 'checkout.session',
                'mode' => 'payment',
                'status' => 'complete',
                'currency' => 'gbp',
                'amount_subtotal' => 2500,
                'amount_total' => 2500,
                'client_reference_id' => 'order_paid_download',
                'metadata' => [
                    'capell_purpose' => 'paid_download',
                    'capell_site_id' => '42',
                    'capell_payable_type' => 'download',
                    'capell_payable_id' => 'guide',
                    'download_path' => 'paid/guide.pdf',
                    'download_name' => 'Original guide',
                    'download_ttl_minutes' => '30',
                ],
                'completed_at' => CarbonImmutable::now()->getTimestamp(),
            ],
        ],
    ]);

    processStripeWebhookPayload($payload);

    $entitlement = PaymentDownloadEntitlement::query()->firstOrFail();
    $originalExpiresAtTimestamp = $entitlement->expires_at?->getTimestamp();
    $originalFulfilledAtTimestamp = $entitlement->fulfilled_at?->getTimestamp();

    expect($originalExpiresAtTimestamp)->toBe(CarbonImmutable::now()->addMinutes(30)->getTimestamp())
        ->and($originalFulfilledAtTimestamp)->toBe(CarbonImmutable::now()->getTimestamp());

    CarbonImmutable::setTestNow(CarbonImmutable::create(2026, 6, 1, 12, 0, 0));

    $replayPayload = stripeWebhookPayload([
        'id' => 'evt_paid_download_completed_replay',
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => 'cs_test_paid_download_replay',
                'object' => 'checkout.session',
                'mode' => 'payment',
                'status' => 'complete',
                'currency' => 'gbp',
                'amount_subtotal' => 2500,
                'amount_total' => 2500,
                'client_reference_id' => 'order_paid_download',
                'metadata' => [
                    'capell_purpose' => 'paid_download',
                    'capell_site_id' => '42',
                    'capell_payable_type' => 'download',
                    'capell_payable_id' => 'guide',
                    'download_path' => 'paid/updated-guide.pdf',
                    'download_name' => 'Updated guide',
                    'download_ttl_minutes' => '1440',
                ],
                'completed_at' => CarbonImmutable::now()->getTimestamp(),
            ],
        ],
    ]);

    processStripeWebhookPayload($replayPayload);

    expect(PaymentDownloadEntitlement::query()->count())->toBe(1)
        ->and($entitlement->refresh()->download_name)->toBe('Updated guide')
        ->and($entitlement->path)->toBe('paid/updated-guide.pdf')
        ->and($entitlement->expires_at?->getTimestamp())->toBe($originalExpiresAtTimestamp)
        ->and($entitlement->fulfilled_at?->getTimestamp())->toBe($originalFulfilledAtTimestamp);
});

it('records payment intent webhooks', function (): void {
    $payload = stripeWebhookPayload([
        'id' => 'evt_payment_intent_succeeded',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_test_succeeded',
                'object' => 'payment_intent',
                'status' => 'succeeded',
                'amount' => 4500,
                'currency' => 'gbp',
                'customer' => 'cus_test_123',
                'metadata' => [
                    'capell_checkout_session_id' => 'cs_test_completed',
                ],
            ],
        ],
    ]);

    $event = processStripeWebhookPayload($payload);

    $paymentIntent = PaymentIntent::query()->firstOrFail();

    expect($event->status)->toBe(PaymentWebhookEventStatus::Processed)
        ->and($paymentIntent->provider_payment_intent_id)->toBe('pi_test_succeeded')
        ->and($paymentIntent->status)->toBe(PaymentIntentStatus::Succeeded)
        ->and($paymentIntent->amount)->toBe(4500)
        ->and($paymentIntent->provider_session_id)->toBe('cs_test_completed');
});

it('records subscription webhooks', function (): void {
    $payload = stripeWebhookPayload([
        'id' => 'evt_subscription_updated',
        'type' => 'customer.subscription.updated',
        'data' => [
            'object' => [
                'id' => 'sub_test_active',
                'object' => 'subscription',
                'status' => 'active',
                'customer' => 'cus_test_123',
                'current_period_start' => CarbonImmutable::now()->subMonth()->getTimestamp(),
                'current_period_end' => CarbonImmutable::now()->addMonth()->getTimestamp(),
                'metadata' => [
                    'capell_checkout_session_id' => 'cs_test_subscription',
                ],
            ],
        ],
    ]);

    $event = processStripeWebhookPayload($payload);

    $subscription = Subscription::query()->firstOrFail();

    expect($event->status)->toBe(PaymentWebhookEventStatus::Processed)
        ->and($subscription->provider_subscription_id)->toBe('sub_test_active')
        ->and($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->provider_session_id)->toBe('cs_test_subscription')
        ->and($subscription->current_period_ends_at)->not->toBeNull();
});

it('records refund webhooks', function (): void {
    $payload = stripeWebhookPayload([
        'id' => 'evt_refund_updated',
        'type' => 'refund.updated',
        'data' => [
            'object' => [
                'id' => 're_test_succeeded',
                'object' => 'refund',
                'status' => 'succeeded',
                'amount' => 1500,
                'currency' => 'gbp',
                'payment_intent' => 'pi_test_succeeded',
                'charge' => 'ch_test_123',
                'reason' => 'requested_by_customer',
                'metadata' => [
                    'support_ticket' => 'SUP-123',
                ],
            ],
        ],
    ]);

    $event = processStripeWebhookPayload($payload);

    $refund = PaymentRefund::query()->firstOrFail();

    expect($event->status)->toBe(PaymentWebhookEventStatus::Processed)
        ->and($refund->provider_refund_id)->toBe('re_test_succeeded')
        ->and($refund->status)->toBe(PaymentRefundStatus::Succeeded)
        ->and($refund->amount)->toBe(1500)
        ->and($refund->provider_payment_intent_id)->toBe('pi_test_succeeded')
        ->and($refund->provider_charge_id)->toBe('ch_test_123');
});

it('records refunds from charge refunded webhook payloads and backfills charge details', function (): void {
    $payload = stripeWebhookPayload([
        'id' => 'evt_charge_refunded',
        'type' => 'charge.refunded',
        'data' => [
            'object' => [
                'id' => 'ch_test_refunded',
                'object' => 'charge',
                'payment_intent' => 'pi_test_refunded',
                'refunds' => [
                    'data' => [
                        [
                            'id' => 're_charge_one',
                            'status' => 'pending',
                            'amount' => '500',
                            'currency' => 'gbp',
                        ],
                        [
                            'id' => 're_charge_two',
                            'status' => 'failed',
                            'amount' => 700,
                            'currency' => 'gbp',
                            'charge' => 'ch_override',
                            'payment_intent' => 'pi_override',
                        ],
                        'not-a-refund-object',
                    ],
                ],
            ],
        ],
    ]);

    $event = processStripeWebhookPayload($payload);
    $refunds = PaymentRefund::query()->orderBy('provider_refund_id')->get();

    expect($event->status)->toBe(PaymentWebhookEventStatus::Processed)
        ->and($refunds)->toHaveCount(2);

    $firstRefund = $refunds->get(0);
    $secondRefund = $refunds->get(1);

    expect($firstRefund)->toBeInstanceOf(PaymentRefund::class)
        ->and($secondRefund)->toBeInstanceOf(PaymentRefund::class);

    if (! $firstRefund instanceof PaymentRefund || ! $secondRefund instanceof PaymentRefund) {
        return;
    }

    expect($firstRefund->provider_refund_id)->toBe('re_charge_one')
        ->and($firstRefund->status)->toBe(PaymentRefundStatus::Pending)
        ->and($firstRefund->amount)->toBe(500)
        ->and($firstRefund->provider_charge_id)->toBe('ch_test_refunded')
        ->and($firstRefund->provider_payment_intent_id)->toBe('pi_test_refunded')
        ->and($secondRefund->provider_refund_id)->toBe('re_charge_two')
        ->and($secondRefund->status)->toBe(PaymentRefundStatus::Failed)
        ->and($secondRefund->provider_charge_id)->toBe('ch_override')
        ->and($secondRefund->provider_payment_intent_id)->toBe('pi_override');
});

it('records dispute webhooks', function (): void {
    $payload = stripeWebhookPayload([
        'id' => 'evt_dispute_created',
        'type' => 'charge.dispute.created',
        'data' => [
            'object' => [
                'id' => 'dp_test_needs_response',
                'object' => 'dispute',
                'status' => 'needs_response',
                'amount' => 2500,
                'currency' => 'gbp',
                'payment_intent' => 'pi_test_disputed',
                'charge' => 'ch_test_disputed',
                'reason' => 'fraudulent',
                'is_charge_refundable' => true,
                'evidence_details' => [
                    'due_by' => CarbonImmutable::now()->addDays(7)->getTimestamp(),
                ],
            ],
        ],
    ]);

    $event = processStripeWebhookPayload($payload);

    $dispute = PaymentDispute::query()->firstOrFail();

    expect($event->status)->toBe(PaymentWebhookEventStatus::Processed)
        ->and($dispute->provider_dispute_id)->toBe('dp_test_needs_response')
        ->and($dispute->status)->toBe(PaymentDisputeStatus::NeedsResponse)
        ->and($dispute->amount)->toBe(2500)
        ->and($dispute->provider_payment_intent_id)->toBe('pi_test_disputed')
        ->and($dispute->provider_charge_id)->toBe('ch_test_disputed')
        ->and($dispute->is_charge_refundable)->toBeTrue()
        ->and($dispute->evidence_due_at)->not->toBeNull();
});

it('marks unsupported stripe webhook event types as ignored without mutating payment records', function (): void {
    $payload = stripeWebhookPayload([
        'id' => 'evt_customer_created',
        'type' => 'customer.created',
        'data' => [
            'object' => [
                'id' => 'cus_ignored',
                'object' => 'customer',
            ],
        ],
    ]);

    $event = processStripeWebhookPayload($payload);

    expect($event->status)->toBe(PaymentWebhookEventStatus::Ignored)
        ->and($event->processed_at)->not->toBeNull()
        ->and(PaymentIntent::query()->count())->toBe(0)
        ->and(CheckoutSession::query()->count())->toBe(0)
        ->and(PaymentRefund::query()->count())->toBe(0);
});

it('requires a configured stripe webhook secret before verifying payloads', function (): void {
    config()->set('capell-payments.stripe.webhook_secret');

    $payload = stripeWebhookPayload([
        'id' => 'evt_missing_secret',
        'type' => 'payment_intent.succeeded',
    ]);

    HandleStripeWebhookAction::run($payload, stripeSignatureHeader($payload));
})->throws(PaymentGatewayConfigurationException::class);

it('accepts the package stripe webhook route without csrf', function (): void {
    Queue::fake();

    $payload = stripeWebhookPayload([
        'id' => 'evt_route_test',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_route_test',
                'object' => 'payment_intent',
                'status' => 'succeeded',
                'amount' => 1200,
                'currency' => 'gbp',
            ],
        ],
    ]);

    $this
        ->call('POST', '/capell/payments/stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => stripeSignatureHeader($payload),
            'CONTENT_TYPE' => 'application/json',
        ], $payload)
        ->assertOk()
        ->assertJson([
            'ok' => true,
            'event_id' => 'evt_route_test',
            'status' => 'received',
        ]);

    expect(PaymentIntent::query()->count())->toBe(0);

    Queue::assertPushed(ProcessStripeWebhookEventJob::class, fn (ProcessStripeWebhookEventJob $job): bool => PaymentWebhookEvent::query()
        ->whereKey($job->webhookEventId)
        ->where('provider_event_id', 'evt_route_test')
        ->exists());
});

it('queues stripe webhook processing after verified intake', function (): void {
    Queue::fake();

    $payload = stripeWebhookPayload([
        'id' => 'evt_queued_intake',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_queued_intake',
                'object' => 'payment_intent',
                'status' => 'succeeded',
                'amount' => 1200,
                'currency' => 'gbp',
            ],
        ],
    ]);

    $event = HandleStripeWebhookAction::run($payload, stripeSignatureHeader($payload));

    expect($event->status)->toBe(PaymentWebhookEventStatus::Received)
        ->and(PaymentIntent::query()->count())->toBe(0);

    Queue::assertPushed(ProcessStripeWebhookEventJob::class, fn (ProcessStripeWebhookEventJob $job): bool => $job->webhookEventId === (int) $event->getKey()
            && $job->queue === 'payments');
});

/**
 * @param  array<string, mixed>  $overrides
 */
function stripeWebhookPayload(array $overrides): string
{
    $payload = array_replace_recursive([
        'id' => 'evt_test',
        'object' => 'event',
        'api_version' => '2026-02-25.clover',
        'created' => CarbonImmutable::now()->getTimestamp(),
        'livemode' => false,
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [],
        ],
    ], $overrides);

    return json_encode($payload, JSON_THROW_ON_ERROR);
}

function stripeSignatureHeader(string $payload, string $secret = 'whsec_test_secret'): string
{
    $timestamp = CarbonImmutable::now()->getTimestamp();
    $signature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

    return sprintf('t=%d,v1=%s', $timestamp, $signature);
}

function processStripeWebhookPayload(string $payload): PaymentWebhookEvent
{
    Queue::fake();

    $event = HandleStripeWebhookAction::run($payload, stripeSignatureHeader($payload));

    return ProcessStripeWebhookEventAction::run((int) $event->getKey());
}
