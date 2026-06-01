<?php

declare(strict_types=1);

namespace Capell\Payments\Exceptions;

use RuntimeException;

final class StripeWebhookSignatureException extends RuntimeException
{
    public static function missingSignatureHeader(): self
    {
        return new self('Stripe webhook request is missing the Stripe-Signature header.');
    }

    public static function missingTimestamp(): self
    {
        return new self('Stripe webhook signature header is missing a timestamp.');
    }

    public static function staleTimestamp(): self
    {
        return new self('Stripe webhook signature timestamp is outside the allowed tolerance.');
    }

    public static function invalidSignature(): self
    {
        return new self('Stripe webhook signature could not be verified.');
    }

    public static function invalidPayload(): self
    {
        return new self('Stripe webhook payload is not valid JSON.');
    }
}
