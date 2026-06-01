<?php

declare(strict_types=1);

namespace Capell\Payments\Enums;

use Capell\Payments\Filament\Resources\CheckoutSessions\CheckoutSessionResource;
use Capell\Payments\Filament\Resources\Customers\PaymentCustomerResource;
use Capell\Payments\Filament\Resources\Disputes\PaymentDisputeResource;
use Capell\Payments\Filament\Resources\PaymentIntents\PaymentIntentResource;
use Capell\Payments\Filament\Resources\Refunds\PaymentRefundResource;
use Capell\Payments\Filament\Resources\Subscriptions\SubscriptionResource;
use Capell\Payments\Filament\Resources\WebhookEvents\PaymentWebhookEventResource;

enum ResourceEnum: string
{
    case Customer = PaymentCustomerResource::class;
    case CheckoutSession = CheckoutSessionResource::class;
    case PaymentIntent = PaymentIntentResource::class;
    case Subscription = SubscriptionResource::class;
    case WebhookEvent = PaymentWebhookEventResource::class;
    case Refund = PaymentRefundResource::class;
    case Dispute = PaymentDisputeResource::class;
}
