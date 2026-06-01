<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Data\PaymentCustomerData;
use Capell\Payments\Data\SubscriptionData;
use Capell\Payments\Models\Subscription;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordSubscriptionAction
{
    use AsAction;

    public function handle(SubscriptionData $data): Subscription
    {
        $customer = RecordPaymentCustomerAction::run(new PaymentCustomerData(
            provider: $data->provider,
            providerCustomerId: $data->providerCustomerId,
            providerPayload: [],
        ));

        return Subscription::query()->updateOrCreate([
            'provider' => $data->provider->value,
            'provider_subscription_id' => $data->providerSubscriptionId,
        ], [
            'payment_customer_id' => $customer?->getKey(),
            'provider_customer_id' => $data->providerCustomerId,
            'provider_session_id' => $data->providerSessionId,
            'status' => $data->status->value,
            'metadata' => $data->metadata,
            'provider_payload' => $data->providerPayload,
            'trial_ends_at' => $data->trialEndsAt,
            'current_period_starts_at' => $data->currentPeriodStartsAt,
            'current_period_ends_at' => $data->currentPeriodEndsAt,
            'cancel_at' => $data->cancelAt,
            'canceled_at' => $data->canceledAt,
        ]);
    }
}
