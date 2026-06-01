<?php

declare(strict_types=1);

namespace Capell\Payments\Tests\Fakes;

use Capell\Payments\Contracts\PaymentFulfillmentHandler;
use Capell\Payments\Data\PaymentFulfillmentResultData;
use Capell\Payments\Models\CheckoutSession;

final class FakePaymentFulfillmentHandler implements PaymentFulfillmentHandler
{
    /** @var list<string> */
    public static array $fulfilledSessionIds = [];

    public function key(): string
    {
        return 'fake';
    }

    public function supports(CheckoutSession $checkoutSession): bool
    {
        return $checkoutSession->payable_type === 'download';
    }

    public function fulfill(CheckoutSession $checkoutSession): PaymentFulfillmentResultData
    {
        self::$fulfilledSessionIds[] = $checkoutSession->provider_session_id;

        return new PaymentFulfillmentResultData(
            handler: $this->key(),
            fulfilled: true,
            metadata: [
                'provider_session_id' => $checkoutSession->provider_session_id,
            ],
        );
    }
}
