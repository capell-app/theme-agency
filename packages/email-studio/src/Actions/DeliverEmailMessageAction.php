<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Enums\EmailMessageStatus;
use Capell\EmailStudio\Enums\EmailRecipientStatus;
use Capell\EmailStudio\Exceptions\RetryableEmailDeliveryException;
use Capell\EmailStudio\Models\EmailMessage;
use Capell\EmailStudio\Models\EmailProfile;
use Capell\EmailStudio\Models\EmailRecipient;
use Capell\EmailStudio\Support\EmailProviderRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

/**
 * @method static EmailMessage run(EmailMessage|int $message)
 */
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
            return MarkEmailMessageDeliveryFailedAction::run($emailMessage, 'All recipients are suppressed.') ?? $emailMessage;
        }

        $profile = $emailMessage->profile;

        if (! $profile instanceof EmailProfile) {
            return MarkEmailMessageDeliveryFailedAction::run($emailMessage, 'Email message profile must be loaded before delivery.') ?? $emailMessage;
        }

        try {
            $providerResult = resolve(EmailProviderRegistry::class)
                ->adapter($profile->provider)
                ->send($emailMessage->fresh(['profile', 'recipients']) ?? $emailMessage);
        } catch (Throwable $throwable) {
            if ($emailMessage->queued_at === null) {
                return MarkEmailMessageDeliveryFailedAction::run($emailMessage, $throwable->getMessage()) ?? $emailMessage;
            }

            $this->releaseForRetry($emailMessage, $throwable->getMessage());

            throw RetryableEmailDeliveryException::provider($throwable);
        }

        if (! $providerResult->successful) {
            return MarkEmailMessageDeliveryFailedAction::run($emailMessage, $providerResult->failureReason) ?? $emailMessage;
        }

        $recipientWriteSummary = $this->applyProviderRecipientResults($emailMessage, $providerResult->recipientProviderMessageIds, $providerResult->failedRecipientReasons);

        $emailMessage->update($this->messageStatusAttributes(
            $recipientWriteSummary['sent'],
            $recipientWriteSummary['failed'],
            $providerResult->failureReason,
        ));

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

    private function releaseForRetry(EmailMessage $message, ?string $failureReason): void
    {
        $message->update([
            'status' => EmailMessageStatus::Queued,
            'failed_at' => null,
            'failure_reason' => $failureReason ?? 'Provider failed to send the message.',
        ]);
    }

    private function markNewSuppressions(EmailMessage $message): void
    {
        $suppressedRecipientIds = [];

        foreach ($message->recipients()->where('status', EmailRecipientStatus::Queued->value)->get() as $recipient) {
            if (resolve(CheckEmailSuppressionAction::class)->handle($recipient->email, $message->site_scope_key) === false) {
                continue;
            }

            $suppressedRecipientIds[] = (int) $recipient->getKey();
        }

        if ($suppressedRecipientIds === []) {
            return;
        }

        EmailRecipient::query()
            ->whereKey($suppressedRecipientIds)
            ->update([
                'status' => EmailRecipientStatus::Suppressed->value,
                'suppressed_at' => now()->toImmutable(),
            ]);
    }

    /**
     * @param  array<int, string>  $providerMessageIds
     * @param  array<int, string>  $failedRecipientReasons
     * @return array{sent: int, failed: int}
     */
    private function applyProviderRecipientResults(EmailMessage $message, array $providerMessageIds, array $failedRecipientReasons): array
    {
        $totalRecipientCount = $message->recipients()->count();
        $queuedRecipients = $message->recipients()
            ->where('status', EmailRecipientStatus::Queued->value)
            ->get([
                'id',
                'site_id',
                'site_scope_key',
                'email_message_id',
                'type',
                'email',
                'normalized_email',
                'email_hash',
            ]);
        $sentAt = now()->toImmutable();
        $updatedAt = now()->toImmutable();
        $sentRows = [];
        $failedRecipientIdsByReason = [];

        foreach ($queuedRecipients as $recipient) {
            $recipientKey = $recipient->id;
            $failureReason = $failedRecipientReasons[$recipientKey] ?? null;

            if ($failureReason !== null) {
                $failedRecipientIdsByReason[$failureReason][] = $recipientKey;

                continue;
            }

            $sentRows[] = [
                'id' => $recipientKey,
                'site_id' => $recipient->site_id,
                'site_scope_key' => $recipient->site_scope_key,
                'email_message_id' => $recipient->email_message_id,
                'type' => $recipient->type,
                'email' => $recipient->email,
                'normalized_email' => $recipient->normalized_email,
                'email_hash' => $recipient->email_hash,
                'status' => EmailRecipientStatus::Sent->value,
                'provider_message_id' => $providerMessageIds[$recipientKey] ?? null,
                'sent_at' => $sentAt,
                'updated_at' => $updatedAt,
            ];
        }

        foreach ($failedRecipientIdsByReason as $failureReason => $recipientIds) {
            EmailRecipient::query()
                ->whereKey($recipientIds)
                ->update([
                    'status' => EmailRecipientStatus::Failed->value,
                    'failure_reason' => $failureReason,
                ]);
        }

        if ($sentRows !== []) {
            EmailRecipient::query()->upsert(
                $sentRows,
                ['id'],
                ['status', 'provider_message_id', 'sent_at', 'updated_at'],
            );
        }

        return [
            'sent' => count($sentRows),
            'failed' => $totalRecipientCount - count($sentRows),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function messageStatusAttributes(int $sentCount, int $failedCount, ?string $failureReason): array
    {
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
