<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Contracts\PaymentGateway;
use Capell\Payments\Data\CreateCheckoutSessionData;
use Capell\Payments\Models\CheckoutSession;
use Lorisleiva\Actions\Concerns\AsAction;

final class CreateCheckoutSessionAction
{
    use AsAction;

    public function __construct(private readonly PaymentGateway $gateway) {}

    public function handle(CreateCheckoutSessionData $data): CheckoutSession
    {
        $requestData = $this->withIdempotencyKey($data);
        $sessionData = $this->gateway->createCheckoutSession($requestData);

        return RecordCheckoutSessionAction::run($sessionData, $requestData);
    }

    private function withIdempotencyKey(CreateCheckoutSessionData $data): CreateCheckoutSessionData
    {
        if ($data->idempotencyKey !== null && $data->idempotencyKey !== '') {
            return $data;
        }

        return new CreateCheckoutSessionData(
            successUrl: $data->successUrl,
            cancelUrl: $data->cancelUrl,
            lineItems: $data->lineItems,
            purpose: $data->purpose,
            mode: $data->mode,
            provider: $data->provider,
            siteId: $data->siteId,
            providerCustomerId: $data->providerCustomerId,
            customerEmail: $data->customerEmail,
            customerName: $data->customerName,
            billableType: $data->billableType,
            billableId: $data->billableId,
            payableType: $data->payableType,
            payableId: $data->payableId,
            sourceType: $data->sourceType,
            sourceId: $data->sourceId,
            referenceId: $data->referenceId,
            idempotencyKey: GeneratePaymentGatewayIdempotencyKeyAction::run('checkout-session', $data->toArray()),
            metadata: $data->metadata,
        );
    }
}
