<?php

declare(strict_types=1);

namespace Capell\Payments\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

final class PaymentsHealthReportData extends Data
{
    /**
     * @param  list<string>  $issues
     */
    public function __construct(
        public readonly string $status,
        public readonly bool $stripeSecretConfigured,
        public readonly bool $stripeWebhookSecretConfigured,
        public readonly int $recordedWebhookEvents,
        public readonly int $failedWebhookEvents,
        public readonly int $failedFulfillmentResults,
        public readonly int $unresolvedDisputes,
        public readonly ?CarbonInterface $latestWebhookReceivedAt,
        public readonly bool $webhooksFresh,
        public readonly array $issues,
    ) {}
}
