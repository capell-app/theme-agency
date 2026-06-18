<?php

declare(strict_types=1);

use Capell\Payments\Actions\HandleStripeWebhookAction;
use Capell\Payments\Actions\ProcessStripeWebhookEventAction;
use Capell\Payments\Actions\RedactPaymentErrorMessageAction;
use Capell\Payments\Contracts\PaymentFulfillmentHandler;
use Capell\Payments\Data\PaymentFulfillmentResultData;
use Capell\Payments\Enums\PaymentWebhookEventStatus;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentWebhookEvent;
use Capell\Payments\Tests\TestCase;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Queue;

uses(TestCase::class);

beforeEach(function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::create(2026, 5, 31, 12, 0, 0));

    config()->set('capell-payments.stripe.webhook_secret', 'whsec_test_secret');
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('redacts non-prefixed opaque secret tokens and bare Bearer tokens that miss the key:value heuristic', function (): void {
    // No configured-secret knowledge and no "secret=" / "sk_" prefix to lean on:
    // a connected-account bearer token and a URL-encoded long opaque token.
    $opaqueToken = 'aZ91kQ7vP3rT8wX2mN5bL0cD4fG6hJ1q';
    $urlEncodedKey = 'sk%5Flive%5F51Hq9aZ91kQ7vP3rT8wX2mN5bL0cD4fG6';

    $redacted = RedactPaymentErrorMessageAction::run(
        new RuntimeException(
            "Request failed Bearer {$opaqueToken} for account acct_1 using key {$urlEncodedKey} oops.",
        ),
    );

    expect($redacted)->not->toContain($opaqueToken)
        ->and($redacted)->not->toContain($urlEncodedKey)
        ->and($redacted)->toContain('[redacted]');
});

it('stores a structured class+code error summary without the raw provider message on webhook failure', function (): void {
    $secretInMessage = 'sk_live_51HqLeakedSecretValue0123456789abcd';

    $throwingHandler = new class($secretInMessage) implements PaymentFulfillmentHandler
    {
        public function __construct(private string $secretInMessage) {}

        public function key(): string
        {
            return 'throwing';
        }

        public function supports(CheckoutSession $checkoutSession): bool
        {
            return true;
        }

        public function fulfill(CheckoutSession $checkoutSession): PaymentFulfillmentResultData
        {
            throw new class($this->secretInMessage) extends RuntimeException
            {
                public function __construct(string $secretInMessage)
                {
                    parent::__construct("Stripe API call failed leaking {$secretInMessage} in the message");
                }

                public function getStripeCode(): string
                {
                    return 'card_declined';
                }

                public function getError(): object
                {
                    return (object) ['type' => 'card_error'];
                }
            };
        }
    };

    app()->instance($throwingHandler::class, $throwingHandler);
    app()->tag([$throwingHandler::class], PaymentFulfillmentHandler::TAG);

    $payloadArray = [
        'id' => 'evt_failing_fulfilment',
        'object' => 'event',
        'api_version' => '2026-02-25.clover',
        'created' => CarbonImmutable::now()->getTimestamp(),
        'livemode' => false,
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => 'cs_failing_fulfilment',
                'object' => 'checkout.session',
                'mode' => 'payment',
                'status' => 'complete',
                'currency' => 'gbp',
                'amount_subtotal' => 2500,
                'amount_total' => 2500,
                'metadata' => [
                    'capell_purpose' => 'paid_download',
                    'capell_payable_type' => 'download',
                    'capell_payable_id' => 'guide',
                ],
                'completed_at' => CarbonImmutable::now()->getTimestamp(),
            ],
        ],
    ];

    $payload = json_encode($payloadArray, JSON_THROW_ON_ERROR);
    $timestamp = CarbonImmutable::now()->getTimestamp();
    $signatureHeader = sprintf(
        't=%d,v1=%s',
        $timestamp,
        hash_hmac('sha256', $timestamp . '.' . $payload, 'whsec_test_secret'),
    );

    Queue::fake();

    $event = HandleStripeWebhookAction::run($payload, $signatureHeader);

    expect(static fn () => ProcessStripeWebhookEventAction::run($event->id))
        ->toThrow(RuntimeException::class);

    $failedEvent = PaymentWebhookEvent::query()->whereKey($event->id)->firstOrFail();

    expect($failedEvent->status)->toBe(PaymentWebhookEventStatus::Failed)
        ->and($failedEvent->error)->not->toBeNull()
        // Structured, non-sensitive identifiers retained for debugging.
        ->and($failedEvent->error)->toContain('type=card_error')
        ->and($failedEvent->error)->toContain('code=card_declined')
        // The leaked secret never reaches the admin-visible column.
        ->and($failedEvent->error)->not->toContain($secretInMessage)
        ->and($failedEvent->error)->not->toContain('sk_live_51HqLeakedSecretValue');
});
