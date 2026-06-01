<?php

declare(strict_types=1);

use Capell\Payments\Enums\ResourceEnum;
use Capell\Payments\Filament\Resources\CheckoutSessions\CheckoutSessionResource;
use Capell\Payments\Filament\Resources\Customers\PaymentCustomerResource;
use Capell\Payments\Filament\Resources\Disputes\PaymentDisputeResource;
use Capell\Payments\Filament\Resources\PaymentIntents\PaymentIntentResource;
use Capell\Payments\Filament\Resources\Refunds\PaymentRefundResource;
use Capell\Payments\Filament\Resources\Subscriptions\SubscriptionResource;
use Capell\Payments\Filament\Resources\WebhookEvents\PaymentWebhookEventResource;
use Capell\Payments\Models\CheckoutSession;
use Capell\Payments\Models\PaymentCustomer;
use Capell\Payments\Models\PaymentDispute;
use Capell\Payments\Models\PaymentIntent;
use Capell\Payments\Models\PaymentRefund;
use Capell\Payments\Models\PaymentWebhookEvent;
use Capell\Payments\Models\Subscription;
use Capell\Payments\Policies\CheckoutSessionPolicy;
use Capell\Payments\Tests\TestCase;
use Illuminate\Foundation\Auth\User;

uses(TestCase::class);

it('declares read-only payment admin resources', function (): void {
    expect(ResourceEnum::cases())->toHaveCount(7)
        ->and(PaymentCustomerResource::getModel())->toBe(PaymentCustomer::class)
        ->and(CheckoutSessionResource::getModel())->toBe(CheckoutSession::class)
        ->and(PaymentIntentResource::getModel())->toBe(PaymentIntent::class)
        ->and(SubscriptionResource::getModel())->toBe(Subscription::class)
        ->and(PaymentWebhookEventResource::getModel())->toBe(PaymentWebhookEvent::class)
        ->and(PaymentRefundResource::getModel())->toBe(PaymentRefund::class)
        ->and(PaymentDisputeResource::getModel())->toBe(PaymentDispute::class)
        ->and(PaymentCustomerResource::getNavigationLabel())->toBe(__('capell-payments::generic.resources.customers'))
        ->and(CheckoutSessionResource::getNavigationLabel())->toBe(__('capell-payments::generic.resources.checkout_sessions'))
        ->and(PaymentWebhookEventResource::getNavigationGroup())->toBe(__('capell-admin::navigation.group_monitoring'));
});

it('keeps payment admin records read only by policy', function (): void {
    $policy = new CheckoutSessionPolicy;
    $user = new User;
    $record = new CheckoutSession;

    expect($policy->viewAny($user))->toBeTrue()
        ->and($policy->view($user, $record))->toBeTrue()
        ->and($policy->create($user))->toBeFalse()
        ->and($policy->update($user, $record))->toBeFalse()
        ->and($policy->delete($user, $record))->toBeFalse()
        ->and($policy->deleteAny($user))->toBeFalse();
});
