<?php

declare(strict_types=1);

use Capell\Payments\Actions\ResolveSubscriptionEntitlementAction;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\SubscriptionStatus;
use Capell\Payments\Models\PaymentCustomer;
use Capell\Payments\Models\Subscription;
use Capell\Payments\Tests\TestCase;
use Carbon\CarbonImmutable;

uses(TestCase::class);

beforeEach(function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::create(2026, 5, 31, 12, 0, 0));
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('resolves active subscription entitlement by provider customer id', function (): void {
    Subscription::query()->create([
        'provider' => PaymentProvider::Stripe->value,
        'provider_subscription_id' => 'sub_active',
        'provider_customer_id' => 'cus_active',
        'status' => SubscriptionStatus::Active->value,
        'current_period_starts_at' => CarbonImmutable::now()->subMonth(),
        'current_period_ends_at' => CarbonImmutable::now()->addMonth(),
    ]);

    $entitlement = ResolveSubscriptionEntitlementAction::run(providerCustomerId: 'cus_active');

    expect($entitlement->entitled)->toBeTrue()
        ->and($entitlement->provider)->toBe(PaymentProvider::Stripe)
        ->and($entitlement->providerCustomerId)->toBe('cus_active')
        ->and($entitlement->providerSubscriptionId)->toBe('sub_active')
        ->and($entitlement->status)->toBe(SubscriptionStatus::Active);
});

it('resolves trialing subscription entitlement by billable identity', function (): void {
    $customer = PaymentCustomer::query()->create([
        'provider' => PaymentProvider::Stripe->value,
        'provider_customer_id' => 'cus_trial',
        'billable_type' => 'user',
        'billable_id' => '42',
    ]);

    Subscription::query()->create([
        'provider' => PaymentProvider::Stripe->value,
        'provider_subscription_id' => 'sub_trial',
        'payment_customer_id' => $customer->getKey(),
        'provider_customer_id' => 'cus_trial',
        'status' => SubscriptionStatus::Trialing->value,
        'trial_ends_at' => CarbonImmutable::now()->addWeek(),
    ]);

    $entitlement = ResolveSubscriptionEntitlementAction::run(
        billableType: 'user',
        billableId: '42',
    );

    expect($entitlement->entitled)->toBeTrue()
        ->and($entitlement->providerCustomerId)->toBe('cus_trial')
        ->and($entitlement->status)->toBe(SubscriptionStatus::Trialing);
});

it('does not entitle expired active subscriptions', function (): void {
    Subscription::query()->create([
        'provider' => PaymentProvider::Stripe->value,
        'provider_subscription_id' => 'sub_expired',
        'provider_customer_id' => 'cus_expired',
        'status' => SubscriptionStatus::Active->value,
        'current_period_starts_at' => CarbonImmutable::now()->subMonths(2),
        'current_period_ends_at' => CarbonImmutable::now()->subDay(),
    ]);

    $entitlement = ResolveSubscriptionEntitlementAction::run(providerCustomerId: 'cus_expired');

    expect($entitlement->entitled)->toBeFalse()
        ->and($entitlement->providerSubscriptionId)->toBe('sub_expired')
        ->and($entitlement->status)->toBe(SubscriptionStatus::Active);
});

it('does not entitle canceled or missing subscriptions', function (): void {
    Subscription::query()->create([
        'provider' => PaymentProvider::Stripe->value,
        'provider_subscription_id' => 'sub_canceled',
        'provider_customer_id' => 'cus_canceled',
        'status' => SubscriptionStatus::Canceled->value,
        'canceled_at' => CarbonImmutable::now()->subDay(),
    ]);

    $canceled = ResolveSubscriptionEntitlementAction::run(providerCustomerId: 'cus_canceled');
    $missing = ResolveSubscriptionEntitlementAction::run(providerCustomerId: 'cus_missing');

    expect($canceled->entitled)->toBeFalse()
        ->and($canceled->providerSubscriptionId)->toBeNull()
        ->and($missing->entitled)->toBeFalse()
        ->and($missing->providerCustomerId)->toBe('cus_missing');
});
