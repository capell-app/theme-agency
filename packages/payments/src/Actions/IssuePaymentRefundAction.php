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

        throw_unless(is_array($response), RuntimeException::class, 'Stripe refund response was not a JSON object.');

        return RecordPaymentRefundAction::run($this->refundData($response, $paymentIntent));
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
                'capell_payment_intent_id' => (string) $paymentIntent->getKey(),
            ],
        ], static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []);
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
            metadata: is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [],
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
        return (int) ResolvePaymentSettingAction::run('capell-payments.stripe.timeout', 'stripe_timeout', 20);
    }

    private function connectTimeout(): int
    {
        return (int) ResolvePaymentSettingAction::run('capell-payments.stripe.connect_timeout', 'stripe_connect_timeout', 5);
    }
}
