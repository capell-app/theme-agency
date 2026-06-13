<?php

declare(strict_types=1);

namespace Capell\Payments\Support\Gateways;

use Capell\Payments\Contracts\PaymentGateway;
use Capell\Payments\Data\CheckoutSessionData;
use Capell\Payments\Data\CreateCheckoutSessionData;
use Capell\Payments\Enums\PaymentProvider;

final class ConfiguredPaymentGateway implements PaymentGateway
{
    public function createCheckoutSession(CreateCheckoutSessionData $data): CheckoutSessionData
    {
        return match ($data->provider) {
            PaymentProvider::PayPal => (new PayPalPaymentGateway)->createCheckoutSession($data),
            PaymentProvider::Stripe => (new StripePaymentGateway)->createCheckoutSession($data),
        };
    }
}
