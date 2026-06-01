<?php

declare(strict_types=1);

namespace Capell\Newsletter\Actions;

use Capell\Newsletter\Enums\NewsletterSendStatus;
use Capell\Newsletter\Models\NewsletterSend;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

final class UpdateNewsletterSendStatusAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function handle(
        NewsletterSend $send,
        NewsletterSendStatus $status,
        ?CarbonInterface $occurredAt = null,
        array $metadata = [],
    ): NewsletterSend {
        $this->validateTransition($send, $status);

        $occurredAt = $occurredAt instanceof CarbonInterface
            ? CarbonImmutable::instance($occurredAt)
            : CarbonImmutable::now();

        $send->status = $status;
        $send->metadata = array_replace_recursive($send->metadata ?? [], $metadata);

        match ($status) {
            NewsletterSendStatus::Sending => $send->metadata = array_replace_recursive($send->metadata ?? [], [
                'delivery' => ['started_at' => $occurredAt->toISOString()],
            ]),
            NewsletterSendStatus::Sent => $send->sent_at = $occurredAt,
            NewsletterSendStatus::Failed => $send->failed_at = $occurredAt,
            NewsletterSendStatus::Cancelled => $send->cancelled_at = $occurredAt,
            default => null,
        };

        $send->save();

        return $send;
    }

    private function validateTransition(NewsletterSend $send, NewsletterSendStatus $status): void
    {
        $currentStatus = $send->status;

        if (! $currentStatus instanceof NewsletterSendStatus) {
            return;
        }

        if (in_array($currentStatus, [NewsletterSendStatus::Sent, NewsletterSendStatus::Cancelled], true)) {
            throw ValidationException::withMessages([
                'status' => __('capell-newsletter::messages.send_transition_final'),
            ]);
        }

        if ($status === NewsletterSendStatus::Sent && $currentStatus !== NewsletterSendStatus::Sending) {
            throw ValidationException::withMessages([
                'status' => __('capell-newsletter::messages.send_transition_requires_sending'),
            ]);
        }
    }
}
