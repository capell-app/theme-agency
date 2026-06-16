<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Contracts\BookingMessageChannel;
use Capell\Bookings\Data\BookingMessageData;
use Capell\Bookings\Enums\BookingMessageStatusEnum;
use Capell\Bookings\Models\BookingMessageLog;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingMessageLog run(BookingMessageLog $messageLog)
 */
class RetryBookingMessageAction
{
    use AsAction;

    public function handle(BookingMessageLog $messageLog): BookingMessageLog
    {
        if ($messageLog->status !== BookingMessageStatusEnum::Failed) {
            throw ValidationException::withMessages([
                'message_log' => __('capell-bookings::validation.message_log_not_retryable'),
            ]);
        }

        return DB::transaction(function () use ($messageLog): BookingMessageLog {
            /** @var BookingMessageLog $lockedMessageLog */
            $lockedMessageLog = BookingMessageLog::query()
                ->whereKey($messageLog->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedMessageLog->status !== BookingMessageStatusEnum::Failed) {
                throw ValidationException::withMessages([
                    'message_log' => __('capell-bookings::validation.message_log_not_retryable'),
                ]);
            }

            $result = app(BookingMessageChannel::class)->send(new BookingMessageData(
                channel: $lockedMessageLog->channel,
                recipient: $lockedMessageLog->recipient,
                type: $lockedMessageLog->type,
                body: (string) $lockedMessageLog->body,
                subject: $lockedMessageLog->subject,
                sendAt: null,
                context: array_merge(
                    is_array($lockedMessageLog->meta) ? $lockedMessageLog->meta : [],
                    [
                        'appointment_request_id' => $lockedMessageLog->appointment_request_id,
                        'message_log_id' => $lockedMessageLog->getKey(),
                        'retry' => true,
                    ],
                ),
            ));

            $meta = is_array($lockedMessageLog->meta) ? $lockedMessageLog->meta : [];
            $retryCount = filter_var($meta['retry_count'] ?? 0, FILTER_VALIDATE_INT);

            $lockedMessageLog->forceFill([
                'status' => $result->sent ? BookingMessageStatusEnum::Sent : BookingMessageStatusEnum::Failed,
                'sent_at' => $result->sent ? CarbonImmutable::now() : null,
                'provider_message_id' => $result->providerMessageId,
                'error' => $result->error,
                'meta' => array_merge($meta, $result->meta, [
                    'retried_at' => CarbonImmutable::now()->toIso8601String(),
                    'retry_count' => ($retryCount === false ? 0 : $retryCount) + 1,
                ]),
            ])->save();

            return $lockedMessageLog->refresh();
        });
    }
}
