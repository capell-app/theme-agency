<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Exceptions;

use RuntimeException;
use Throwable;

class RetryableEmailDeliveryException extends RuntimeException
{
    public static function provider(Throwable $throwable): self
    {
        return new self($throwable->getMessage(), 0, $throwable);
    }
}
