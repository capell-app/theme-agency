<?php

declare(strict_types=1);

namespace Capell\Payments\Contracts;

use Capell\Payments\Data\PaymentFulfillmentResultData;
use Capell\Payments\Models\CheckoutSession;

interface PaymentFulfillmentHandler
{
    public const string TAG = 'capell.payments.fulfillment_handler';

    public function key(): string;

    public function supports(CheckoutSession $checkoutSession): bool;

    public function fulfill(CheckoutSession $checkoutSession): PaymentFulfillmentResultData;
}
