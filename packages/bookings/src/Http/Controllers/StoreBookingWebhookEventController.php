<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Actions\RecordBookingWebhookEventAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class StoreBookingWebhookEventController
{
    public function __invoke(Request $request, string $provider): JsonResponse
    {
        $expectedToken = config('capell-bookings.webhook_tokens.' . $provider);
        $providedToken = $request->header('X-Capell-Webhook-Token') ?? $request->bearerToken();
        abort_unless(
            is_string($expectedToken)
                && $expectedToken !== ''
                && is_string($providedToken)
                && hash_equals($expectedToken, $providedToken),
            403,
        );

        $payload = $this->payload($request);

        $webhookEvent = RecordBookingWebhookEventAction::run(
            provider: $provider,
            providerEventId: $this->eventId($request, $payload),
            type: $this->eventType($request, $payload),
            payload: $payload,
        );

        return response()->json([
            'id' => $webhookEvent->getKey(),
            'status' => $webhookEvent->status,
        ], $webhookEvent->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        $payload = $request->isJson() ? $request->json()->all() : $request->all();

        if (! is_array($payload)) {
            return [];
        }

        $normalizedPayload = [];

        foreach ($payload as $key => $value) {
            if (is_string($key)) {
                $normalizedPayload[$key] = $value;
            }
        }

        return $normalizedPayload;
    }

    /**
     * @param  array<array-key, mixed>  $payload
     */
    private function eventId(Request $request, array $payload): string
    {
        $headerValue = $request->header('X-Webhook-Event-Id');

        if (is_string($headerValue) && $headerValue !== '') {
            return $headerValue;
        }

        $payloadValue = data_get($payload, 'id');

        if (is_string($payloadValue) && $payloadValue !== '') {
            return $payloadValue;
        }

        return hash('sha256', $request->getContent());
    }

    /**
     * @param  array<array-key, mixed>  $payload
     */
    private function eventType(Request $request, array $payload): string
    {
        $headerValue = $request->header('X-Webhook-Type');

        if (is_string($headerValue) && $headerValue !== '') {
            return $headerValue;
        }

        $payloadValue = data_get($payload, 'type');

        if (is_string($payloadValue) && $payloadValue !== '') {
            return $payloadValue;
        }

        return 'received';
    }
}
