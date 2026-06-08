<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Data\PaymentRefundData;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentRefundStatus;
use Capell\Payments\Exceptions\PaymentGatewayConfigurationException;
use Capell\Payments\Models\PaymentIntent;
use Capell\Payments\Models\PaymentRefund;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

/**
 * @method static PaymentRefund run(PaymentIntent $paymentIntent, ?int $amount = null, ?string $reason = null)
 */
final class IssuePaymentRefundAction
{
    use AsAction;

    public function handle(PaymentIntent $paymentIntent, ?int $amount = null, ?string $reason = null): PaymentRefund
    {
        $secretKey = ResolvePaymentSettingAction::run('capell-payments.stripe.secret_key', 'stripe_secret_key');

        if (! is_string($secretKey) || $secretKey === '') {
            throw PaymentGatewayConfigurationException::missingStripeSecret();
        }

        $payload = $this->payload($paymentIntent, $amount, $reason);
        $response = Http::asForm()
            ->withToken($secretKey)
            ->withHeaders([
                'Stripe-Version' => $this->apiVersion(),
                'Idempotency-Key' => GeneratePaymentGatewayIdempotencyKeyAction::run('refund', $payload),
            ])
            ->timeout($this->timeout())
            ->connectTimeout($this->connectTimeout())
            ->post($this->apiBaseUrl() . '/v1/refunds', $payload)
            ->throw()
            ->json();

        return RecordPaymentRefundAction::run($this->refundData($this->responsePayload($response), $paymentIntent));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(PaymentIntent $paymentIntent, ?int $amount, ?string $reason): array
    {
        return array_filter([
            'payment_intent' => $paymentIntent->provider_payment_intent_id,
            'amount' => $amount,
            'reason' => $reason,
            'metadata' => [
                'capell_payment_intent_id' => $this->modelKey($paymentIntent),
            ],
        ], static fn (mixed $value): bool => ! in_array($value, [null, '', []], true));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function refundData(array $payload, PaymentIntent $paymentIntent): PaymentRefundData
    {
        $providerRefundId = is_string($payload['id'] ?? null) ? $payload['id'] : null;

        throw_unless($providerRefundId !== null, RuntimeException::class, 'Stripe refund response did not include an id.');

        return new PaymentRefundData(
            provider: PaymentProvider::Stripe,
            providerRefundId: $providerRefundId,
            status: $this->refundStatus($payload['status'] ?? null),
            amount: is_int($payload['amount'] ?? null) ? $payload['amount'] : $paymentIntent->amount,
            currency: is_string($payload['currency'] ?? null) ? $payload['currency'] : $paymentIntent->currency,
            providerPaymentIntentId: is_string($payload['payment_intent'] ?? null) ? $payload['payment_intent'] : $paymentIntent->provider_payment_intent_id,
            providerChargeId: is_string($payload['charge'] ?? null) ? $payload['charge'] : null,
            reason: is_string($payload['reason'] ?? null) ? $payload['reason'] : null,
            metadata: $this->metadata($payload['metadata'] ?? null),
            providerPayload: $payload,
        );
    }

    private function refundStatus(mixed $status): PaymentRefundStatus
    {
        return match ($status) {
            'pending' => PaymentRefundStatus::Pending,
            'requires_action' => PaymentRefundStatus::RequiresAction,
            'succeeded' => PaymentRefundStatus::Succeeded,
            'failed' => PaymentRefundStatus::Failed,
            default => PaymentRefundStatus::Unknown,
        };
    }

    private function apiBaseUrl(): string
    {
        $apiBaseUrl = ResolvePaymentSettingAction::run('capell-payments.stripe.api_base_url', 'stripe_api_base_url', 'https://api.stripe.com');

        return rtrim(is_string($apiBaseUrl) ? $apiBaseUrl : 'https://api.stripe.com', '/');
    }

    private function apiVersion(): string
    {
        $apiVersion = ResolvePaymentSettingAction::run('capell-payments.stripe.api_version', 'stripe_api_version', '2026-02-25.clover');

        return is_string($apiVersion) && $apiVersion !== '' ? $apiVersion : '2026-02-25.clover';
    }

    private function timeout(): int
    {
        return $this->integerSetting('capell-payments.stripe.timeout', 'stripe_timeout', 20);
    }

    private function connectTimeout(): int
    {
        return $this->integerSetting('capell-payments.stripe.connect_timeout', 'stripe_connect_timeout', 5);
    }

    /**
     * @return array<string, mixed>
     */
    private function responsePayload(mixed $payload): array
    {
        throw_unless(is_array($payload), RuntimeException::class, 'Stripe refund response was not a JSON object.');

        return $this->stringKeyedArray($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function metadata(mixed $metadata): array
    {
        if (! is_array($metadata)) {
            return [];
        }

        return $this->stringKeyedArray($metadata);
    }

    private function integerSetting(string $configKey, string $settingsKey, int $fallback): int
    {
        $value = ResolvePaymentSettingAction::run($configKey, $settingsKey, $fallback);

        return is_numeric($value) ? (int) $value : $fallback;
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

    private function modelKey(PaymentIntent $paymentIntent): string
    {
        $key = $paymentIntent->getKey();

        if (is_int($key) || is_string($key)) {
            return (string) $key;
        }

        return '';
    }
}
