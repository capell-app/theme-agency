<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Data\CheckoutSessionData;
use Capell\Payments\Data\CreateCheckoutSessionData;
use Capell\Payments\Data\PaymentCustomerData;
use Capell\Payments\Models\CheckoutSession;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordCheckoutSessionAction
{
    use AsAction;

    public function handle(CheckoutSessionData $sessionData, CreateCheckoutSessionData $sourceData): CheckoutSession
    {
        $customer = RecordPaymentCustomerAction::run(new PaymentCustomerData(
            provider: $sessionData->provider,
            providerCustomerId: $sessionData->providerCustomerId,
            siteId: $sourceData->siteId,
            billableType: $sourceData->billableType,
            billableId: $sourceData->billableId,
            email: $sessionData->customerEmail,
            name: $sessionData->customerName,
            metadata: $sessionData->metadata,
            providerPayload: [],
        ));

        return CheckoutSession::query()->updateOrCreate([
            'provider' => $sessionData->provider->value,
            'provider_session_id' => $sessionData->providerSessionId,
        ], [
            'payment_customer_id' => $customer?->getKey(),
            'site_id' => $sourceData->siteId,
            'mode' => $sessionData->mode->value,
            'purpose' => $sessionData->purpose->value,
            'status' => $sessionData->status->value,
            'url' => $sessionData->url,
            'currency' => $sessionData->currency,
            'amount_subtotal' => $sessionData->amountSubtotal,
            'amount_total' => $sessionData->amountTotal,
            'provider_customer_id' => $sessionData->providerCustomerId,
            'provider_payment_intent_id' => $sessionData->providerPaymentIntentId,
            'provider_subscription_id' => $sessionData->providerSubscriptionId,
            'billable_type' => $sourceData->billableType,
            'billable_id' => $sourceData->billableId,
            'payable_type' => $sourceData->payableType,
            'payable_id' => $sourceData->payableId,
            'source_type' => $sourceData->sourceType,
            'source_id' => $sourceData->sourceId,
            'reference_id' => $sourceData->referenceId,
            'metadata' => $sessionData->metadata,
            'provider_payload' => $sessionData->providerPayload,
            'expires_at' => $sessionData->expiresAt,
            'completed_at' => $sessionData->completedAt,
        ]);
    }
}
