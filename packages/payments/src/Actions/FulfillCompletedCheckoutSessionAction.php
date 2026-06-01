<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Contracts\PaymentFulfillmentHandler;
use Capell\Payments\Data\PaymentFulfillmentResultData;
use Capell\Payments\Enums\CheckoutSessionStatus;
use Capell\Payments\Models\CheckoutSession;
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

        return $results;
    }
}
