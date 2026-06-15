<?php

declare(strict_types=1);

namespace Capell\Payments\Support\Gateways;

use Capell\Payments\Actions\ResolvePaymentSettingAction;
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

final class PayPalPaymentGateway implements PaymentGateway
{
    public function createCheckoutSession(CreateCheckoutSessionData $data): CheckoutSessionData
    {
        $accessToken = $this->accessToken();

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->asJson()
            ->withHeaders(array_filter([
                'PayPal-Request-Id' => $data->idempotencyKey,
            ], static fn (?string $value): bool => $value !== null && $value !== ''))
            ->timeout($this->timeout())
            ->connectTimeout($this->connectTimeout())
            ->post($this->apiBaseUrl() . '/v2/checkout/orders', $this->payload($data))
            ->throw()
            ->json();

        throw_unless(is_array($response), RuntimeException::class, 'PayPal checkout order response was not a JSON object.');

        return $this->mapCheckoutSession($this->stringKeyedArray($response), $data);
    }

    private function accessToken(): string
    {
        $clientId = ResolvePaymentSettingAction::run('capell-payments.paypal.client_id', 'paypal_client_id');
        $clientSecret = ResolvePaymentSettingAction::run('capell-payments.paypal.client_secret', 'paypal_client_secret');

        if (! is_string($clientId) || $clientId === '' || ! is_string($clientSecret) || $clientSecret === '') {
            throw PaymentGatewayConfigurationException::missingPayPalCredentials();
        }

        $response = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->timeout($this->timeout())
            ->connectTimeout($this->connectTimeout())
            ->post($this->apiBaseUrl() . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ])
            ->throw()
            ->json();

        throw_unless(is_array($response), RuntimeException::class, 'PayPal access token response was not a JSON object.');

        $accessToken = $response['access_token'] ?? null;

        throw_unless(is_string($accessToken) && $accessToken !== '', RuntimeException::class, 'PayPal access token response did not include an access token.');

        return $accessToken;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(CreateCheckoutSessionData $data): array
    {
        return [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $data->referenceId ?? $data->sourceId ?? 'capell_checkout',
                    'custom_id' => $data->referenceId,
                    'description' => $this->description($data),
                    'amount' => [
                        'currency_code' => strtoupper($this->currency($data)),
                        'value' => $this->moneyValue($this->totalAmount($data)),
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => strtoupper($this->currency($data)),
                                'value' => $this->moneyValue($this->totalAmount($data)),
                            ],
                        ],
                    ],
                    'items' => array_map(
                        $this->lineItemPayload(...),
                        $data->lineItems,
                    ),
                ],
            ],
            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        'return_url' => $data->successUrl,
                        'cancel_url' => $data->cancelUrl,
                        'user_action' => 'PAY_NOW',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function lineItemPayload(CheckoutLineItemData $lineItem): array
    {
        return array_filter([
            'name' => $lineItem->name,
            'description' => $lineItem->description,
            'quantity' => (string) $lineItem->quantity,
            'unit_amount' => [
                'currency_code' => strtoupper($lineItem->currency),
                'value' => $this->moneyValue($lineItem->amount),
            ],
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapCheckoutSession(array $payload, CreateCheckoutSessionData $source): CheckoutSessionData
    {
        $providerSessionId = $payload['id'] ?? null;

        throw_unless(is_string($providerSessionId) && $providerSessionId !== '', RuntimeException::class, 'PayPal checkout order response did not include an order id.');

        return new CheckoutSessionData(
            provider: PaymentProvider::PayPal,
            providerSessionId: $providerSessionId,
            status: $this->checkoutStatus($payload['status'] ?? null),
            mode: CheckoutMode::Payment,
            purpose: $source->purpose,
            url: $this->approvalUrl($payload),
            currency: $this->currency($source),
            amountSubtotal: $this->totalAmount($source),
            amountTotal: $this->totalAmount($source),
            customerEmail: $source->customerEmail,
            customerName: $source->customerName,
            providerPaymentIntentId: $providerSessionId,
            expiresAt: CarbonImmutable::now()->addMinutes(10),
            metadata: array_filter([
                ...$source->metadata,
                'capell_purpose' => $source->purpose->value,
                'capell_site_id' => $source->siteId,
                'capell_billable_type' => $source->billableType,
                'capell_billable_id' => $source->billableId,
                'capell_payable_type' => $source->payableType,
                'capell_payable_id' => $source->payableId,
                'capell_source_type' => $source->sourceType,
                'capell_source_id' => $source->sourceId,
            ], static fn (mixed $value): bool => $value !== null && $value !== ''),
            providerPayload: $payload,
        );
    }

    private function checkoutStatus(mixed $status): CheckoutSessionStatus
    {
        return match ($status) {
            'CREATED', 'SAVED', 'APPROVED', 'PAYER_ACTION_REQUIRED' => CheckoutSessionStatus::Open,
            'COMPLETED' => CheckoutSessionStatus::Complete,
            'VOIDED' => CheckoutSessionStatus::Expired,
            default => CheckoutSessionStatus::Unknown,
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function approvalUrl(array $payload): ?string
    {
        $links = $payload['links'] ?? null;

        if (! is_array($links)) {
            return null;
        }

        foreach ($links as $link) {
            if (! is_array($link)) {
                continue;
            }

            if (($link['rel'] ?? null) === 'approve' && is_string($link['href'] ?? null)) {
                return $link['href'];
            }
        }

        return null;
    }

    private function totalAmount(CreateCheckoutSessionData $data): int
    {
        return array_sum(array_map(
            static fn (CheckoutLineItemData $lineItem): int => $lineItem->amount * $lineItem->quantity,
            $data->lineItems,
        ));
    }

    private function currency(CreateCheckoutSessionData $data): string
    {
        $firstLineItem = $data->lineItems[0] ?? null;

        return $firstLineItem instanceof CheckoutLineItemData ? $firstLineItem->currency : 'gbp';
    }

    private function moneyValue(int $minorUnits): string
    {
        return number_format($minorUnits / 100, 2, '.', '');
    }

    private function description(CreateCheckoutSessionData $data): string
    {
        $description = $data->metadata['description'] ?? null;

        return is_string($description) && $description !== '' ? $description : $data->purpose->getLabel();
    }

    private function apiBaseUrl(): string
    {
        $apiBaseUrl = ResolvePaymentSettingAction::run('capell-payments.paypal.api_base_url', 'paypal_api_base_url', 'https://api-m.paypal.com');

        return rtrim(is_string($apiBaseUrl) ? $apiBaseUrl : 'https://api-m.paypal.com', '/');
    }

    private function timeout(): int
    {
        return $this->integerSetting('capell-payments.paypal.timeout', 'paypal_timeout', 20);
    }

    private function connectTimeout(): int
    {
        return $this->integerSetting('capell-payments.paypal.connect_timeout', 'paypal_connect_timeout', 5);
    }

    private function integerSetting(string $configKey, string $settingsKey, int $default): int
    {
        $value = ResolvePaymentSettingAction::run($configKey, $settingsKey, $default);

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && filter_var($value, FILTER_VALIDATE_INT) !== false) {
            return (int) $value;
        }

        return $default;
    }

    /**
     * @param  array<mixed>  $values
     * @return array<string, mixed>
     */
    private function stringKeyedArray(array $values): array
    {
        $result = [];

        foreach ($values as $key => $value) {
            if (is_string($key)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
