<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Data\PaymentCustomerData;
use Capell\Payments\Data\PaymentIntentData;
use Capell\Payments\Models\PaymentIntent;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordPaymentIntentAction
{
    use AsAction;

    public function handle(PaymentIntentData $data): PaymentIntent
    {
        $customer = RecordPaymentCustomerAction::run(new PaymentCustomerData(
            provider: $data->provider,
            providerCustomerId: $data->providerCustomerId,
            providerPayload: [],
        ));

        return PaymentIntent::query()->updateOrCreate([
            'provider' => $data->provider->value,
            'provider_payment_intent_id' => $data->providerPaymentIntentId,
        ], [
            'payment_customer_id' => $customer?->getKey(),
            'provider_customer_id' => $data->providerCustomerId,
            'provider_session_id' => $data->providerSessionId,
            'status' => $data->status->value,
            'amount' => $data->amount,
            'currency' => $data->currency,
            'metadata' => $data->metadata,
            'provider_payload' => $data->providerPayload,
        ]);
    }
}
