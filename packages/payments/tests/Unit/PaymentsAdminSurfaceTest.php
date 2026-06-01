<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;
use Capell\Payments\Enums\ResourceEnum;
use Capell\Payments\Filament\Resources\CheckoutSessions\CheckoutSessionResource;
use Capell\Payments\Filament\Resources\Customers\PaymentCustomerResource;
use Capell\Payments\Filament\Resources\Disputes\PaymentDisputeResource;
use Capell\Payments\Filament\Resources\PaymentIntents\PaymentIntentResource;
use Capell\Payments\Filament\Resources\Refunds\PaymentRefundResource;
use Capell\Payments\Filament\Resources\Subscriptions\SubscriptionResource;
use Capell\Payments\Filament\Resources\WebhookEvents\PaymentWebhookEventResource;
use Capell\Payments\Manifest\CheckoutSessionResourceContribution;
use Capell\Payments\Manifest\PaymentCustomerResourceContribution;
use Capell\Payments\Manifest\PaymentDisputeResourceContribution;
use Capell\Payments\Manifest\PaymentIntentResourceContribution;
use Capell\Payments\Manifest\PaymentRefundResourceContribution;
use Capell\Payments\Manifest\PaymentWebhookEventResourceContribution;
use Capell\Payments\Manifest\SubscriptionResourceContribution;
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

it('declares admin resources in the package manifest', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['contributes'])->toContain([
        'type' => 'admin-resource',
        'class' => PaymentCustomerResourceContribution::class,
        'resourceClass' => PaymentCustomerResource::class,
    ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => CheckoutSessionResourceContribution::class,
            'resourceClass' => CheckoutSessionResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => PaymentIntentResourceContribution::class,
            'resourceClass' => PaymentIntentResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => SubscriptionResourceContribution::class,
            'resourceClass' => SubscriptionResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => PaymentWebhookEventResourceContribution::class,
            'resourceClass' => PaymentWebhookEventResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => PaymentRefundResourceContribution::class,
            'resourceClass' => PaymentRefundResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => PaymentDisputeResourceContribution::class,
            'resourceClass' => PaymentDisputeResource::class,
        ])
        ->and(class_implements(PaymentCustomerResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(CheckoutSessionResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(PaymentIntentResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(SubscriptionResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(PaymentWebhookEventResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(PaymentRefundResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(PaymentDisputeResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and($manifest['contributionTraceability']['deferredContributions'])->not->toContain('admin-resource', 'form-field', 'migration', 'model', 'webhook');
});
