<?php

declare(strict_types=1);

namespace Capell\Payments\Http\Controllers;

use Capell\Payments\Actions\HandleStripeWebhookAction;
use Capell\Payments\Exceptions\StripeWebhookSignatureException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class StripeWebhookController
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $event = HandleStripeWebhookAction::run(
                $request->getContent(),
                $request->headers->get('Stripe-Signature'),
            );
        } catch (StripeWebhookSignatureException) {
            return response()->json([
                'ok' => false,
            ], 400);
        }

        return response()->json([
            'ok' => true,
            'event_id' => $event->provider_event_id,
            'status' => $event->status->value,
        ]);
    }
}
