<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Enums\EmailMessageStatus;
use Capell\EmailStudio\Enums\EmailRecipientStatus;
use Capell\EmailStudio\Models\EmailMessage;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EmailMessage|null run(EmailMessage|int $message, ?string $failureReason = null)
 */
class MarkEmailMessageDeliveryFailedAction
{
    use AsAction;

    public function handle(EmailMessage|int $message, ?string $failureReason = null): ?EmailMessage
    {
        $emailMessage = $message instanceof EmailMessage
            ? $message
            : EmailMessage::query()->find($message);

        if (! $emailMessage instanceof EmailMessage) {
            return null;
        }

        $resolvedFailureReason = $failureReason ?? 'Provider failed to send the message.';

        $emailMessage->recipients()
            ->where('status', EmailRecipientStatus::Queued->value)
            ->update([
                'status' => EmailRecipientStatus::Failed,
                'failure_reason' => $resolvedFailureReason,
            ]);

        $emailMessage->update([
            'status' => EmailMessageStatus::Failed,
            'failed_at' => now()->toImmutable(),
            'failure_reason' => $resolvedFailureReason,
        ]);

        return $emailMessage->fresh(['profile', 'recipients']) ?? $emailMessage;
    }
}
