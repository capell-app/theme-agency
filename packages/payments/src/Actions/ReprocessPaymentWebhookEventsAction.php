<?php

declare(strict_types=1);

namespace Capell\Payments\Actions;

use Capell\Payments\Data\ReprocessPaymentWebhookEventsResultData;
use Capell\Payments\Enums\PaymentWebhookEventStatus;
use Capell\Payments\Models\PaymentWebhookEvent;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class ReprocessPaymentWebhookEventsAction
{
    use AsAction;

    public function handle(?int $webhookEventId = null, int $limit = 100): ReprocessPaymentWebhookEventsResultData
    {
        $processed = 0;
        $failed = 0;

        $this->query($webhookEventId, $limit)
            ->get()
            ->each(function (PaymentWebhookEvent $event) use (&$processed, &$failed): void {
                try {
                    $event->forceFill([
                        'status' => PaymentWebhookEventStatus::Received->value,
                        'failed_at' => null,
                        'error' => null,
                    ])->save();

                    ProcessStripeWebhookEventAction::run((int) $event->getKey());

                    $processed++;
                } catch (Throwable) {
                    $failed++;
                }
            });

        return new ReprocessPaymentWebhookEventsResultData(
            processed: $processed,
            failed: $failed,
        );
    }

    /**
     * @return Builder<PaymentWebhookEvent>
     */
    private function query(?int $webhookEventId, int $limit): Builder
    {
        return PaymentWebhookEvent::query()
            ->when($webhookEventId !== null, function (Builder $query) use ($webhookEventId): void {
                $query->whereKey($webhookEventId);
            })
            ->when($webhookEventId === null, function (Builder $query): void {
                $query->where('status', PaymentWebhookEventStatus::Failed->value);
            })
            ->oldest('received_at')
            ->orderBy('id')
            ->limit(max(1, $limit));
    }
}
