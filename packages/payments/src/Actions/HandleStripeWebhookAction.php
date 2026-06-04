<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Enums\PaymentProvider;
use Capell\Payments\Enums\PaymentWebhookEventStatus;
use Capell\Payments\Exceptions\PaymentGatewayConfigurationException;
use Capell\Payments\Jobs\ProcessStripeWebhookEventJob;
use Capell\Payments\Models\PaymentWebhookEvent;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;

final class HandleStripeWebhookAction
{
    use AsAction;

    public function handle(string $payload, ?string $signatureHeader): PaymentWebhookEvent
    {
        $endpointSecret = ResolvePaymentSettingAction::run('capell-payments.stripe.webhook_secret', 'stripe_webhook_secret');

        if (! is_string($endpointSecret) || $endpointSecret === '') {
            throw PaymentGatewayConfigurationException::missingStripeWebhookSecret();
        }

        $eventPayload = VerifyStripeWebhookSignatureAction::run($payload, $signatureHeader, $endpointSecret);
        $event = $this->recordEvent($eventPayload, $signatureHeader);

        if ($event->status !== PaymentWebhookEventStatus::Processed && $event->status !== PaymentWebhookEventStatus::Ignored) {
            $pendingDispatch = ProcessStripeWebhookEventJob::dispatch((int) $event->getKey());
            $queueName = config('capell-payments.webhooks.queue');

            if (is_string($queueName) && $queueName !== '') {
                $pendingDispatch->onQueue($queueName);
            }

            $pendingDispatch->afterCommit();
        }

        return $event->refresh();
    }

    /**
     * @param  array<string, mixed>  $eventPayload
     */
    private function recordEvent(array $eventPayload, ?string $signatureHeader): PaymentWebhookEvent
    {
        $providerEventId = $this->stringValue($eventPayload['id'] ?? null);
        $eventType = $this->stringValue($eventPayload['type'] ?? null);

        throw_if($providerEventId === null || $eventType === null, InvalidArgumentException::class, 'Stripe webhook payload is missing an event id or type.');

        $event = PaymentWebhookEvent::query()
            ->where('provider', PaymentProvider::Stripe->value)
            ->where('provider_event_id', $providerEventId)
            ->first();

        if ($event instanceof PaymentWebhookEvent) {
            return $event;
        }

        try {
            return PaymentWebhookEvent::query()->create([
                'provider' => PaymentProvider::Stripe->value,
                'provider_event_id' => $providerEventId,
                'event_type' => $eventType,
                'livemode' => (bool) ($eventPayload['livemode'] ?? false),
                'api_version' => $this->stringValue($eventPayload['api_version'] ?? null),
                'status' => PaymentWebhookEventStatus::Received,
                'signature_header_hash' => is_string($signatureHeader) ? hash('sha256', $signatureHeader) : null,
                'payload' => $eventPayload,
                'received_at' => CarbonImmutable::now(),
            ]);
        } catch (QueryException $queryException) {
            $event = PaymentWebhookEvent::query()
                ->where('provider', PaymentProvider::Stripe->value)
                ->where('provider_event_id', $providerEventId)
                ->first();

            if ($event instanceof PaymentWebhookEvent) {
                return $event;
            }

            throw $queryException;
        }
    }

    private function stringValue(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
