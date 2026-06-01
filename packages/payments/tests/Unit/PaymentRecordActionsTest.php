<?php

declare(strict_types=1);

use Capell\Payments\Actions\RecordCheckoutSessionAction;
use Capell\Payments\Actions\RecordPaymentCustomerAction;
use Capell\Payments\Actions\RecordPaymentDisputeAction;
use Capell\Payments\Actions\RecordPaymentIntentAction;
use Capell\Payments\Actions\RecordPaymentRefundAction;
use Capell\Payments\Actions\RecordSubscriptionAction;
use Capell\Payments\Data\CheckoutSessionData;
use Capell\Payments\Data\CreateCheckoutSessionData;
use Capell\Payments\Data\PaymentCustomerData;
use Capell\Payments\Data\PaymentDisputeData;
use Capell\Payments\Data\PaymentIntentData;
use Capell\Payments\Data\PaymentRefundData;
use Capell\Payments\Data\SubscriptionData;
use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Enums\PaymentDisputeStatus;
use Capell\Payments\Enums\PaymentIntentStatus;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentPurpose;
use Capell\Payments\Enums\PaymentRefundStatus;
use Capell\Payments\Enums\SubscriptionStatus;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentCustomer;
use Capell\Payments\Models\PaymentDispute;
use Capell\Payments\Models\PaymentIntent;
use Capell\Payments\Models\PaymentRefund;
use Capell\Payments\Models\Subscription;
use Capell\Payments\Tests\TestCase;
use Carbon\CarbonImmutable;

uses(TestCase::class);

it('records provider payment objects idempotently through their natural keys', function (): void {
    $customer = RecordPaymentCustomerAction::run(new PaymentCustomerData(
        provider: PaymentProvider::Stripe,
        providerCustomerId: 'cus_record_123',
        siteId: 10,
        billableType: 'user',
        billableId: '42',
        email: 'first@example.test',
        name: 'First Customer',
        metadata: ['source' => 'checkout'],
        providerPayload: ['object' => 'customer'],
    ));

    $updatedCustomer = RecordPaymentCustomerAction::run(new PaymentCustomerData(
        provider: PaymentProvider::Stripe,
        providerCustomerId: 'cus_record_123',
        siteId: 11,
        billableType: 'account',
        billableId: '84',
        email: 'updated@example.test',
        name: 'Updated Customer',
        metadata: ['source' => 'portal'],
        providerPayload: ['object' => 'customer', 'livemode' => false],
    ));

    $intent = RecordPaymentIntentAction::run(new PaymentIntentData(
        provider: PaymentProvider::Stripe,
        providerPaymentIntentId: 'pi_record_123',
        status: PaymentIntentStatus::RequiresPaymentMethod,
        amount: 2500,
        currency: 'gbp',
        providerCustomerId: 'cus_record_123',
        providerSessionId: 'cs_record_123',
        metadata: ['cart' => 'starter'],
        providerPayload: ['object' => 'payment_intent'],
    ));

    $updatedIntent = RecordPaymentIntentAction::run(new PaymentIntentData(
        provider: PaymentProvider::Stripe,
        providerPaymentIntentId: 'pi_record_123',
        status: PaymentIntentStatus::Succeeded,
        amount: 3000,
        currency: 'gbp',
        providerCustomerId: 'cus_record_123',
        providerSessionId: 'cs_record_123',
        metadata: ['cart' => 'pro'],
        providerPayload: ['object' => 'payment_intent', 'status' => 'succeeded'],
    ));

    $refund = RecordPaymentRefundAction::run(new PaymentRefundData(
        provider: PaymentProvider::Stripe,
        providerRefundId: 're_record_123',
        status: PaymentRefundStatus::Pending,
        amount: 500,
        currency: 'gbp',
        providerPaymentIntentId: 'pi_record_123',
        providerChargeId: 'ch_record_123',
        reason: 'requested_by_customer',
        metadata: ['ticket' => 'A-1'],
        providerPayload: ['object' => 'refund'],
    ));

    $updatedRefund = RecordPaymentRefundAction::run(new PaymentRefundData(
        provider: PaymentProvider::Stripe,
        providerRefundId: 're_record_123',
        status: PaymentRefundStatus::Succeeded,
        amount: 750,
        currency: 'gbp',
        providerPaymentIntentId: 'pi_record_123',
        providerChargeId: 'ch_record_123',
        reason: 'duplicate',
        metadata: ['ticket' => 'A-2'],
        providerPayload: ['object' => 'refund', 'status' => 'succeeded'],
    ));

    $evidenceDueAt = CarbonImmutable::parse('2026-06-07 12:00:00');
    $dispute = RecordPaymentDisputeAction::run(new PaymentDisputeData(
        provider: PaymentProvider::Stripe,
        providerDisputeId: 'dp_record_123',
        status: PaymentDisputeStatus::WarningNeedsResponse,
        amount: 1250,
        currency: 'gbp',
        providerPaymentIntentId: 'pi_record_123',
        providerChargeId: 'ch_record_123',
        reason: 'fraudulent',
        isChargeRefundable: true,
        evidenceDueAt: $evidenceDueAt,
        providerPayload: ['object' => 'dispute'],
    ));

    $updatedDispute = RecordPaymentDisputeAction::run(new PaymentDisputeData(
        provider: PaymentProvider::Stripe,
        providerDisputeId: 'dp_record_123',
        status: PaymentDisputeStatus::Won,
        amount: 1250,
        currency: 'gbp',
        providerPaymentIntentId: 'pi_record_123',
        providerChargeId: 'ch_record_123',
        reason: 'fraudulent',
        isChargeRefundable: false,
        evidenceDueAt: $evidenceDueAt,
        providerPayload: ['object' => 'dispute', 'status' => 'won'],
    ));

    $periodStart = CarbonImmutable::parse('2026-06-01 00:00:00');
    $periodEnd = CarbonImmutable::parse('2026-07-01 00:00:00');
    $subscription = RecordSubscriptionAction::run(new SubscriptionData(
        provider: PaymentProvider::Stripe,
        providerSubscriptionId: 'sub_record_123',
        status: SubscriptionStatus::Trialing,
        providerCustomerId: 'cus_record_123',
        providerSessionId: 'cs_record_123',
        trialEndsAt: $periodStart,
        currentPeriodStartsAt: $periodStart,
        currentPeriodEndsAt: $periodEnd,
        metadata: ['plan' => 'starter'],
        providerPayload: ['object' => 'subscription'],
    ));

    $updatedSubscription = RecordSubscriptionAction::run(new SubscriptionData(
        provider: PaymentProvider::Stripe,
        providerSubscriptionId: 'sub_record_123',
        status: SubscriptionStatus::Active,
        providerCustomerId: 'cus_record_123',
        providerSessionId: 'cs_record_123',
        currentPeriodStartsAt: $periodStart,
        currentPeriodEndsAt: $periodEnd,
        metadata: ['plan' => 'pro'],
        providerPayload: ['object' => 'subscription', 'status' => 'active'],
    ));

    expect($updatedCustomer?->getKey())->toBe($customer?->getKey())
        ->and($updatedIntent->getKey())->toBe($intent->getKey())
        ->and($updatedRefund->getKey())->toBe($refund->getKey())
        ->and($updatedDispute->getKey())->toBe($dispute->getKey())
        ->and($updatedSubscription->getKey())->toBe($subscription->getKey())
        ->and(PaymentCustomer::query()->count())->toBe(1)
        ->and(PaymentIntent::query()->count())->toBe(1)
        ->and(PaymentRefund::query()->count())->toBe(1)
        ->and(PaymentDispute::query()->count())->toBe(1)
        ->and(Subscription::query()->count())->toBe(1)
        ->and($updatedCustomer?->email)->toBe('updated@example.test')
        ->and($updatedCustomer?->metadata)->toBe(['source' => 'portal'])
        ->and($updatedIntent->status)->toBe(PaymentIntentStatus::Succeeded)
        ->and($updatedIntent->amount)->toBe(3000)
        ->and($updatedIntent->customer?->is($updatedCustomer))->toBeTrue()
        ->and($updatedRefund->status)->toBe(PaymentRefundStatus::Succeeded)
        ->and($updatedRefund->reason)->toBe('duplicate')
        ->and($updatedDispute->status)->toBe(PaymentDisputeStatus::Won)
        ->and($updatedDispute->is_charge_refundable)->toBeFalse()
        ->and($updatedSubscription->status)->toBe(SubscriptionStatus::Active)
        ->and($updatedSubscription->metadata)->toBe(['plan' => 'pro'])
        ->and($updatedSubscription->customer?->is($updatedCustomer))->toBeTrue();
});

it('records checkout sessions with source context and links them to provider customers', function (): void {
    $sourceData = new CreateCheckoutSessionData(
        mode: CheckoutMode::Payment,
        purpose: PaymentPurpose::PaidDownload,
        successUrl: 'https://example.test/success',
        cancelUrl: 'https://example.test/cancel',
        lineItems: [],
        siteId: 22,
        billableType: 'user',
        billableId: '42',
        payableType: 'download',
        payableId: 'guide',
        sourceType: 'form_submission',
        sourceId: '900',
        referenceId: 'order-900',
    );

    $session = RecordCheckoutSessionAction::run(new CheckoutSessionData(
        provider: PaymentProvider::Stripe,
        providerSessionId: 'cs_record_123',
        status: CheckoutSessionStatus::Open,
        mode: CheckoutMode::Payment,
        purpose: PaymentPurpose::PaidDownload,
        url: 'https://checkout.stripe.test/session',
        currency: 'gbp',
        amountSubtotal: 2000,
        amountTotal: 2400,
        providerCustomerId: 'cus_checkout_123',
        customerEmail: 'reader@example.test',
        customerName: 'Reader Example',
        providerPaymentIntentId: 'pi_checkout_123',
        expiresAt: CarbonImmutable::parse('2026-06-01 12:30:00'),
        metadata: ['download_path' => 'paid/guide.pdf'],
        providerPayload: ['object' => 'checkout.session'],
    ), $sourceData);

    $updatedSession = RecordCheckoutSessionAction::run(new CheckoutSessionData(
        provider: PaymentProvider::Stripe,
        providerSessionId: 'cs_record_123',
        status: CheckoutSessionStatus::Complete,
        mode: CheckoutMode::Payment,
        purpose: PaymentPurpose::PaidDownload,
        url: null,
        currency: 'gbp',
        amountSubtotal: 2000,
        amountTotal: 2400,
        providerCustomerId: 'cus_checkout_123',
        customerEmail: 'reader@example.test',
        customerName: 'Reader Example',
        providerPaymentIntentId: 'pi_checkout_123',
        completedAt: CarbonImmutable::parse('2026-06-01 12:05:00'),
        metadata: ['download_path' => 'paid/guide.pdf', 'download_name' => 'Guide'],
        providerPayload: ['object' => 'checkout.session', 'status' => 'complete'],
    ), $sourceData);

    expect($updatedSession->getKey())->toBe($session->getKey())
        ->and(CheckoutSession::query()->count())->toBe(1)
        ->and(PaymentCustomer::query()->where('provider_customer_id', 'cus_checkout_123')->count())->toBe(1)
        ->and($updatedSession->customer?->email)->toBe('reader@example.test')
        ->and($updatedSession->status)->toBe(CheckoutSessionStatus::Complete)
        ->and($updatedSession->site_id)->toBe(22)
        ->and($updatedSession->billable_type)->toBe('user')
        ->and($updatedSession->payable_type)->toBe('download')
        ->and($updatedSession->payable_id)->toBe('guide')
        ->and($updatedSession->source_type)->toBe('form_submission')
        ->and($updatedSession->reference_id)->toBe('order-900')
        ->and($updatedSession->metadata)->toBe(['download_path' => 'paid/guide.pdf', 'download_name' => 'Guide'])
        ->and($updatedSession->completed_at?->toDateTimeString())->toBe('2026-06-01 12:05:00');
});
