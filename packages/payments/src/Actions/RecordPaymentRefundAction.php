<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Data\PaymentRefundData;
use Capell\Payments\Models\PaymentRefund;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordPaymentRefundAction
{
    use AsAction;

    public function handle(PaymentRefundData $data): PaymentRefund
    {
        return PaymentRefund::query()->updateOrCreate([
            'provider' => $data->provider->value,
            'provider_refund_id' => $data->providerRefundId,
        ], [
            'provider_payment_intent_id' => $data->providerPaymentIntentId,
            'provider_charge_id' => $data->providerChargeId,
            'status' => $data->status->value,
            'amount' => $data->amount,
            'currency' => $data->currency,
            'reason' => $data->reason,
            'metadata' => $data->metadata,
            'provider_payload' => $data->providerPayload,
        ]);
    }
}
