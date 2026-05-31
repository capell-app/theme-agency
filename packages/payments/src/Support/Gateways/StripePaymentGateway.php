<?php

declare(strict_types=1);

namespace Capell\Payments\Support\Gateways;

use Capell\Payments\Contracts\PaymentGateway;
use Capell\Payments\Data\CheckoutLineItemData;
use Capell\Payments\Data\CheckoutSessionData;
use Capell\Payments\Data\CreateCheckoutSessionData;
use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Exceptions\PaymentGatewayConfigurationException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class StripePaymentGateway implements PaymentGateway
{
    public function createCheckoutSession(CreateCheckoutSessionData $data): CheckoutSessionData
    {
        $secretKey = config('capell-payments.stripe.secret_key');

        if (! is_string($secretKey) || $secretKey === '') {
            throw PaymentGatewayConfigurationException::missingStripeSecret();
        }

        $response = Http::asForm()
            ->withToken($secretKey)
            ->withHeaders($this->headers($data))
            ->timeout($this->timeout())
            ->connectTimeout($this->connectTimeout())
            ->post($this->apiBaseUrl() . '/v1/checkout/sessions', $this->payload($data))
            ->throw()
            ->json();

        throw_unless(is_array($response), RuntimeException::class, 'Stripe checkout session response was not a JSON object.');

        return $this->mapCheckoutSession($response, $data);
    }

    /**
     * @return array<string, mixed>
     */
    private function lineItemPayload(CheckoutLineItemData $lineItem): array
    {
        if ($lineItem->providerPriceId !== null) {
            return [
                'price' => $lineItem->providerPriceId,
                'quantity' => $lineItem->quantity,
            ];
        }

        return [
            'quantity' => $lineItem->quantity,
            'price_data' => [
                'currency' => strtolower($lineItem->currency),
                'unit_amount' => $lineItem->amount,
                'product_data' => array_filter([
                    'name' => $lineItem->name,
                    'description' => $lineItem->description,
                    'metadata' => $lineItem->metadata,
                ], static fn (mixed $value): bool => $value !== null && $value !== []),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function headers(CreateCheckoutSessionData $data): array
    {
        return array_filter([
            'Stripe-Version' => $this->apiVersion(),
            'Idempotency-Key' => $data->idempotencyKey,
        ], static fn (?string $value): bool => $value !== null && $value !== '');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(CreateCheckoutSessionData $data): array
    {
        $payload = [
            'mode' => $data->mode->value,
            'success_url' => $data->successUrl,
            'cancel_url' => $data->cancelUrl,
            'metadata' => $this->metadata($data),
            'line_items' => array_map(
                $this->lineItemPayload(...),
                $data->lineItems,
            ),
        ];

        if ($data->providerCustomerId !== null) {
            $payload['customer'] = $data->providerCustomerId;
        } elseif ($data->customerEmail !== null) {
            $payload['customer_email'] = $data->customerEmail;
        }

        if ($data->referenceId !== null) {
            $payload['client_reference_id'] = $data->referenceId;
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function metadata(CreateCheckoutSessionData $data): array
    {
        return array_filter([
            ...$data->metadata,
            'capell_purpose' => $data->purpose->value,
            'capell_site_id' => $data->siteId,
            'capell_billable_type' => $data->billableType,
            'capell_billable_id' => $data->billableId,
            'capell_payable_type' => $data->payableType,
            'capell_payable_id' => $data->payableId,
            'capell_source_type' => $data->sourceType,
            'capell_source_id' => $data->sourceId,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapCheckoutSession(array $payload, CreateCheckoutSessionData $source): CheckoutSessionData
    {
        return new CheckoutSessionData(
            provider: PaymentProvider::Stripe,
            providerSessionId: (string) ($payload['id'] ?? ''),
            status: $this->checkoutStatus($payload['status'] ?? null),
            mode: $this->checkoutMode($payload['mode'] ?? null),
            purpose: $source->purpose,
            url: is_string($payload['url'] ?? null) ? $payload['url'] : null,
            currency: is_string($payload['currency'] ?? null) ? $payload['currency'] : null,
            amountSubtotal: is_int($payload['amount_subtotal'] ?? null) ? $payload['amount_subtotal'] : null,
            amountTotal: is_int($payload['amount_total'] ?? null) ? $payload['amount_total'] : null,
            providerCustomerId: is_string($payload['customer'] ?? null) ? $payload['customer'] : $source->providerCustomerId,
            customerEmail: is_string($payload['customer_details']['email'] ?? null) ? $payload['customer_details']['email'] : $source->customerEmail,
            customerName: is_string($payload['customer_details']['name'] ?? null) ? $payload['customer_details']['name'] : $source->customerName,
            providerPaymentIntentId: is_string($payload['payment_intent'] ?? null) ? $payload['payment_intent'] : null,
            providerSubscriptionId: is_string($payload['subscription'] ?? null) ? $payload['subscription'] : null,
            expiresAt: $this->timestamp($payload['expires_at'] ?? null),
            completedAt: $this->timestamp($payload['completed_at'] ?? null),
            metadata: is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [],
            providerPayload: $payload,
        );
    }

    private function checkoutStatus(mixed $status): CheckoutSessionStatus
    {
        return match ($status) {
            'open' => CheckoutSessionStatus::Open,
            'complete' => CheckoutSessionStatus::Complete,
            'expired' => CheckoutSessionStatus::Expired,
            default => CheckoutSessionStatus::Unknown,
        };
    }

    private function checkoutMode(mixed $mode): CheckoutMode
    {
        return match ($mode) {
            'subscription' => CheckoutMode::Subscription,
            'setup' => CheckoutMode::Setup,
            default => CheckoutMode::Payment,
        };
    }

    private function timestamp(mixed $timestamp): ?CarbonImmutable
    {
        if (! is_int($timestamp)) {
            return null;
        }

        return CarbonImmutable::createFromTimestamp($timestamp);
    }

    private function apiBaseUrl(): string
    {
        $apiBaseUrl = config('capell-payments.stripe.api_base_url', 'https://api.stripe.com');

        return rtrim(is_string($apiBaseUrl) ? $apiBaseUrl : 'https://api.stripe.com', '/');
    }

    private function apiVersion(): string
    {
        $apiVersion = config('capell-payments.stripe.api_version', '2026-02-25.clover');

        return is_string($apiVersion) && $apiVersion !== '' ? $apiVersion : '2026-02-25.clover';
    }

    private function timeout(): int
    {
        return (int) config('capell-payments.stripe.timeout', 20);
    }

    private function connectTimeout(): int
    {
        return (int) config('capell-payments.stripe.connect_timeout', 5);
    }
}
