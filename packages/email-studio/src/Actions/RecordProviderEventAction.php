<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Data\ProviderWebhookEventData;
use Capell\EmailStudio\Enums\EmailEventType;
use Capell\EmailStudio\Enums\EmailRecipientStatus;
use Capell\EmailStudio\Enums\SuppressionReason;
use Capell\EmailStudio\Models\EmailEvent;
use Capell\EmailStudio\Models\EmailMessage;
use Capell\EmailStudio\Models\EmailProfile;
use Capell\EmailStudio\Models\EmailRecipient;
use Capell\EmailStudio\Support\EmailAddressNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EmailEvent run(EmailProfile $profile, ProviderWebhookEventData $eventData)
 */
final class RecordProviderEventAction
{
    use AsAction;

    public function handle(EmailProfile $profile, ProviderWebhookEventData $eventData): EmailEvent
    {
        $eventType = $this->eventType($eventData->eventType);
        $recipient = $this->recipient($profile, $eventData);
        $message = $recipient?->message;
        $idempotencyKey = $eventData->idempotencyKey ?: $this->idempotencyKey($profile, $eventData);
        $siteId = $recipient instanceof EmailRecipient ? $recipient->site_id : null;
        $siteId ??= $message instanceof EmailMessage ? $message->site_id : null;
        $siteId ??= $profile->site_id;
        $siteScopeKey = $recipient instanceof EmailRecipient ? $recipient->site_scope_key : null;
        $siteScopeKey ??= $message instanceof EmailMessage ? $message->site_scope_key : null;
        $siteScopeKey ??= $profile->site_scope_key;

        /** @var EmailEvent $event */
        $event = EmailEvent::query()->firstOrCreate([
            'email_profile_id' => $this->modelKey($profile),
            'idempotency_key' => $idempotencyKey,
        ], [
            'site_id' => $siteId,
            'site_scope_key' => $siteScopeKey,
            'email_message_id' => $this->nullableModelKey($message),
            'email_recipient_id' => $this->nullableModelKey($recipient),
            'type' => $eventType,
            'provider_event_id' => $eventData->idempotencyKey,
            'provider_payload' => $eventData->payload,
            'occurred_at' => now(),
        ]);

        if ($event->wasRecentlyCreated && $recipient instanceof EmailRecipient) {
            $this->applyRecipientStatus($recipient, $eventType);
            $this->applySuppression($recipient, $eventType);
        }

        return $event;
    }

    private function eventType(string $eventType): EmailEventType
    {
        $normalized = str_replace('_', '-', mb_strtolower($eventType));

        return match ($normalized) {
            'delivered', 'delivery' => EmailEventType::Delivered,
            'bounced', 'bounce', 'hard-bounce', 'soft-bounce' => EmailEventType::Bounced,
            'complained', 'complaint', 'spam-complaint' => EmailEventType::Complained,
            'opened', 'open' => EmailEventType::Opened,
            'clicked', 'click' => EmailEventType::Clicked,
            'replied', 'reply' => EmailEventType::Replied,
            'failed', 'failure', 'dropped' => EmailEventType::Failed,
            default => EmailEventType::Sent,
        };
    }

    private function recipient(EmailProfile $profile, ProviderWebhookEventData $eventData): ?EmailRecipient
    {
        $query = EmailRecipient::query()
            ->whereHas('message', fn (Builder $messageQuery): Builder => $messageQuery->where('email_profile_id', $profile->getKey()));

        if ($eventData->providerMessageId !== null && $eventData->providerMessageId !== '') {
            $recipient = (clone $query)->where('provider_message_id', $eventData->providerMessageId)->first();

            if ($recipient instanceof EmailRecipient) {
                return $recipient;
            }
        }

        if ($eventData->recipientEmail === null || $eventData->recipientEmail === '') {
            return null;
        }

        $normalizer = new EmailAddressNormalizer;

        return $query
            ->where('email_hash', $normalizer->hash($eventData->recipientEmail))
            ->latest('id')
            ->first();
    }

    private function applyRecipientStatus(EmailRecipient $recipient, EmailEventType $eventType): void
    {
        $status = match ($eventType) {
            EmailEventType::Delivered => EmailRecipientStatus::Delivered,
            EmailEventType::Bounced => EmailRecipientStatus::Bounced,
            EmailEventType::Complained => EmailRecipientStatus::Complained,
            EmailEventType::Opened => EmailRecipientStatus::Opened,
            EmailEventType::Clicked => EmailRecipientStatus::Clicked,
            EmailEventType::Replied => EmailRecipientStatus::Replied,
            EmailEventType::Failed => EmailRecipientStatus::Failed,
            EmailEventType::Sent => EmailRecipientStatus::Sent,
        };

        $timestampColumn = match ($status) {
            EmailRecipientStatus::Delivered => 'delivered_at',
            EmailRecipientStatus::Bounced => 'bounced_at',
            EmailRecipientStatus::Complained => 'complained_at',
            EmailRecipientStatus::Opened => 'opened_at',
            EmailRecipientStatus::Clicked => 'clicked_at',
            EmailRecipientStatus::Replied => 'replied_at',
            EmailRecipientStatus::Sent => 'sent_at',
            default => null,
        };

        $updates = ['status' => $status];

        if ($timestampColumn !== null) {
            $updates[$timestampColumn] = now();
        }

        $recipient->forceFill($updates)->save();
    }

    private function idempotencyKey(EmailProfile $profile, ProviderWebhookEventData $eventData): string
    {
        return hash('sha256', implode('|', [
            (string) $this->modelKey($profile),
            $eventData->provider,
            $eventData->eventType,
            $eventData->providerMessageId ?? '',
            $eventData->recipientEmail ?? '',
            json_encode($eventData->payload, JSON_THROW_ON_ERROR),
        ]));
    }

    private function applySuppression(EmailRecipient $recipient, EmailEventType $eventType): void
    {
        if (! in_array($eventType, [EmailEventType::Bounced, EmailEventType::Complained], true)) {
            return;
        }

        SuppressEmailAddressAction::run(
            email: $recipient->email,
            reason: $eventType === EmailEventType::Bounced
                ? SuppressionReason::Bounce
                : SuppressionReason::Complaint,
            siteId: $recipient->site_id,
            siteScopeKey: $recipient->site_scope_key,
            source: 'provider-event',
            notes: sprintf('Automatically suppressed from provider %s event.', $eventType->value),
        );
    }

    private function modelKey(Model $model): int
    {
        $key = $model->getKey();

        return is_int($key) ? $key : 0;
    }

    private function nullableModelKey(?Model $model): ?int
    {
        return $model instanceof Model ? $this->modelKey($model) : null;
    }
}
