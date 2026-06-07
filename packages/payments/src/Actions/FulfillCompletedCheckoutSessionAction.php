<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Contracts\PaymentFulfillmentHandler;
use Capell\Payments\Data\PaymentFulfillmentResultData;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Models\CheckoutSession;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

final class FulfillCompletedCheckoutSessionAction
{
    use AsAction;

    /**
     * @return list<PaymentFulfillmentResultData>
     */
    public function handle(CheckoutSession $checkoutSession): array
    {
        if ($checkoutSession->status !== CheckoutSessionStatus::Complete) {
            return [];
        }

        $results = [];

        foreach (app()->tagged(PaymentFulfillmentHandler::TAG) as $handler) {
            if (! $handler instanceof PaymentFulfillmentHandler) {
                continue;
            }

            if (! $handler->supports($checkoutSession)) {
                continue;
            }

            $results[] = $handler->fulfill($checkoutSession);
        }

        $this->recordResults($checkoutSession, $results);

        return $results;
    }

    /**
     * @param  list<PaymentFulfillmentResultData>  $results
     */
    private function recordResults(CheckoutSession $checkoutSession, array $results): void
    {
        if ($results === []) {
            return;
        }

        $metadata = $checkoutSession->metadata ?? [];
        $metadata['fulfillment_results'] = array_map(
            static fn (PaymentFulfillmentResultData $result): array => [
                'handler' => $result->handler,
                'fulfilled' => $result->fulfilled,
                'message' => $result->message,
                'metadata' => $result->metadata,
                'recorded_at' => CarbonImmutable::now()->toIso8601String(),
            ],
            $results,
        );
        $metadata['fulfillment_failed'] = collect($results)
            ->contains(static fn (PaymentFulfillmentResultData $result): bool => ! $result->fulfilled);

        $checkoutSession->forceFill([
            'metadata' => $metadata,
        ])->save();
    }
}
