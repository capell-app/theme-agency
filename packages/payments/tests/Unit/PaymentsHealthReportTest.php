<?php

declare(strict_types=1);

use Capell\Payments\Actions\BuildPaymentsHealthReportAction;
use Capell\Payments\Enums\PaymentDisputeStatus;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentWebhookEventStatus;
use Capell\Payments\Health\PaymentsHealthCheck;
use Capell\Payments\Models\PaymentDispute;
use Capell\Payments\Models\PaymentWebhookEvent;
use Capell\Payments\Tests\TestCase;
use Carbon\CarbonImmutable;

uses(TestCase::class);

beforeEach(function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::create(2026, 5, 31, 12, 0, 0));
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('warns when payments are configured but no stripe webhooks have been received', function (): void {
    config()->set('capell-payments.stripe.secret_key', 'sk_test_123');
    config()->set('capell-payments.stripe.webhook_secret', 'whsec_123');

    $report = PaymentsHealthCheck::report();

    expect(PaymentsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and($report->status)->toBe('warning')
        ->and($report->stripeSecretConfigured)->toBeTrue()
        ->and($report->stripeWebhookSecretConfigured)->toBeTrue()
        ->and($report->recordedWebhookEvents)->toBe(0)
        ->and($report->webhooksFresh)->toBeFalse()
        ->and($report->issues)->toContain('No Stripe webhook events have been recorded yet.');
});

it('passes when stripe configuration and recent webhook delivery are healthy', function (): void {
    config()->set('capell-payments.stripe.secret_key', 'sk_test_123');
    config()->set('capell-payments.stripe.webhook_secret', 'whsec_123');

    PaymentWebhookEvent::query()->create([
        'provider' => PaymentProvider::Stripe->value,
        'provider_event_id' => 'evt_recent',
        'event_type' => 'payment_intent.succeeded',
        'livemode' => false,
        'status' => PaymentWebhookEventStatus::Processed->value,
        'payload' => ['id' => 'evt_recent'],
        'received_at' => CarbonImmutable::now()->subHour(),
        'processed_at' => CarbonImmutable::now()->subHour(),
    ]);

    $report = BuildPaymentsHealthReportAction::run();

    expect($report->status)->toBe('passed')
        ->and($report->recordedWebhookEvents)->toBe(1)
        ->and($report->failedWebhookEvents)->toBe(0)
        ->and($report->unresolvedDisputes)->toBe(0)
        ->and($report->webhooksFresh)->toBeTrue()
        ->and($report->issues)->toBe([]);
});

it('fails when webhook processing failed or disputes need a response', function (): void {
    config()->set('capell-payments.stripe.secret_key', 'sk_test_123');
    config()->set('capell-payments.stripe.webhook_secret', 'whsec_123');

    PaymentWebhookEvent::query()->create([
        'provider' => PaymentProvider::Stripe->value,
        'provider_event_id' => 'evt_failed',
        'event_type' => 'payment_intent.succeeded',
        'livemode' => false,
        'status' => PaymentWebhookEventStatus::Failed->value,
        'payload' => ['id' => 'evt_failed'],
        'received_at' => CarbonImmutable::now()->subMinutes(10),
        'failed_at' => CarbonImmutable::now()->subMinutes(9),
        'error' => 'Handler failed.',
    ]);

    PaymentDispute::query()->create([
        'provider' => PaymentProvider::Stripe->value,
        'provider_dispute_id' => 'dp_needs_response',
        'status' => PaymentDisputeStatus::NeedsResponse->value,
        'amount' => 2500,
        'currency' => 'gbp',
        'is_charge_refundable' => true,
        'provider_payload' => ['id' => 'dp_needs_response'],
    ]);

    $report = BuildPaymentsHealthReportAction::run();

    expect($report->status)->toBe('failed')
        ->and($report->failedWebhookEvents)->toBe(1)
        ->and($report->unresolvedDisputes)->toBe(1)
        ->and($report->issues)->toContain('1 Stripe webhook event(s) failed processing.')
        ->and($report->issues)->toContain('1 payment dispute(s) need a response.');
});
