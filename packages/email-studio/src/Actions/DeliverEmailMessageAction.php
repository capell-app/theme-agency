<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Enums\EmailMessageStatus;
use Capell\EmailStudio\Enums\EmailRecipientStatus;
use Capell\EmailStudio\Models\EmailMessage;
use Capell\EmailStudio\Models\EmailProfile;
use Capell\EmailStudio\Support\EmailProviderRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;
use Throwable;

class DeliverEmailMessageAction
{
    use AsAction;

    public function handle(EmailMessage|int $message): EmailMessage
    {
        $emailMessageId = $message instanceof EmailMessage ? (int) $message->getKey() : $message;

        if (! $this->claimForSending($emailMessageId)) {
            return EmailMessage::query()->with(['profile', 'recipients'])->findOrFail($emailMessageId);
        }

        $emailMessage = EmailMessage::query()->findOrFail($emailMessageId);
        $emailMessage->loadMissing(['profile', 'recipients']);

        $this->markNewSuppressions($emailMessage);

        if (! $emailMessage->recipients()->where('status', EmailRecipientStatus::Queued->value)->exists()) {
            $emailMessage->update([
                'status' => EmailMessageStatus::Failed,
                'failed_at' => now()->toImmutable(),
                'failure_reason' => 'All recipients are suppressed.',
            ]);

            return $emailMessage->fresh(['profile', 'recipients']) ?? $emailMessage;
        }

        try {
            $profile = $emailMessage->profile;

            throw_unless($profile instanceof EmailProfile, RuntimeException::class, 'Email message profile must be loaded before delivery.');

            $providerResult = resolve(EmailProviderRegistry::class)
                ->adapter($profile->provider)
                ->send($emailMessage->fresh(['profile', 'recipients']) ?? $emailMessage);
        } catch (Throwable $throwable) {
            $this->markProviderFailure($emailMessage, $throwable->getMessage());

            return $emailMessage->fresh(['profile', 'recipients']) ?? $emailMessage;
        }

        if (! $providerResult->successful) {
            $this->markProviderFailure($emailMessage, $providerResult->failureReason);

            return $emailMessage->fresh(['profile', 'recipients']) ?? $emailMessage;
        }

        foreach ($emailMessage->recipients()->where('status', EmailRecipientStatus::Queued->value)->get() as $recipient) {
            $recipientKey = (int) $recipient->getKey();
            $failureReason = $providerResult->failedRecipientReasons[$recipientKey] ?? null;

            if ($failureReason !== null) {
                $recipient->update([
                    'status' => EmailRecipientStatus::Failed,
                    'failure_reason' => $failureReason,
                ]);

                continue;
            }

            $recipient->update([
                'status' => EmailRecipientStatus::Sent,
                'provider_message_id' => $providerResult->recipientProviderMessageIds[$recipientKey] ?? null,
                'sent_at' => now()->toImmutable(),
            ]);
        }

        $emailMessage->update($this->messageStatusAttributes($emailMessage, $providerResult->failureReason));

        return $emailMessage->fresh(['profile', 'recipients']) ?? $emailMessage;
    }

    private function claimForSending(int $emailMessageId): bool
    {
        $staleSendingCutoff = now()->subSeconds((int) config('capell-email-studio.sending_lock_ttl_seconds', 900));

        return DB::transaction(fn (): bool => EmailMessage::query()
            ->whereKey($emailMessageId)
            ->where(function (Builder $query) use ($staleSendingCutoff): void {
                $query
                    ->whereIn('status', [
                        EmailMessageStatus::Queued->value,
                        EmailMessageStatus::Requested->value,
                    ])
                    ->orWhere(function (Builder $query) use ($staleSendingCutoff): void {
                        $query
                            ->where('status', EmailMessageStatus::Sending->value)
                            ->where('updated_at', '<=', $staleSendingCutoff);
                    });
            })
            ->update(['status' => EmailMessageStatus::Sending]) === 1);
    }

    private function markProviderFailure(EmailMessage $message, ?string $failureReason): void
    {
        $resolvedFailureReason = $failureReason ?? 'Provider failed to send the message.';

        foreach ($message->recipients()->where('status', EmailRecipientStatus::Queued->value)->get() as $recipient) {
            $recipient->update([
                'status' => EmailRecipientStatus::Failed,
                'failure_reason' => $resolvedFailureReason,
            ]);
        }

        $message->update([
            'status' => EmailMessageStatus::Failed,
            'failed_at' => now()->toImmutable(),
            'failure_reason' => $resolvedFailureReason,
        ]);
    }

    private function markNewSuppressions(EmailMessage $message): void
    {
        foreach ($message->recipients()->where('status', EmailRecipientStatus::Queued->value)->get() as $recipient) {
            if (resolve(CheckEmailSuppressionAction::class)->handle($recipient->email, $message->site_scope_key) === false) {
                continue;
            }

            $recipient->update([
                'status' => EmailRecipientStatus::Suppressed,
                'suppressed_at' => now()->toImmutable(),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function messageStatusAttributes(EmailMessage $message, ?string $failureReason): array
    {
        $recipientStatuses = $message->recipients()->pluck('status');
        $sentCount = $recipientStatuses->filter(
            fn (EmailRecipientStatus|string $status): bool => ($status instanceof EmailRecipientStatus ? $status : EmailRecipientStatus::from($status)) === EmailRecipientStatus::Sent,
        )->count();

        $failedCount = $recipientStatuses->count() - $sentCount;

        if ($sentCount === 0) {
            return [
                'status' => EmailMessageStatus::Failed,
                'failed_at' => now()->toImmutable(),
                'failure_reason' => $failureReason,
            ];
        }

        return [
            'status' => $failedCount > 0 ? EmailMessageStatus::PartiallyFailed : EmailMessageStatus::Sent,
            'sent_at' => now()->toImmutable(),
            'failure_reason' => $failureReason,
        ];
    }
}
