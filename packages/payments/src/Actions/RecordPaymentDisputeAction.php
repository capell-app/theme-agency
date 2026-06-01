<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Data\PaymentDisputeData;
use Capell\Payments\Models\PaymentDispute;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordPaymentDisputeAction
{
    use AsAction;

    public function handle(PaymentDisputeData $data): PaymentDispute
    {
        return PaymentDispute::query()->updateOrCreate([
            'provider' => $data->provider->value,
            'provider_dispute_id' => $data->providerDisputeId,
        ], [
            'provider_payment_intent_id' => $data->providerPaymentIntentId,
            'provider_charge_id' => $data->providerChargeId,
            'status' => $data->status->value,
            'amount' => $data->amount,
            'currency' => $data->currency,
            'reason' => $data->reason,
            'is_charge_refundable' => $data->isChargeRefundable,
            'evidence_due_at' => $data->evidenceDueAt,
            'provider_payload' => $data->providerPayload,
        ]);
    }
}
