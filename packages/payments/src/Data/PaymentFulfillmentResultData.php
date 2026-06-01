<?php

declare(strict_types=1);

namespace Capell\Payments\Data;

use Spatie\LaravelData\Data;

final class PaymentFulfillmentResultData extends Data
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $handler,
        public readonly bool $fulfilled,
        public readonly ?string $message = null,
        public readonly array $metadata = [],
    ) {}
}
