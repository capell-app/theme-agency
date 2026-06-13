<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingMessageChannelEnum;
use Capell\Bookings\Enums\BookingReviewParticipantStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingReviewParticipant;
use Capell\Bookings\Models\BookingReviewRequest;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array<int, string> run(BookingReviewRequest $reviewRequest, ?CarbonImmutable $expiresAt = null)
 */
class RemindPendingReviewParticipantsAction
{
    use AsAction;

    /**
     * @return array<int, string>
     */
    public function handle(BookingReviewRequest $reviewRequest, ?CarbonImmutable $expiresAt = null): array
    {
        CreateReviewLoopAction::run($reviewRequest);

        /** @var EloquentCollection<int, BookingReviewParticipant> $participants */
        $participants = $reviewRequest->participants()
            ->whereNotIn('status', [
                BookingReviewParticipantStatusEnum::Completed,
                BookingReviewParticipantStatusEnum::Suppressed,
            ])
            ->get();
        $urls = [];

        foreach ($participants as $participant) {
            $participantId = filter_var($participant->getKey(), FILTER_VALIDATE_INT);

            if ($participantId === false) {
                continue;
            }

            $urls[$participantId] = IssueReviewParticipantUrlAction::run($participant, $expiresAt);
            $appointmentRequest = $reviewRequest->appointmentRequest;

            if ($appointmentRequest instanceof AppointmentRequest) {
                DispatchBookingMessageAction::run(
                    appointmentRequest: $appointmentRequest,
                    channel: BookingMessageChannelEnum::Email,
                    type: 'review_participant_request:' . $participantId,
                    body: __('capell-bookings::review.participant_request_body', ['url' => $urls[$participantId]]),
                    subject: __('capell-bookings::review.participant_request_subject'),
                    recipient: $participant->email,
                    context: [
                        'booking_review_participant_id' => $participantId,
                        'booking_review_request_id' => $reviewRequest->getKey(),
                        'review_url' => $urls[$participantId],
                        'role' => $participant->role,
                    ],
                );
            }

            $participant->forceFill([
                'sent_at' => CarbonImmutable::now(),
                'status' => BookingReviewParticipantStatusEnum::Sent,
            ])->save();
        }

        return $urls;
    }
}
