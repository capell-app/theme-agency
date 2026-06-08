<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Contracts\PaymentGateway;
use Capell\Payments\Data\CheckoutLineItemData;
use Capell\Payments\Data\CreateCheckoutSessionData;
use Capell\Payments\Models\CheckoutSession;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static CheckoutSession run(CreateCheckoutSessionData $data)
 */
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
            idempotencyKey: GeneratePaymentGatewayIdempotencyKeyAction::run('checkout-session', $this->idempotencyPayload($data)),
            metadata: $data->metadata,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function idempotencyPayload(CreateCheckoutSessionData $data): array
    {
        return [
            'success_url' => $data->successUrl,
            'cancel_url' => $data->cancelUrl,
            'line_items' => array_map(
                static fn (CheckoutLineItemData $lineItem): array => [
                    'name' => $lineItem->name,
                    'amount' => $lineItem->amount,
                    'currency' => $lineItem->currency,
                    'quantity' => $lineItem->quantity,
                    'description' => $lineItem->description,
                    'provider_price_id' => $lineItem->providerPriceId,
                    'metadata' => $lineItem->metadata,
                ],
                $data->lineItems,
            ),
            'purpose' => $data->purpose->value,
            'mode' => $data->mode->value,
            'provider' => $data->provider->value,
            'site_id' => $data->siteId,
            'provider_customer_id' => $data->providerCustomerId,
            'customer_email' => $data->customerEmail,
            'customer_name' => $data->customerName,
            'billable_type' => $data->billableType,
            'billable_id' => $data->billableId,
            'payable_type' => $data->payableType,
            'payable_id' => $data->payableId,
            'source_type' => $data->sourceType,
            'source_id' => $data->sourceId,
            'reference_id' => $data->referenceId,
            'metadata' => $data->metadata,
        ];
    }
}
