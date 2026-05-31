<?php

declare(strict_types=1);

namespace Capell\Payments\Tests\Fakes;

use Capell\Payments\Contracts\PaymentGateway;
use Capell\Payments\Data\CheckoutSessionData;
use Capell\Payments\Data\CreateCheckoutSessionData;

final class FakePaymentGateway implements PaymentGateway
{
    public function __construct(private readonly CheckoutSessionData $sessionData) {}

    public function createCheckoutSession(CreateCheckoutSessionData $data): CheckoutSessionData
    {
        return $this->sessionData;
    }
}
