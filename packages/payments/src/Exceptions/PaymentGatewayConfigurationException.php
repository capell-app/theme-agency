<?php

declare(strict_types=1);

namespace Capell\Payments\Exceptions;

use RuntimeException;

final class PaymentGatewayConfigurationException extends RuntimeException
{
    public static function missingStripeSecret(): self
    {
        return new self('Stripe checkout requires a configured STRIPE_SECRET value.');
    }
}
