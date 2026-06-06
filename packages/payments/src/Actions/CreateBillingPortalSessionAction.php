<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Data\BillingPortalSessionData;
use Capell\Payments\Data\CreateBillingPortalSessionData;
use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Exceptions\PaymentGatewayConfigurationException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

final class CreateBillingPortalSessionAction
{
    use AsAction;

    public function handle(CreateBillingPortalSessionData $data): BillingPortalSessionData
    {
        $secretKey = ResolvePaymentSettingAction::run('capell-payments.stripe.secret_key', 'stripe_secret_key');

        if (! is_string($secretKey) || $secretKey === '') {
            throw PaymentGatewayConfigurationException::missingStripeSecret();
        }

        $response = Http::asForm()
            ->withToken($secretKey)
            ->withHeaders([
                'Stripe-Version' => $this->apiVersion(),
                'Idempotency-Key' => GeneratePaymentGatewayIdempotencyKeyAction::run('billing-portal-session', $data->toArray()),
            ])
            ->timeout($this->timeout())
            ->connectTimeout($this->connectTimeout())
            ->post($this->apiBaseUrl() . '/v1/billing_portal/sessions', $this->payload($data))
            ->throw()
            ->json();

        throw_unless(is_array($response), RuntimeException::class, 'Stripe billing portal session response was not a JSON object.');

        return $this->mapBillingPortalSession($response, $data);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(CreateBillingPortalSessionData $data): array
    {
        return array_filter([
            'customer' => $data->providerCustomerId,
            'return_url' => $data->returnUrl,
            'configuration' => $data->configuration,
            'locale' => $data->locale,
        ], static fn (?string $value): bool => $value !== null && $value !== '');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapBillingPortalSession(array $payload, CreateBillingPortalSessionData $source): BillingPortalSessionData
    {
        $providerSessionId = is_string($payload['id'] ?? null) ? $payload['id'] : null;
        $url = is_string($payload['url'] ?? null) ? $payload['url'] : null;

        throw_unless($providerSessionId !== null && $url !== null, RuntimeException::class, 'Stripe billing portal session response did not include an id and url.');

        return new BillingPortalSessionData(
            provider: PaymentProvider::Stripe,
            providerSessionId: $providerSessionId,
            providerCustomerId: is_string($payload['customer'] ?? null) ? $payload['customer'] : $source->providerCustomerId,
            url: $url,
            returnUrl: is_string($payload['return_url'] ?? null) ? $payload['return_url'] : $source->returnUrl,
            configuration: is_string($payload['configuration'] ?? null) ? $payload['configuration'] : $source->configuration,
            locale: is_string($payload['locale'] ?? null) ? $payload['locale'] : $source->locale,
            createdAt: $this->timestamp($payload['created'] ?? null),
            providerPayload: $payload,
        );
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
