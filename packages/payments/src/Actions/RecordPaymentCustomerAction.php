<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Data\PaymentCustomerData;
use Capell\Payments\Models\PaymentCustomer;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordPaymentCustomerAction
{
    use AsAction;

    public function handle(PaymentCustomerData $data): ?PaymentCustomer
    {
        if ($data->providerCustomerId === null) {
            return null;
        }

        return PaymentCustomer::query()->updateOrCreate([
            'provider' => $data->provider->value,
            'provider_customer_id' => $data->providerCustomerId,
        ], [
            'site_id' => $data->siteId,
            'billable_type' => $data->billableType,
            'billable_id' => $data->billableId,
            'email' => $data->email,
            'name' => $data->name,
            'metadata' => $data->metadata,
            'provider_payload' => $data->providerPayload,
        ]);
    }
}
