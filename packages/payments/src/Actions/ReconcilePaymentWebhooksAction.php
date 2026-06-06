<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Data\ReconcilePaymentWebhooksResultData;
use Capell\Payments\Enums\PaymentWebhookEventStatus;
use Capell\Payments\Models\PaymentWebhookEvent;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Lorisleiva\Actions\Concerns\AsAction;

final class ReconcilePaymentWebhooksAction
{
    use AsAction;

    public function handle(?DateTimeInterface $now = null, int $staleAfterMinutes = 30): ReconcilePaymentWebhooksResultData
    {
        $currentTime = CarbonImmutable::parse($now ?? CarbonImmutable::now());
        $staleBefore = $currentTime->subMinutes(max(1, $staleAfterMinutes));

        return new ReconcilePaymentWebhooksResultData(
            received: $this->countStatus(PaymentWebhookEventStatus::Received),
            processed: $this->countStatus(PaymentWebhookEventStatus::Processed),
            ignored: $this->countStatus(PaymentWebhookEventStatus::Ignored),
            failed: $this->countStatus(PaymentWebhookEventStatus::Failed),
            staleReceived: PaymentWebhookEvent::query()
                ->where('status', PaymentWebhookEventStatus::Received->value)
                ->where('received_at', '<=', $staleBefore)
                ->count(),
        );
    }

    private function countStatus(PaymentWebhookEventStatus $status): int
    {
        return PaymentWebhookEvent::query()
            ->where('status', $status->value)
            ->count();
    }
}
