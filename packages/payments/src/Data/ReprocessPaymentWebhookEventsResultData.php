<?php

declare(strict_types=1);

namespace Capell\Payments\Data;

use Spatie\LaravelData\Data;

final class ReprocessPaymentWebhookEventsResultData extends Data
{
    public function __construct(
        public int $processed,
        public int $failed,
    ) {}
}
