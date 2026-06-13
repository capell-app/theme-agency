<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Contracts\BookingMessageChannel;
use Capell\Bookings\Data\BookingMessageData;
use Capell\Bookings\Enums\BookingMessageChannelEnum;
use Capell\Bookings\Enums\BookingMessageStatusEnum;
use Capell\Bookings\Enums\MessagingConsentStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingMessageLog;
use Capell\Bookings\Models\MessagingConsent;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingMessageLog run(AppointmentRequest $appointmentRequest, BookingMessageChannelEnum $channel, string $type, string $body, ?string $subject = null, ?CarbonImmutable $sendAt = null)
 */
class DispatchBookingMessageAction
{
    use AsAction;

    public function handle(
        AppointmentRequest $appointmentRequest,
        BookingMessageChannelEnum $channel,
        string $type,
        string $body,
        ?string $subject = null,
        ?CarbonImmutable $sendAt = null,
    ): BookingMessageLog {
        return DB::transaction(function () use ($appointmentRequest, $channel, $type, $body, $subject, $sendAt): BookingMessageLog {
            /** @var BookingMessageLog|null $existingMessageLog */
            $existingMessageLog = BookingMessageLog::query()
                ->where('appointment_request_id', $appointmentRequest->getKey())
                ->where('channel', $channel->value)
                ->where('type', $type)
                ->lockForUpdate()
                ->first();

            if ($existingMessageLog instanceof BookingMessageLog) {
                return $existingMessageLog;
            }

            $recipient = $this->recipient($appointmentRequest, $channel);
            $status = BookingMessageStatusEnum::Pending;
            $error = null;
            $providerMessageId = null;
            $sentAt = null;
            $meta = [];

            if (! $this->hasConsent($appointmentRequest, $channel)) {
                $status = BookingMessageStatusEnum::Skipped;
                $error = __('capell-bookings::validation.messaging_consent_required');
            } else {
                $result = app(BookingMessageChannel::class)->send(new BookingMessageData(
                    channel: $channel,
                    recipient: $recipient,
                    type: $type,
                    body: $body,
                    subject: $subject,
                    sendAt: $sendAt,
                    context: ['appointment_request_id' => $appointmentRequest->getKey()],
                ));

                $status = $result->sent ? BookingMessageStatusEnum::Sent : BookingMessageStatusEnum::Failed;
                $providerMessageId = $result->providerMessageId;
                $error = $result->error;
                $sentAt = $result->sent ? CarbonImmutable::now() : null;
                $meta = $result->meta;
            }

            /** @var BookingMessageLog $messageLog */
            $messageLog = BookingMessageLog::query()->create([
                'appointment_request_id' => $appointmentRequest->getKey(),
                'site_id' => $appointmentRequest->site_id,
                'portal_account_id' => $appointmentRequest->portal_account_id,
                'channel' => $channel,
                'type' => $type,
                'status' => $status,
                'recipient' => $recipient,
                'subject' => $subject,
                'body' => $body,
                'scheduled_for' => $sendAt,
                'sent_at' => $sentAt,
                'provider_message_id' => $providerMessageId,
                'error' => $error,
                'meta' => $meta,
            ]);

            return $messageLog;
        });
    }

    private function recipient(AppointmentRequest $appointmentRequest, BookingMessageChannelEnum $channel): string
    {
        if ($channel === BookingMessageChannelEnum::Email) {
            return $appointmentRequest->customer_email;
        }

        return (string) $appointmentRequest->customer_phone;
    }

    private function hasConsent(AppointmentRequest $appointmentRequest, BookingMessageChannelEnum $channel): bool
    {
        if ($channel === BookingMessageChannelEnum::Email) {
            return true;
        }

        if ($appointmentRequest->portal_account_id === null || $appointmentRequest->site_id === null) {
            return false;
        }

        /** @var MessagingConsent|null $consent */
        $consent = MessagingConsent::query()
            ->where('site_id', $appointmentRequest->site_id)
            ->where('portal_account_id', $appointmentRequest->portal_account_id)
            ->where('channel', $channel->value)
            ->first();

        return $consent?->status === MessagingConsentStatusEnum::Granted;
    }
}
