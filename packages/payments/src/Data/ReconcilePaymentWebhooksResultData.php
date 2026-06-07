<?php

declare(strict_types=1);

namespace Capell\Payments\Data;

use Spatie\LaravelData\Data;

final class ReconcilePaymentWebhooksResultData extends Data
{
    public function __construct(
        public int $received,
        public int $processed,
        public int $ignored,
        public int $failed,
        public int $staleReceived,
    ) {}
}
