<?php

declare(strict_types=1);

namespace Capell\Payments\Contracts;

use Capell\Payments\Data\CheckoutSessionData;
use Capell\Payments\Data\CreateCheckoutSessionData;

interface PaymentGateway
{
    public function createCheckoutSession(CreateCheckoutSessionData $data): CheckoutSessionData;
}
